<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Khata;
use App\Models\Transaction;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\CountryCode;


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
        $countryCodeId = CountryCode::where('country_code', 977)->first();
            $khata = Khata::create([
                'user_id' => Auth::id(),
                'name' => $request->name,
                'phone' => $request->phone,
                'country_code_id' => $countryCodeId->id,
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

    public function editKhata(Request $request, $id)
    {
      
        $user = Auth::user();
        $khata = Khata::where('id', $id)->where('user_id', $user->id)->first();
        if (!$khata) {
            return response()->json([
                'status' => false,
                'message' => 'Khata not found',
            ], 404);
        }
   
        $validatedData = Validator::make($request->all(),[
            'name' => 'sometimes|required|string|max:255',
            'phone' =>['nullable','max:10',Rule::unique('khatas')->ignore($id)->where(fn($query) => $query->where('user_id', $user->id))],
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validatedData->errors(),
            ], 422);
        }

        try {
            $image = null;
            if($request->hasFile('photo')){
                $imageName = time().'.'.$request->photo->extension();
                $request->photo->move(public_path('images/khata_profile'), $imageName);
                $image = 'images/khata_profile/'.$imageName;
            }
           $res = Khata::where('id', $id)->where('user_id', $user->id)->update([
                'name' => $request->has('name') ? $request->name : $khata->name,
                'phone' => $request->has('phone') ? $request->phone : $khata->phone,
                'address' => $request->has('address') ? $request->address : $khata->address,
                'email' => $request->has('email') ? $request->email : $khata->email,
                'photo' => $image ? $image : $khata->photo,

           ]);

            return response()->json([
                'status' => true,
                'message' => 'Khata updated successfully',
                'data' => $res
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update Khata',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function khataDetails($id)
    {
        $user = Auth::user();
        $data = Transaction::where('khata_id', $id)->where('user_id', $user->id)->orderBy('created_at', 'desc')->limit(10)->get();
        $khata = Khata::where('id', $id)->where('user_id', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'Khata details retrieved successfully',
            'khata' => $khata,
            'data' => $data
           
        ]);
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
