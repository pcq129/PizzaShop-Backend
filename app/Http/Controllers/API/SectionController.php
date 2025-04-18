<?php

namespace App\Http\Controllers\API;

use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $section = Section::with('tables')->get();
        return response()->json([
            'code'=>'200',
            'status'=>'true',
            'data'=>$section,
            'messge'=>'Section data fetched successfully'
        ],200);
    }

    public function waiting_token(){
        $tokens = Section::with(['customers'=>function ($query){
            $query->where('customers.status', 1);
        }])->get();
        return response()->json([
            "code"=>"200",
            "status"=>"true",
            "data"=>$tokens,
            "message"=>"Waiting list fetched successfully"
        ]);
    }




    /**
     * Show the form for storing a new resource.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=> [Rule::Unique('sections', 'name')->withoutTrashed()],
        ],
    $message =
[
    'name.unique' => 'Section already exists'
]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }

        $newSection = new Section();
        $newSection->name = $request->name;
        $newSection->description = $request->description;
        $newSection->save();

        return response()->json([
            'code'=>'200',
            'status'=>'true',
            'messge'=>'New Section added statusfully'
        ],201);
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $section = Section::find($id);
        if($section){
            return response()->json([
                'code' => '200',
                'status' => 'true',
                'data' => $section,
                'message'=> 'Section fetched successfully'
            ], 200);
        }
        return response()->json([
            'code' => '404',
            'status' => 'false',
            'message' => 'Section not found',
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'=> ['required'],
            'name'=> [Rule::Unique('sections', 'name')->ignore($request->id)->withoutTrashed()],
        ],$message = ['name.unique' => 'Section already exists']);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }

        $newSection = Section::find($request->id);
        $newSection->name = $request->name;
        $newSection->description = $request->description;
        $newSection->save();

        return response()->json([
            'code'=>'200',
            'status'=>'true',
            'messge'=>'Section Updated successfully'
        ],201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $section = Section::find($id);
        if ($section) {
            // $section->tables()->where('status','Available')->delete();
            $contains =  $section->with('tables')->where('id', '=', $id)->get();
            // return $contains;

            foreach ($contains->tables as $table) {
                if($table->status === "Occupied"){
                    dd($table);
                }
            }
            // $section->delete();
            return response()->json([
                'code' => '200',
                'status' => 'true',
                'message' => 'Section deleted successfully'
            ], 200);
        }
        return response()->json([
            'code' => '404',
            'status' => 'false',
            'message' => 'Section not found',
        ], 200);
    }
}
