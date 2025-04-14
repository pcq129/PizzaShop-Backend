<?php

//secret key r5NWpnVfA8Frnmj4raP35bBOW8JncosXkYXWha5Mxkz4LZ9ZBOiudbhAZJCQval8

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return response()->json([
            'code' => '200',
            'status' => 'true',
            'message' => $users,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string|max:50|unique:App\Models\User,user_name',
            'email' => 'required|string|email|max:50|unique:App\Models\User,email',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'phone' => 'required|max_digits:12|min_digits:10|numeric',
            'address' => 'required|string|max:180',
            'password' => 'required|string|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/|min:6',
            'role' => 'string|max:13',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }

        $user = new User;
        $user->first_name = $request->get('first_name');
        $user->last_name = $request->get('last_name');
        $user->user_name = $request->get('user_name');
        $user->phone = $request->get('phone');
        $user->email = $request->get('email');
        $user->address = $request->get('address');
        $user->password = bcrypt($request->get('password'));
        $user->role = $request->get('role');
        $user->save();

        return response()->json([
            'code' => '201',
            'status' => 'true',
            'message' => 'user added successfully'
        ],  201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }

        $user = User::find($request->id);
        if ($user) {
            return response()->json([
                'code' => '200',
                'status' => 'true',
                'message' => $user,
            ], 200);
        }
        return response()->json([
            'code' => '404',
            'status' => 'true',
            'message' => 'not found',
        ], 200);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'user_name' => 'required|string|max:50|unique:App\Models\User,user_name,' . $request->id . ',id',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'address' => 'required|string|max:180',
            'country'=> 'required|string|max:50',
            'state' => 'required|string|max:50',
            'city'=> 'required|string|max:50',
            'zipcode'=> 'required|numeric'
            // 'password' => 'required|string|confirmed|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }

        $user = Auth::getUser();
        $user->first_name = $request->get('first_name');
        $user->last_name = $request->get('last_name');
        $user->user_name = $request->get('user_name');
        $user->country = $request->get('country');
        $user->state = $request->get('state');
        $user->city = $request->get('city');
        $user->zipcode = $request->get('zipcode');
        $user->address = $request->get('address');
        $user->update();

        return response()->json([
            'code' => '201',
            'status' => 'true',
            'message' => 'user updated successfully'
        ],  201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $user = User::find($request->id);
        if ($user) {
            $user->delete();
            return response()->json([
                'code' => '204',
                'status' => 'true',
                'message' => 'user deleted successfully'
            ],  200);
        }
        return response()->json([
            'code' => '404',
            'status' => 'false',
            'message' => 'user not found'
        ],  200);
    }


    public function update_password(Request $request)
    {

        $passwordRegex = '/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/|min:6';
        // inject passwordRegex into validators for final build



        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string',
            'confirmPassword' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }

        // $user = User::find($request->id);
        $user = Auth::getUser();

        // $2y$10$Oolde.WHc4vCvWRLxkz8mOdadPXj9AqlLTOxc.aM7ZmWFEUyka1qm actualpass
        // $2y$10$acqFPiQW7ZgJUtPDzKBO6uTeDBCNoIlBGL0yVrQhh2ErBQ.mIj7c.


        if (Hash::check($request->currentPassword, $user->password)) {
            if ($request->newPassword == $request->confirmPassword) {
                $user->password = bcrypt($request->newPassword);
                $user->update();

                Auth::logout();
                return response()->json([
                    'code' => '204',
                    'status' => 'true',
                    'message' => 'Password updated successfully'
                ],  200);
            }
            else{
                return response()->json([
                    'code' => '204',
                    'status' => 'false',
                    'message' => 'New password and current password mismatch'
                ],  200);
            }
        }
        else{
            return response()->json([
                'code' => '204',
                'status' => 'false',
                'message' => 'Invalid current password'
            ],  200);
        }
    }
}
