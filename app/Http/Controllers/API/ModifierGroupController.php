<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ModifierGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class ModifierGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try {
            if (!auth()->user()->can('view_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $modifierGroup = ModifierGroup::with('Modifiers.ModifierGroups')->get();
            // return response()->json([
            //     "code" => "201",
            //     "status" => "true",
            //     "data" => $modifierGroup,
            //     "message" => "Modifier-Group fetched successfully"
            // ]);
            return Helper::sendResponse('ok', true, $modifierGroup, 'Modifier Groups fetched successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Modifier Groups');

        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            if (!auth()->user()->can('add_edit_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:50', Rule::unique('modifier_groups', 'name')->withoutTrashed()],
                // 'name' => 'required|string|max:50|unique:App\Models\ModifierGroup,name',
                'description' => 'nullable|string|max:180',
            ]);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());

                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }

            $modifierGroup = new ModifierGroup();
            $modifierGroup->name = $request->name;
            $modifierGroup->description = $request->description;
            $modifierGroup->save();
            $modifierGroup->modifiers()->sync($request->modifiers);

            // return response()->json([
            //     'code' => '201',
            //     'status' => 'true',
            //     'message' => 'Modifier-Group added successfully'
            // ],  201);
            return Helper::sendResponse('ok', true, null, 'Modifier Group added successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error adding  Modifier Groups');

        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        try {
            if (!auth()->user()->can('view_menu')) {
                abort(403, 'Unauthorized action.');
            }
            // $validator = Validator::make($request->all(), [
            //     'id'=>'required'
            // ]);
            // if($validator->fails()){
            //     return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            // }

            $modifierGroup = ModifierGroup::with('Modifiers')->find($id);
            if ($modifierGroup) {
                // return response()->json([
                //     'code' => '200',
                //     'status' => 'true',
                //     'data' => $modifierGroup,
                //     'message' => 'Modifier-Group fetched successfully'
                // ], 200);
                return Helper::sendResponse('ok', true, $modifierGroup, 'Modifier Groups fetched successfully');
            }
            // return response()->json([
            //     'code' => '404',
            //     'status' => 'true',
            //     'message' => 'Modifier-Group not found',
            // ], 200);
            return Helper::sendResponse('no_content', false, null, 'Modifier Group not found');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Modifier Group');

        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        try {
            //code...
            if (!auth()->user()->can('add_edit_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $modifierGroup = ModifierGroup::find($request->id);



            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:50', Rule::unique('modifier_groups', 'name')->withoutTrashed()->ignore($request->id)],
                // 'name' => 'required|string|max:50|unique:App\Models\ModifierGroup,name,' . $request->id . ',id',
                'description' => 'nullable|string|max:180',
            ]);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());

                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }


            if ($modifierGroup) {
                $modifierGroup->name = $request->name;
                $modifierGroup->description = $request->description;
                $modifierGroup->update();
                $modifierGroup->modifiers()->sync($request->modifiers);


                // return response()->json([
                //     'code' => '201',
                //     'status' => 'true',
                //     'message' => 'Modifier-Group updated successfully'
                // ],  201);
                return Helper::sendResponse('ok', true, null, 'Modifier Group updated successfully');
            }
            // return response()->json([
            //     'code' => '404',
            //     'status' => 'true',
            //     'message' => 'Modifier-Group not found'
            // ], 200);
            return Helper::sendResponse('not_found', false, null, 'Modifier Group not found');
        } catch (\Throwable $th) {
            //throw $th;
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error updating Modifier Groups');

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

        if (!auth()->user()->can('delete_menu')) {
            abort(403, 'Unauthorized action.');
        }
        $modifierGroup = ModifierGroup::find($id);
        if ($modifierGroup) {
            $modifierGroup->delete();

            // return response()->json([
            //     'code' => '204',
            //     'status' => 'true',
            //     'message' => 'Modifier-Group deleted successfully'
            // ],  200);
            return Helper::sendResponse('ok', true, null, 'Modifier Group deleted successfully');
        }
        // return response()->json([
        //     'code' => '404',
        //     'status' => 'false',
        //     'message' => 'Modifier-Group not found'
        // ],  200);
        return Helper::sendResponse('not_found', false, null, 'Modifier Group not found');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error deleting Modifier Groups');
        }



    }
}
