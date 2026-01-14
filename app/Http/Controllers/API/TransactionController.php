<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Khata;
use Illuminate\Support\Facades\Auth;
class TransactionController extends Controller
{
    //receive payment
    public function received(Request $request, $khata_id)
    {
        $request->validate([
            
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'transaction_date' => 'required|date',
        ]);
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                if($request->hasFile('attachment')){
                    $imageName = time().'.'.$request->attachment->extension();
                    $request->attachment->move(public_path('images/khata_profile'), $imageName);
                    $attachmentPath = 'images/Transaction_Attachment/'.$imageName;
                }
            }   
            $transaction = Transaction::create([
                'khata_id' => $khata_id,
                'user_id' => auth()->id(),
                'amount' => $request->amount,
                'note' => $request->has('note') ? $request->note : null,
                'attachment' => $request->hasFile('attachment') ? $attachmentPath : null,
                'status' => 'synced',
                'type' => 'received',
                'transaction_date' => $request->transaction_date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if ($transaction) {
                $khata=Khata::where('id',$khata_id)->where('user_id',auth()->id())->first();
                if(!$khata){
                    return response()->json([
                        'status' => false,
                        'message' => 'Khata not found'
                    ], 404);
                }
                if($khata->due_amount>0){
                    
                    if($request->amount < $khata->due_amount){
                        $amount = $khata->due_amount - $request->amount;
                        $khata->due_amount = $amount;
                        $khata->total_amount = $amount;
                        $khata->updated_at = now();
                        $khata->save();
                        $khata->refresh();
                            return response()->json([
                                'status' => true,
                                'message' => 'transaction successfully',
                                'payable' => $khata->total_amount,
                            ], 201);
                        
                    }elseif($request->amount == $khata->due_amount){
                        $khata->due_amount = 0;
                        $khata->total_amount = 0;
                        $khata->updated_at = now();
                        $khata->save();
                         $khata->refresh();
                            return response()->json([
                                'status' => true,
                                'message' => 'transaction successfully',
                                'balance' => $khata->total_amount,
                            ], 201);
                    }
                    else{
                        $amountToReduce = $khata->due_amount;
                        $additionalAmount = $request->amount - $amountToReduce;
                        $khata->due_amount = 0;
                        $khata->total_amount = $additionalAmount;
                        $khata->received_amount += $additionalAmount;
                        $khata->updated_at = now();
                        $khata->save();
                         $khata->refresh();
                            return response()->json([
                                'status' => true,
                                'message' => 'transaction successfully',
                                'receivable' => $khata->total_amount,
                            ], 201);
                    }
                
                }else{
                    $additionalAmount = $request->amount + $khata->total_amount;
                
                    $khata->total_amount = $additionalAmount;
                    $khata->received_amount += $request->amount;
                    $khata->updated_at = now();
                    $khata->save();
                    $khata->refresh();
                        return response()->json([
                            'status' => true,
                            'message' => 'transaction successfully',
                            'receivable' => $khata->total_amount,
                        ], 201);
                }

            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'transaction Failed'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'transaction Failed: '.$e->getMessage()
            ], 500);
        }
        
    }




    public function payment(Request $request , $khata_id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'transaction_date' => 'required|date',
        ]);

        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                if($request->hasFile('attachment')){
                    $imageName = time().'.'.$request->attachment->extension();
                    $request->attachment->move(public_path('images/khata_profile'), $imageName);
                    $attachmentPath = 'images/Transaction_Attachment/'.$imageName;
                }
            }   
            $transaction = Transaction::create([
                'khata_id' => $khata_id,
                'user_id' => auth()->id(),
                'amount' => $request->amount,
                'note' => $request->has('note') ? $request->note : null,
                'attachment' => $request->hasFile('attachment') ? $attachmentPath : null,
                'status' => 'synced',
                'type' => 'payment',
                'transaction_date' => $request->transaction_date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($transaction) {
                $khata=Khata::where('id',$khata_id)->where('user_id',auth()->id())->first();
                if(!$khata){
                    return response()->json([
                        'status' => false,
                        'message' => 'Khata not found'
                    ], 404);
                }
                if($khata->received_amount>0){
                    
                    if($request->amount < $khata->received_amount){
                        $amount = $khata->received_amount - $request->amount;
                        $khata->received_amount  = $amount;
                        $khata->total_amount = $amount;
                        $khata->updated_at = now();
                        $khata->save();
                         $khata->refresh();
                            return response()->json([
                                'status' => true,
                                'message' => 'transaction successfully',
                                'payable' => $khata->total_amount,
                            ], 201);
                        
                    }elseif($request->amount == $khata->received_amount){
                        $khata->received_amount = 0;
                        $khata->total_amount = 0;
                        $khata->updated_at = now();
                        $khata->save();
                         $khata->refresh();
                            return response()->json([
                                'status' => true,
                                'message' => 'transaction successfully',
                                'balance' => $khata->total_amount,
                            ], 201);
                    }
                    else{
                        $amountToReduce = $khata->received_amount;
                        $additionalAmount = $request->amount - $amountToReduce;
                        $khata->received_amount = 0;
                        $khata->total_amount = $additionalAmount;
                        $khata->due_amount += $additionalAmount;
                        $khata->updated_at = now();
                        $khata->save();
                         $khata->refresh();
                            return response()->json([
                                'status' => true,
                                'message' => 'transaction successfully',
                                'receivable' => $khata->total_amount,
                            ], 201);
                    }
                
                }else{
                    $additionalAmount = $request->amount + $khata->total_amount;
                
                    $khata->total_amount += $additionalAmount;
                    $khata->due_amount += $request->amount;
                    $khata->updated_at = now();
                    $khata->save();
                    $khata->refresh();
                        return response()->json([
                            'status' => true,
                            'message' => 'transaction successfully',
                            'receivable' => $khata->total_amount,
                        ], 201);
                }

            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'transaction Failed'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'transaction Failed: '.$e->getMessage()
            ], 500);
        }
       


        
    }
}