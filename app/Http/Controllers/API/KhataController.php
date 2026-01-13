<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Khata;


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
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:10|unique:khatas,phone',
            'country_code_id' => 'required|integer',
        ]);
        if($validatedData->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validatedData->errors()
            ], 422);
        }
    try {
            $khata = Khata::create([
                'user_id' => Auth::id(),
                'name' => $validatedData['name'],
                'phone' => $validatedData['phone'],
                'country_code_id' => $validatedData['country_code_id'],
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
