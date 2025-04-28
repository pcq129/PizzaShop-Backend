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
        $credentials = $request->only('email', 'password');

        $role = User::with('roles:name')->where('email', '=', $request->email)->first()->roles()->get();
        // Attempt to authenticate user
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'code' => 200,
            'status' => true,
            'role'=>$role,
            'access_token'=>$token,
            'message' => 'Login Successful',

        ]);
        // return $this->respondWithToken($token);
    }

    // User Registration
    public function register(Request $request)
    {
        $encryptPassword = bcrypt($request->password);
        $user = new User();
        $user->password = $encryptPassword;
        $user->first_name = $request->get('first_name');
        $user->last_name = $request->get('last_name');
        $user->user_name = $request->get('user_name');
        $user->phone = $request->get('phone');
        $user->email = $request->get('email');
        $user->address = $request->get('address');
        $user->save();

        $token = Auth::login($user);

        // for saving token in db
        // $user->remember_token = $token;
        // $user->save();

        return $this->respondWithToken($token);
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
