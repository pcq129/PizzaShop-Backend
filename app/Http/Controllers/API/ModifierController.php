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
        if (!auth()->user()->can('view_menu')) {
            abort(403, 'Unauthorized action.');
        }
        $modifiers = Modifier::with(['ModifierGroups'=> function($query){
            $query->select ('modifier_groups.name');
        }])->get();

        // $modifiers = Modifier::with(['ModifierGroups'])->get();
        return response()->json([
            'code' => '200',
            'status' => 'true',
            'data' => $modifiers,
            'message'=>'Modifiers fetched successfully'
        ], 200);
    }

    public function getList(){
        if (!auth()->user()->can('view_menu')) {
            abort(403, 'Unauthorized action.');
        }
        $modifier_group = ModifierGroup::all(['name', 'id']);
        return $modifier_group;
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('add_edit_menu')) {
            abort(403, 'Unauthorized action.');
        }
        $validator = Validator::make($request->all(), [
            'name' => ['required','string','max:50',Rule::unique('modifiers', 'name')->withoutTrashed()],
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
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
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

        return response()->json([
            'code' => '201',
            'status' => 'true',
            'message' => 'Modifier added successfully'
        ],  201);
    }

    public function getModifierByModifierGroupId(Request $request){
        if (!auth()->user()->can('view_menu')) {
            abort(403, 'Unauthorized action.');
        }
        $validator = Validator::make($request->all(), [
            'id'=>'required'
        ]);
        if($validator->fails()){
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
        }

        $modifiers = ModifierGroup::findOrFail($request->id)->modifiers();

        return $modifiers;
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    { if (!auth()->user()->can('view_menu')) {
        abort(403, 'Unauthorized action.');
    }

        // $validator = Validator::make($request->all(), [
        //     'id'=>'required'
        // ]);
        // if($validator->fails()){
        //     return response()->json(['code' => 400, 'success' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
        // }

        $modifier = Modifier::find($id);
        if($modifier){
            return response()->json([
                'code' => '200',
                'status' => 'true',
                'data' => $modifier,
                'message'=> 'Modifier fetched successfully'
            ], 200);
        }
        return response()->json([
            'code' => '404',
            'status' => 'false',
            'message' => 'Modifier not found',
        ], 404);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        if (!auth()->user()->can('add_edit_menu')) {
            abort(403, 'Unauthorized action.');
        }
        $modifier = Modifier::find($request->id);

        $validator = Validator::make($request->all(), [
            'name' => ['required','string','max:50',Rule::unique('modifiers', 'name')->withoutTrashed()->ignore($request->id)],
            // 'name' => 'required|string|max:50|unique:App\Models\Modifier,name,'.$request->id.',id',
            'description' => 'nullable|string|max:180',
            // 'modifier_group_id' => 'required|min_digits:1|max_digits:3|exists:modifier_groups,id',
            'rate' => 'required|gt:0',
            'quantity' => 'required|gt:0',
            'unit' => 'required|in:grams,pieces',
            'id'=> 'required'
        ]);


        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
        }
        $modifier->name = $request->name;
        $modifier->description = $request->description;
        // $modifier->modifier_group_id = $request->modifier_group_id;
        $modifier->quantity = $request->quantity;
        $modifier->rate = $request->rate;
        $modifier->unit = $request->unit;
        $modifier->update();
        $modifier->ModifierGroups()->sync($request->modifier_group_id);


        return response()->json([
            'code' => '201',
            'status' => 'true',
            'message' => 'Modifier updated successfully'
        ],  201);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
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
        if($modifier){
            $modifier->delete();
            return response()->json([
                'code' => '204',
                'status' => 'true',
                'message' => 'Modifier deleted successfully'
            ],  200);
        }

        return response()->json([
            'code' => '404',
            'status' => 'false',
            'message' => 'Modifier not found'
        ],  200);
    }

    public function search_modifier($search){
        if (!auth()->user()->can('view_menu')) {
            abort(403, 'Unauthorized action.');
        }
        $modifier = Modifier::where('name', 'like', "%$search%")->get();
        if($modifier->count()>=1){
            return response()->json([
                'code' => '200',
                'status' => 'true',
                'data'=> $modifier,
                'message' => 'Modifiers found'
            ],  200);
        }else{
            return response()->json([
                'code' => '404',
                'status' => 'false',
                'message' => 'Modifiers not found'
            ],  404);
        }
    }
}
