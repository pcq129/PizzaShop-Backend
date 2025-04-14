<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ModifierGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ModifierGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $modifierGroup = ModifierGroup::with('Modifiers.ModifierGroups')->get();
        return response()->json([
            "code" => "201",
            "status" => "true",
            "data" => $modifierGroup,
            "message" => "Modifier-Group fetched successfully"
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required','string','max:50',Rule::unique('modifier_groups', 'name')->withoutTrashed()],
            // 'name' => 'required|string|max:50|unique:App\Models\ModifierGroup,name',
            'description' => 'nullable|string|max:180',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }

        $modifierGroup = new ModifierGroup();
        $modifierGroup->name = $request->name;
        $modifierGroup->description = $request->description;
        $modifierGroup->save();
        $modifierGroup->modifiers()->sync($request->modifiers);

        return response()->json([
            'code' => '201',
            'status' => 'true',
            'message' => 'Modifier-Group added successfully'
        ],  201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        // $validator = Validator::make($request->all(), [
        //     'id'=>'required'
        // ]);
        // if($validator->fails()){
        //     return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        // }

        $modifierGroup = ModifierGroup::with('Modifiers')->find($id);
        if ($modifierGroup) {
            return response()->json([
                'code' => '200',
                'status' => 'true',
                'data' => $modifierGroup,
                'message' => 'Modifier-Group fetched successfully'
            ], 200);
        }
        return response()->json([
            'code' => '404',
            'status' => 'true',
            'message' => 'Modifier-Group not found',
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        $modifierGroup = ModifierGroup::find($request->id);



        $validator = Validator::make($request->all(), [
            'name' => ['required','string','max:50',Rule::unique('modifier_groups', 'name')->withoutTrashed()->ignore($request->id)],
            // 'name' => 'required|string|max:50|unique:App\Models\ModifierGroup,name,' . $request->id . ',id',
            'description' => 'nullable|string|max:180',
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }


        if ($modifierGroup) {
            $modifierGroup->name = $request->name;
            $modifierGroup->description = $request->description;
            $modifierGroup->update();
        $modifierGroup->modifiers()->sync($request->modifiers);


            return response()->json([
                'code' => '201',
                'status' => 'true',
                'message' => 'Modifier-Group updated successfully'
            ],  201);
        }
        return response()->json([
            'code' => '404',
            'status' => 'true',
            'message' => 'Modifier-Group not found'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $modifierGroup = ModifierGroup::find($id);
        if ($modifierGroup) {
            $modifierGroup->delete();

            return response()->json([
                'code' => '204',
                'status' => 'true',
                'message' => 'Modifier-Group deleted successfully'
            ],  200);
        }
        return response()->json([
            'code' => '404',
            'status' => 'false',
            'message' => 'Modifier-Group not found'
        ],  200);
    }
}
