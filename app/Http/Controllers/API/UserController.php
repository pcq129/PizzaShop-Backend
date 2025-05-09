<?php

//secret key r5NWpnVfA8Frnmj4raP35bBOW8JncosXkYXWha5Mxkz4LZ9ZBOiudbhAZJCQval8

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\API\AuthController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Helper;


use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       try {
        if (!auth()->user()->can('view_user')) {
            abort(403, 'Unauthorized action.');
        }
        $per_page = $request->perPage;
        $users = User::with(['roles:id,name'])->paginate($per_page);

        return Helper::sendResponse('ok', true, $users, 'Users fetched successfully');
       } catch (\Throwable $th) {
        //throw $th;
        return Helper::sendResponse('error', false, $th->getMessage(), 'Error while fetching Users');

       }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {try {
        if (!auth()->user()->can('add_edit_user')) {
            abort(403, 'Unauthorized action.');
        }

            // {
            //     "first_name": "harmit",
            //     "last_name": "KAFK",
            //     "username": "FAD",
            //     "email": "DF@DAF.COM",
            //     "phone": 787488,
            //     "country": "COUTNRY",
            //     "state": "state",
            //     "city": "city",
            //     "address": "ADD",
            //     "zipcode": 4322,
            //     "role": "user",
            //     "password": "TEST",
            //     "confirmPassword": "TEST"
            // }

            $validator = Validator::make($request->all(), [
                'username' => ['required','string','max:50',Rule::unique('users', 'user_name')->withoutTrashed()],
                'email' => ['required','string','email','max:50',Rule::unique('users', 'email')->withoutTrashed()],
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'phone' => 'required|max_digits:12|min_digits:10|numeric',
                'address' => 'required|string|max:180',
                'password' => 'required|string|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/|min:6',
                'role' => 'string|required',
                'zipcode'=> 'required|numeric',
                'state'=> 'required|max:50',
                'country'=> 'required|max:50',
                'city'=> 'required|max:50',

            ]);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());
                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }

            $user = new User;
            $user->first_name = $request->get('first_name');
            $user->last_name = $request->get('last_name');
            $user->user_name = $request->get('username');
            $user->phone = $request->get('phone');
            $user->email = $request->get('email');
            $user->address = $request->get('address');
            $user->password = bcrypt($request->get('password'));
            $user->zipcode = $request->get('zipcode');
            // if($request->country && $request->state && $request->city){
                $user->state =$request->state;
                $user->country = $request->country;
                $user->city = $request->city;
            // }

            $user->syncRoles([$request->role]);
            $user->save();

            // return response()->json([
            //     'code' => '201',
            //     'status' => 'true',
            //     'message' => 'user added successfully'
            // ],  201);
            return Helper::sendResponse('created', true, null, 'User added successfully');

    } catch (\Throwable $th) {
        //throw $th;
        return Helper::sendResponse('error', false, $th->getMessage(), 'Error while adding User');

    }}

    public function update(Request $request)
    {try {
        if (!auth()->user()->can('add_edit_user')) {
            abort(403, 'Unauthorized action.');
        }

            // {
            //     "first_name": "harmit",
            //     "last_name": "KAFK",
            //     "username": "FAD",
            //     "email": "DF@DAF.COM",
            //     "phone": 787488,
            //     "country": "COUTNRY",
            //     "state": "state",
            //     "city": "city",
            //     "address": "ADD",
            //     "zipcode": 4322,
            //     "role": "user",
            //     "password": "TEST",
            //     "confirmPassword": "TEST"
            // }


            $validator = Validator::make($request->all(), [
                'username' => ['required','string','max:50',Rule::unique('users', 'user_name')->ignore($request->id)->withoutTrashed()],
                'email' => ['required','string','email','max:50',Rule::unique('users', 'email')->ignore($request->id)->withoutTrashed()],
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'phone' => 'required|max_digits:12|min_digits:10|numeric',
                'address' => 'required|string|max:180',
                'role' => 'string|required',
                'zipcode'=> 'required|numeric',
                'state'=> 'required|max:50',
                'country'=> 'required|max:50',
                'city'=> 'required|max:50',

            ]);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());

                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }

            $user = User::find($request->id);
            $user->first_name = $request->get('first_name');
            $user->last_name = $request->get('last_name');
            $user->user_name = $request->get('username');
            $user->phone = $request->get('phone');
            $user->email = $request->get('email');
            $user->address = $request->get('address');
            $user->password = bcrypt($request->get('password'));
            $user->zipcode = $request->get('zipcode');
            // if($request->country && $request->state && $request->city){
                $user->state =$request->state;
                $user->country = $request->country;
                $user->city = $request->city;
            // }
            $role = 'chef';
            switch($role){
                case 1: $role = 'super_admin';
                break;

                case 2: $role = 'account_manager';
                break;

                case 3: $role = 'chef';
                break;

                default:
                $role = 'chef';
            }
            $user->syncRoles([$request->role]);

            $user->save();

            // return response()->json([
            //     'code' => '201',
            //     'status' => 'true',
            //     'message' => 'user updated successfully'
            // ],  201);
            return Helper::sendResponse('ok', true, null, 'User updated successfully');

    } catch (\Throwable $th) {
        //throw $th;
        return Helper::sendResponse('error', false, $th->getMessage(), 'Error while updating User');

    }}

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        try {
            if (!auth()->user()->can('view_user')) {
                abort(403, 'Unauthorized action.');
            }
            $validator = Validator::make($request->all(), [
                'id' => 'required'
            ]);
            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());

                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }

            $user = User::find($request->id)->with(['roles:id, name'])->get();
            if ($user) {
                // return response()->json([
                //     'code' => '200',
                //     'status' => 'true',
                //     'message' => $user,
                // ], 200);
                return Helper::sendResponse('found', true, $user, 'User fetched successfully');
            }
            // return response()->json([
            //     'code' => '404',
            //     'status' => 'true',
            //     'message' => 'not found',
            // ], 200);
            return Helper::sendResponse('not_found', false, null, 'User not found');

        } catch (\Throwable $th) {
            //throw $th;
        return Helper::sendResponse('error', false, $th->getMessage(), 'Error while fetching User');

        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update_user(Request $request)
    {try {
        if (!auth()->user()->can('add_edit_user')) {
            abort(403, 'Unauthorized action.');
        }

            $validator = Validator::make($request->all(), [
                // 'id' => 'required',
                'username' => ['required','string','max:50',Rule::unique('users', 'user_name')->ignore($request->username, 'user_name')->withoutTrashed()],
                'firstName' => 'required|string|max:50',
                'lastName' => 'required|string|max:50',
                'address' => 'required|string|max:180',
                'country'=> 'required|string|max:50',
                'state' => 'required|string|max:50',
                'city'=> 'required|string|max:50',
                'zipcode'=> 'required|numeric'
                // 'password' => 'required|string|confirmed|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/|min:6'
            ]);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());

                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }

            $user = Auth::getUser();
            $user->first_name = $request->get('firstName');
            $user->last_name = $request->get('lastName');
            $user->user_name = $request->get('username');
            $user->country = $request->get('country');
            $user->state = $request->get('state');
            $user->city = $request->get('city');
            $user->zipcode = $request->get('zipcode');
            $user->address = $request->get('address');

            $user->update();
            $token = Auth::refresh();

            // return response()->json([
            //     'code' => '201',
            //     'status' => 'true',
            //     'message' => 'user updated successfully',
            //     'data'=> $token
            // ],  201);
            return Helper::sendResponse('ok',true, $token, 'User updated successfully');

    } catch (\Throwable $th) {
        //throw $th;
        return Helper::sendResponse('error', false, $th->getMessage(), 'Error while updating User details');

    }}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        try {
            if (!auth()->user()->can('delete_user')) {
                abort(403, 'Unauthorized action.');
            }
                $user = User::find($id);
                if ($user) {
                    $user->delete();
                    // return response()->json([
                    //     'code' => '204',
                    //     'status' => 'true',
                    //     'message' => 'user deleted successfully'
                    // ],  200);
                    return Helper::sendResponse('ok', true,null, 'User deleted successfully');
                }
                // return response()->json([
                //     'code' => '404',
                //     'status' => 'false',
                //     'message' => 'user not found'
                // ],  200);
                return Helper::sendResponse('not_found', true, null, 'User not found');

        } catch (\Throwable $th) {
            //throw $th;
        return Helper::sendResponse('error', false, $th->getMessage(), 'Error while deleting User');

        }
    }


    public function update_password(Request $request)
    {

        try {
            // $passwordRegex = '/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#%]).*$/|min:6';
        // inject passwordRegex into validators for final build

        // dd($request);

        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string',
            'confirmPassword' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
        }

        // $user = User::find($request->id);
        $user = Auth::getUser();


        if (Hash::check($request->currentPassword, $user->password)) {
            if ($request->newPassword == $request->confirmPassword) {
                $user->password = bcrypt($request->newPassword);
                $user->update();



                return response()->json([
                    'code' => '204',
                    'status' => 'true',
                    'access_token' => Auth::refresh(),
                    'message' => 'Password updated successfully'
                ],  200);
                // using customer response due to presece of access token
            }
            else{
                // return response()->json([
                //     'code' => '204',
                //     'status' => 'false',
                //     'message' => 'New password and current password mismatch'
                // ],  200);
                return Helper::sendResponse('bad_request', false, null, 'New password and current password mismatch');
            }
        }
        else{
            // return response()->json([
            //     'code' => '204',
            //     'status' => 'false',
            //     'message' => 'Invalid current password'
            // ],  200);
            return Helper::sendResponse('unauthorized', false, null, 'Invalid current password');
        }
        } catch (\Throwable $th) {
            //throw $th;
        return Helper::sendResponse('error', false, $th->getMessage(), 'Error while updating User password');

        }
    }


    public function search_user($search, Request $request){
       try {
        if (!auth()->user()->can('view_user')) {
            abort(403, 'Unauthorized action.');
        }
        $per_page = $request->perPage;
        $table = 'App\Models\User';
        $user = $table::with(['roles:id,name'])->where('user_name', 'like', "%$search%")->paginate($per_page);
        if($user->count()>=1){
            // return response()->json([
            //     'code' => '200',
            //     'status' => 'true',
            //     'data'=> $user,
            //     'message' => 'Users found'
            // ],  200);

            return Helper::sendResponse('ok', true, $user, 'Users found');
        }else{
            return response()->json([
                'code' => '404',
                'status' => 'false',
                'message' => 'Users not found'
            ],  404);
            return Helper::sendResponse('no_content', false, null,'User not found');
        }
       } catch (\Throwable $th) {
        //throw $th;
        return Helper::sendResponse('error', false, $th->getMessage(), 'Error while searching Users');

       }
    }


}
