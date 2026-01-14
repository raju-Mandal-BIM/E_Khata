<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class AuthController extends Controller
{
    /**
     * Logout user and revoke token
     */
    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }
    public function edit(Request $request)
    {   

        $validatedData = Validator::make($request->all(),[
            'name'=>'required|string|max:255',
            'email'=>'required|email|max:255|unique:users,email',
            'address'=>'nullable|string|max:500',
            'profile_image'=>'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($validatedData->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validatedData->errors(),
            ], 422);
        }


        try{
      
            $image = null;
            if($request->hasFile('profile_image')){
                $imageName = time().'.'.$request->profile_image->extension();
                $request->profile_image->move(public_path('images/profile_images'), $imageName);
                $image = 'images/profile_images/'.$imageName;
            }
            $user_id = Auth::user()->id;
            $userData = User::where('id', $user_id)->first();
            $user = User::where('id', $user_id)->update([
                'name'=>$request->name?$request->name:$userData->name,
                'email'=>$request->email?$request->email:$userData->email,
                'address'=>$request->address?$request->address:$userData->address,
                'profile_image'=>$image?$image:$userData->profile_image]);

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'data' => $user
            ]);
        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => 'Failed to update profile',
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
