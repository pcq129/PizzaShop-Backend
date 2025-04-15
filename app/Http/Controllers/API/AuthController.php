<?php

namespace App\Http\Controllers\API;



use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    // User Login & Generate JWT Token
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Attempt to authenticate user
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
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
}

