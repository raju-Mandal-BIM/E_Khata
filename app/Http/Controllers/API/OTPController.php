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
                      
                        // $result = $this->otpService->sendOtp(
                        //     $request->phone,
                        //     'login',
                        //     $request->ip(),
                        //     $request->userAgent()
                        // );

                        // // Store demo OTP for local environment
                        // if (app()->environment('local', 'testing')) {
                        //     Session::put('demo_otp', '123456');
                        // }
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
            
                // $result = $this->otpService->sendOtp(
                //     $request->phone,
                //     'login',
                //     $request->ip(),
                //     $request->userAgent()
                // );
                // // Store verification data in session
                // Session::put('otp_verification', [
                //     'phone' => $request->phone,
                //     'expires_at' => now()->addMinutes(5)
                // ]);   

                //   // Store demo OTP for local environment
                // if (app()->environment('local', 'testing')) {
                //     Session::put('demo_otp', '123456');
                // }
                
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
                            "data"=>Session::get('otp_verification')
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

        // if (!Session::has('otp_verification')) {
        //     return response()->json([
        //         "status"=>false,
        //         "message"=>"No OTP verification session found. Please request a new OTP.",
        //         'redirect_to' => '/login',
        //         "data"=>Session::get('otp_verification')
        //     ], 401);
        // }

        // $verificationData = Session::get('otp_verification');

        try {
            // $result = $this->otpService->verifyOtp(
            //     $verificationData['phone'],
            //     $request->otp,
            //     'login',
            //     $request->ip(),
            //     $request->userAgent()
            // );
           
            // // Clear OTP session data
            // Session::forget('otp_verification');
            // Session::forget('demo_otp');

            // if (!isset($verificationData['user_id'])) {
            //     // For new users, store phone in session and redirect to registration
            //     Session::put('registration_phone', $verificationData['phone']);
            //     Session::put('registration_verified', true);

            //     return redirect()->route('api.register')
            //         ->with('success', 'Phone verified successfully! Please complete your registration.');
            // } else {
            //     // For existing users, login and redirect to dashboard
            //     return response()->json([
            //             "status"=>true,
            //             "message"=>"otp verified hahah successfully",
            //         ]);
            //     $user = $result['user'];
            //     Auth::login($user);

            //     // Clear any temporary user data if exists
            //     if (!$user->is_active) {
            //         $user->update([
            //             'is_active' => true,
            //             'name' => 'User_' . $user->phone, // Set a temporary name
            //         ]);
            //     }

            //     return redirect()->route('dashboard')
            //         ->with('success', 'Welcome back, ' . ($user->name ?? 'User') . '!');
            // }
            // $CountryCode =CountryCode::create([
            //     'country_name' => 'Nepal',
            //     'country_code' => 977,
              
            // ]);
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
                    'country_code_id' => 1,
                    'is_active' => true,
                ]);
                }
             
                Auth::login($user);
                OTP::where('phone', $request->phone)->delete();

                return response()->json([
                    "status"=>true,
                    "message"=>"otp verified successfully",
                    "code"=>Auth::user()->id,
                ]);
                    
               
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

}
