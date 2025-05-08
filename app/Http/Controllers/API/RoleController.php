<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nette\Utils\Validators;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;


class RoleController extends Controller
{


    public function get_roles(){
       try {
        if (!auth()->user()->can('add_edit_role_permission')) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::with('permissions:name')->where('name', '!=','super_admin')->get();

        if($roles->count()>0){
        return Helper::sendResponse('ok', true, $roles, 'Roles fetched successfully');

        }else{
            return Helper::sendResponse('no_content', true, null, 'No Roles available');
        }
       } catch (\Throwable $th) {
        return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching roles');
       }  }

    public function update_role($id, Request $request){
       try {
        if (!auth()->user()->can('add_edit_role_permission')) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'permissions' => ['required', 'array']
        ]);

        if ($validator->fails()) {
            return Helper::sendResponse('bad_request', false,null, $validator->messages()->first());
            // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
        }

        $role = Role::find($id);
        if($role){
        $role->syncPermissions($request->permissions);
        return Helper::sendResponse('ok', true, null, 'Role updated successfully');

        } else{
            return Helper::sendResponse('not_found', true, null, 'Role not found');
        }
       } catch (\Throwable $th) {
        return Helper::sendResponse('error', false, $th->getMessage(), 'Error while updating Role');
       }
    }

}
