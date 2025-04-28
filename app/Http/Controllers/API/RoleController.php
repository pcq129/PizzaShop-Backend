<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Nette\Utils\Validators;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;


class RoleController extends Controller
{


    public function getRoles(){
        if (!auth()->user()->can('add_edit_role_permission')) {
            abort(403, 'Unauthorized action.');
        }

        $roles = Role::with('permissions:name')->where('name', '!=','super_admin')->get();

        return response()->json([
            'code'=>'200',
            'status'=>'true',
            'data'=>$roles,
            'message'=>'Roles fetched successfully'
        ]);
    }

    public function update_role($id, Request $request){
        if (!auth()->user()->can('add_edit_role_permission')) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'permissions' => ['required', 'array']
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
        }

        $role = Role::find($id);
        $role->syncPermissions($request->permissions);
        return response()->json([
            'code'=>200,
            'status'=>true,
            'message'=>'Role Permission Updated Successfully'
        ]);

    }

}
