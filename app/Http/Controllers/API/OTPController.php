<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Services\OtpService;
use App\Models\OTP;
use Illuminate\Support\Str;
use App\Models\CountryCode;
use App\Models\Khata;
class OTPController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }


     public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10'
        ]);

        $user = User::where('phone', $request->phone)->first();
       
        if($user){
            if(!$user->is_active){
                return redirect()->back()->withErrors([
                    'phone' => 'This phone number is inactive. Please contact support.'
                ])->withInput();
            }else{
                
            
                    // Store verification data in otp table
                $resOtp = OTP::where('phone', $request->phone)->where('created', date('Y-m-d'))->orderBy('created_at', 'desc')->first();
                if($resOtp && $resOtp->created_at > now()->subHour() && $resOtp->count >= 3){
                    return response()->json([
                        "status"=>false,
                        "message"=>"You have reached the maximum OTP request limit for an hour. Please try again after one hour.",
                        "data"=>null
                    ], 429);
                }else{
                    try {
                      
                        
                        //store otp in otp table
                        $otpCode = '123456'; 
                        $resOtp = OTP::where('phone', $request->phone)->where('created', date('Y-m-d'))->orderBy('created_at', 'desc')->first();
                        $otpEntry = OTP::create([
                            'phone' => $request->phone,
                            'otp' => $otpCode,
                            'count' => $resOtp ? $resOtp->count + 1 : 1,
                            'expires_at' => now()->addMinutes(10),
                            'created' => date('Y-m-d'),
                        ]);
                        return response()->json([
                            "status"=>true,
                            "message"=>"otp sent successfully",
                            "phone"=>$request->phone,
                           
                        ]);
                    
                    } catch (\Exception $e) {
                        return back()
                            ->withErrors(['phone' => $e->getMessage()])
                            ->withInput();
                    }
                }
            }
        }else{
            try {
            
                
                $resOtp = OTP::where('phone', $request->phone)->where('created', date('Y-m-d'))->orderBy('created_at', 'desc')->first();
                if($resOtp && $resOtp->created_at > now()->subHour() && $resOtp->count >= 3){
                    return response()->json([
                        "status"=>false,
                        "message"=>"You have reached the maximum OTP request limit for an hour. Please try again after one hour.",
                        "data"=>null
                    ], 429);
                }else{
                    $otpCode = '654321';
                    $otpEntry = OTP::create([
                                'phone' => $request->phone,
                                'otp' => $otpCode,
                                'count' => isset($resOtp) ? $resOtp->count + 1 : 1,
                                'expires_at' => now()->addMinutes(10),
                                'created' => date('Y-m-d'),
                            ]);

                    return response()->json([
                            "status"=>true,
                            "message"=>"otp sent successfully",
                            "phone"=>$request->phone,
                        ]);
                }
            }catch (\Exception $e) {
                return $e->getMessage();
            }
        }
        
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
            'phone' => 'required|digits:10'
        ]);


        try {
       
        if(!CountryCode::where('country_code', 977)->exists()){
            $CountryCode =CountryCode::create([
                'country_name' => 'Nepal',
                'country_code' => 977,
              
            ]);
        }
            $CountryCode = CountryCode::where('country_code', 977)->first();

            $otpRecord = OTP::where('phone', $request->phone)
                            ->where('otp', $request->otp)
                            ->where('expires_at', '>', now())
                            ->first();
            if($otpRecord){
                $user = User::where('phone', $request->phone)->first();
                if(!$user){
                $user = User::create([
                    'uuid'=> Str::uuid(),
                    'phone' => $request->phone,
                    'phone_verified_at' => now(),
                    'country_code_id' => $CountryCode->id,
                    'is_active' => true,
                ]);
                }
             
                Auth::login($user);
                OTP::where('phone', $request->phone)->delete();
                $user->tokens()->delete();
                $token = $user->createToken('api')->plainTextToken;
               
                $khatas = Khata::where('user_id', $user->id)->orderBy('updated_at', 'desc')->get();

                if($khatas->isEmpty()){
                    return response()->json([
                    "status"=>true,
                    "message"=>"otp verified successfully",
                    "code"=>Auth::user()->id,
                    "token"=>$token,
                  
                ]);
                }else{
                $res = $this->totalAmount();
                $userData = User::where('id', Auth::id())->first();
                return response()->json([
                    "status"=>true,
                    "message"=>"otp verified successfully",
                    "code"=>Auth::user()->id,
                    "token"=>$token,
                    "name"=>$userData,
                    "amount"=>$res->original['amount'],
                    "type"=>$res->original['type'],
                    "khatas"=>$khatas,
                ]);
                }
                    
           
                    
               
            }else{
                return response()->json([
                    "status"=>false,
                    "message"=>"Invalid or expired OTP. Please try again.",
                ], 400);
            }
        } catch (\Exception $e) {
            return $e->getMessage() ;
        }
    }
    public function totalAmount()
    {
        $user = Auth::user();
        $khatas = Khata::where('user_id', $user->id)->get();
        $received_amount = 0;
        $due_amount = 0;
        foreach ($khatas as $khata) {
            $received_amount += $khata->received_amount;
            $due_amount += $khata->due_amount;

        }
        $amount = $received_amount > $due_amount ? $received_amount - $due_amount : $due_amount - $received_amount;
        $type = $received_amount > $due_amount ? 'receivable' : 'payable';
        return response()->json([
            'status' => true,
            'amount' => $amount,
            'type' => $type,
        ]);
    }

}