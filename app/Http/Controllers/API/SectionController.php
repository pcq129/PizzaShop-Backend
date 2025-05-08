<?php

namespace App\Http\Controllers\API;

use App\Models\Section;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;


class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if (!auth()->user()->can('view_table')) {
                abort(403, 'Unauthorized action.');
            }

            $section = Section::with('tables')->get();
            if($section->count()>0){
                return Helper::sendResponse('ok', true, $section, 'Sections fetched successfully');
            }else{
                return Helper::sendResponse('no_content', true, null, 'No Section records available');
            }
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Sections');
        }
    }

    public function waiting_token()
    {


        try {
            if (!auth()->user()->can('view_table')) {
                abort(403, 'Unauthorized action.');
            }
            $tokens = Section::with(['customers' => function ($query) {
                $query->where('customers.status', 1);
            }])->get();
            return response()->json([
                "code" => "200",
                "status" => "true",
                "data" => $tokens,
                "message" => "Waiting list fetched successfully"
            ]);
            return Helper::sendResponse('ok', true, $tokens, 'Waititng list fetched successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Waiting Tokens');
        }
    }




    /**
     * Show the form for storing a new resource.
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->user()->can('view_table')) {
                abort(403, 'Unauthorized action.');
            }
            $validator = Validator::make(
                $request->all(),
                [
                    'name' => [Rule::Unique('sections', 'name')->withoutTrashed()],
                ],
                $message =
                    [
                        'name.unique' => 'Section already exists'
                    ]
            );

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->message()->first());
            }

            $newSection = new Section();
            $newSection->name = $request->name;
            $newSection->description = $request->description;
            $newSection->save();

            return response()->json([
                'code' => '200',
                'status' => 'true',
                'messge' => 'New Section added statusfully'
            ], 201);
            return Helper::sendResponse('created', true, null, 'New section added successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error while adding Section');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            if (!auth()->user()->can('view_table')) {
                abort(403, 'Unauthorized action.');
            }
            $section = Section::find($id);
            if ($section) {
                return response()->json([
                    'code' => '200',
                    'status' => 'true',
                    'data' => $section,
                    'message' => 'Section fetched successfully'
                ], 200);
                return Helper::sendResponse('found', true, $section, 'Section fetched successfully');
            }
            return response()->json([
                'code' => '404',
                'status' => 'false',
                'message' => 'Section not found',
            ], 404);
            return Helper::sendResponse('not_found', false, null, 'Section not found');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error while fetching Section');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            if (!auth()->user()->can('add_edit_table')) {
                abort(403, 'Unauthorized action.');
            }
            $validator = Validator::make($request->all(), [
                'id' => ['required'],
                'name' => [Rule::Unique('sections', 'name')->ignore($request->id)->withoutTrashed()],
            ], $message = ['name.unique' => 'Section already exists']);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());
                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }

            $newSection = Section::find($request->id);
            $newSection->name = $request->name;
            $newSection->description = $request->description;
            $newSection->save();

            return response()->json([
                'code' => '200',
                'status' => 'true',
                'messge' => 'Section Updated successfully'
            ], 201);
            return Helper::sendResponse('ok', true, null, 'Section updated successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error updating Section');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

            if (!auth()->user()->can('delete_table')) {
                abort(403, 'Unauthorized action.');
            }
            $section = Section::find($id);
            if ($section) {
                // $section->tables()->where('status','Available')->delete();
                $contains =  $section->with('tables')->where('id', '=', $id)->get();
                // return $contains;

                foreach ($contains->tables as $table) {
                    if ($table->status === "Occupied") {
                        dd($table);
                    }
                }

                return Helper::sendResponse('ok', true, null, 'Section deleted successfully');
            } else {
                return Helper::sendResponse('not_found', false, null, 'Section not found');
            }
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error while deleting Section');
        }
    }
}
