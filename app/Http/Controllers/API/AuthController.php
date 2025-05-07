<?php

namespace App\Http\Controllers\API;



use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Casts\Json;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'forgot_password', 'reset_password']]);
    }

    // User Login & Generate JWT Token
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            // dd($credentials);
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['code' => 401, 'status' => false, 'message' => 'Unauthorized'], 401);
            }

            $role = User::with('roles:name')->where('email', '=', $request->email)->first()->roles()->get();

            return response()->json([
                'code' => 200,
                'status' => true,
                'role' => $role,
                'access_token' => $token,
                'message' => 'Login Successful',
            ]);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'status' => false, 'message' => 'Server error during login'], 500);
        }
    }

    // User Registration
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'user_name' => 'required|string|max:255|unique:users,user_name',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6|confirmed',
                'phone' => 'required|string',
                'address' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['code' => 400, 'status' => false, 'message' => $validator->messages()], 400);
            }

            $user = new User();
            $user->password = bcrypt($request->password);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->user_name = $request->user_name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            $user->address = $request->address;
            $user->save();

            $token = Auth::login($user);

            return $this->respondWithToken($token);
        } catch (\Exception $e) {
            return response()->json(['code' => 500, 'status' => false, 'message' => 'Server error during registration'], 500);
        }
    }

    // Get Authenticated User
    public function me()
    {
        return response()->json(Auth::getUser());
    }

    // Logout User
    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    // Refresh Token
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    // Format Token Response
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }




    // function for handling forgot password form data
    public function forgot_password(Request $request)
    {
       try{
        $request->validate(['email' => 'required|email']);
        // dd('data');
        $status = Password::sendResetLink(

            $request->only('email')


        );
        // dd($request);

        if ($status) {
            return response()->json([
                "status" => $status,
            ]);
        } else {
            return response()->json([
                "status" => "not found",
                "message" => "User not found"

            ]);
        }
       }catch(\Exception $e){
        
       }
    }


    // function for handling reset password form data
    public function reset_password(Request $request)
    {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'password' => ['required'],
            'password_confirmation' => ['required'],
            'token' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }

        $password = $request->password;

        // http://localhost:4200/login/change-password?token=ae01a004c2b8c367336ff2c474185f63aee587214f235cebe03d701de4f0851d&email=froigreunneubruri-6897%40yopmail.com
        $status = Password::reset(

            $request->only('email', 'password', 'password_confirmation', 'token'),

            function (User $user, string $password) {
                // dd($password);
                // dd($user);

                $user->forceFill([

                    'password' => bcrypt($password)

                ])->setRememberToken(Str::random(60));



                $user->save();



                event(new PasswordReset($user));
            }

        );



        return response()->json([
            'status' => $status,
            'password' => $password
        ]);
    }
}
