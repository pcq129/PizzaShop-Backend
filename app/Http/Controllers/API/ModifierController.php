<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use App\Models\ModifierModifierGroup;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class ModifierController extends Controller
{
    //not needed

    // public function getmapper(){
    //     $mappingData = ModifierModifierGroup::query()
    //     ->orderBy('modifier_id')->get();
    //     return response()->json([
    //         'code' => '200',
    //         'status' => 'true',
    //         'data' => $mappingData,
    //         'message'=>'modifiers fetched successfully'
    //     ], 200);
    // }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        try {
            if (!auth()->user()->can('view_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $modifiers = Modifier::with(['ModifierGroups' => function ($query) {
                $query->select('modifier_groups.name');
            }])->get();

            // $modifiers = Modifier::with(['ModifierGroups'])->get();
            // return response()->json([
            //     'code' => '200',
            //     'status' => 'true',
            //     'data' => $modifiers,
            //     'message'=>'Modifiers fetched successfully'
            // ], 200);
            return Helper::sendResponse('ok', true, $modifiers, 'Modifier fetched successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Modifiers');

        }
    }

    public function get_list()
    {

        try {

            if (!auth()->user()->can('view_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $modifier_group = ModifierGroup::all(['name', 'id']);
            return $modifier_group;
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Modifier');

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
                'name' => ['required', 'string', 'max:50', Rule::unique('modifiers', 'name')->withoutTrashed()],
                // 'name' => 'required|string|max:50|unique:App\Models\Modifier,name',
                'description' => 'nullable|string|max:180',
                // 'modifier_group_id' => 'required|min_digits:1|max_digits:3|exists:modifier_groups,id',
                'rate' => 'required|gt:0',
                'quantity' => 'required|gt:0',
                'unit' => 'required|in:grams,pieces'

            ], [
                'name.unique' => 'Please add unique name',
                'name' => 'Invalid name',
            ]);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());

                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }

            $newModifier = new Modifier();
            $newModifier->name = $request->name;
            $newModifier->description = $request->description;
            $newModifier->quantity = $request->quantity;
            $newModifier->rate = $request->rate;
            $newModifier->unit = $request->unit;
            // dd($request->modifier_group_id);
            $newModifier->save();
            $newModifier->ModifierGroups()->sync($request->modifier_group_id);

            // return response()->json([
            //     'code' => '201',
            //     'status' => 'true',
            //     'message' => 'Modifier added successfully'
            // ],  201);
            return Helper::sendResponse('created', true, null, 'Modifier added successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error adding Modifier');

        }
    }

    public function getModifierByModifierGroupId($modifierGroupId, Request $request)
    {

        try {

            if (!auth()->user()->can('view_menu')) {
                abort(403, 'Unauthorized action.');
            }
            // $validator = Validator::make($request->all(), [
            //     'modifierGroupId'=>'required'
            // ]);
            // if($validator->fails()){
            //     return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            // }

            $per_page = $request->perPage;

            $modifiers = ModifierGroup::find($modifierGroupId)->modifiers()->with('ModifierGroups')->paginate($per_page);

            $transformed_modifiers = $modifiers->getCollection()->map(function ($modifier) {
                $modifier->modifier_groups = $modifier->ModifierGroups->pluck('id')->all();
                unset($modifier->ModifierGroups);
                unset($modifier->pivot);
                return $modifier;
            });

            $paginated_modifiers = $modifiers->setCollection($transformed_modifiers);
            // return response()->json([
            //     'code' => '200',
            //     'status' => 'true',
            //     'data' => $paginated_modifiers,
            //     'message'=> 'Modifier fetched successfully'
            // ], 200);
            return Helper::sendResponse('ok', true, $paginated_modifiers, 'Modifier fetched successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Modifier');

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
            //     return response()->json(['code' => 400, 'success' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            // }

            $modifier = Modifier::find($id);
            if ($modifier) {
                // return response()->json([
                //     'code' => '200',
                //     'status' => 'true',
                //     'data' => $modifier,
                //     'message'=> 'Modifier fetched successfully'
                // ], 200);
                return Helper::sendResponse('ok', true, $modifier, 'Modifier fetched successfully');
            }
            // return response()->json([
            //     'code' => '404',
            //     'status' => 'false',
            //     'message' => 'Modifier not found',
            // ], 404);
            return Helper::sendResponse('not_found', false, null, 'Modifiers not found');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Modifier');

        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        try {

            if (!auth()->user()->can('add_edit_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $modifier = Modifier::find($request->id);

            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:50', Rule::unique('modifiers', 'name')->withoutTrashed()->ignore($request->id)],
                // 'name' => 'required|string|max:50|unique:App\Models\Modifier,name,'.$request->id.',id',
                'description' => 'nullable|string|max:180',
                // 'modifier_group_id' => 'required|min_digits:1|max_digits:3|exists:modifier_groups,id',
                'rate' => 'required|gt:0',
                'quantity' => 'required|gt:0',
                'unit' => 'required|in:grams,pieces',
                'id' => 'required'
            ]);


            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());

                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }
            $modifier->name = $request->name;
            $modifier->description = $request->description;
            // $modifier->modifier_group_id = $request->modifier_group_id;
            $modifier->quantity = $request->quantity;
            $modifier->rate = $request->rate;
            $modifier->unit = $request->unit;
            $modifier->update();
            $modifier->ModifierGroups()->sync($request->modifier_group_id);


            // return response()->json([
            //     'code' => '201',
            //     'status' => 'true',
            //     'message' => 'Modifier updated successfully'
            // ],  201);

            return Helper::sendResponse('ok', true, null, 'Modifier updated successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error updating Modifier');

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

            // $validator = Validator::make($id->all(), [
            //     'id'=>'required'
            // ]);
            // if($validator->fails()){
            //     return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            // }


            $modifier = Modifier::find($id);
            if ($modifier) {
                $modifier->delete();
                // return response()->json([
                //     'code' => '204',
                //     'status' => 'true',
                //     'message' => 'Modifier deleted successfully'
                // ],  200);
                return Helper::sendResponse('ok', true, null, 'Modifier deleted successfully');
            }

            // return response()->json([
            //     'code' => '404',
            //     'status' => 'false',
            //     'message' => 'Modifier not found'
            // ],  200);
            return Helper::sendResponse('not_found', false, null, 'Modifier not found');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error deleting Modifier');

        }
    }

    public function search_modifier($search, Request $request)
    {

        try {
            if (!auth()->user()->can('view_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $per_page = $request->perPage;
            $modifier = Modifier::where('name', 'like', "%$search%")->paginate($per_page);
            if ($modifier->count() >= 1) {
                // return response()->json([
                //     'code' => '200',
                //     'status' => 'true',
                //     'data'=> $modifier,
                //     'message' => 'Modifiers found'
                // ],  200);
                return Helper::sendResponse('ok', true, $modifier, 'Modifiers found');
            } else {
                // return response()->json([
                //     'code' => '404',
                //     'status' => 'false',
                //     'message' => 'Modifiers not found'
                // ],  404);
                return Helper::sendResponse('no_content', false, null, 'Modifiers not found');
            }
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error searching Modifier');

        }
    }
}
