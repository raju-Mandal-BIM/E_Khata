<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Khata;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;


class KhataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $validatedData = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'phone' => ['required','max:10',Rule::unique('khatas')->where(fn($query) => $query->where('user_id', $user->id))],
            'country_code_id' => 'required|integer',
        ]);
        $khataExists = Khata::where('user_id', $user->id)
                        ->where('phone', $request->phone)
                        ->first();
   
        if ($validatedData->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation errors',
            'errors' => $validatedData->errors(),
            'khata_id' => $khataExists ? $khataExists->id : null,
        ], 422);
    }
 
    try {
            $khata = Khata::create([
                'user_id' => Auth::id(),
                'name' => $validatedData['name'],
                'phone' => $validatedData['phone'],
                'country_code_id' => 1,
                'synced_at' => date('Y-m-d'),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Khata created successfully',
                'data' => $khata
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to create Khata',
                'error' => $e->getMessage()
            ], 500);
        }  
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Khata $khata)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Khata $khata)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Khata $khata)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Khata $khata)
    {
        //
    }
}
