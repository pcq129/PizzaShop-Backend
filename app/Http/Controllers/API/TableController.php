<?php

namespace App\Http\Controllers\API;

use App\Models\Table;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;


class TableController extends Controller
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
            $table = Table::all();
            if ($table->count() > 0) {
                return Helper::sendResponse('ok', true, $table, 'Tables fetched successfully');
            } else {
                return Helper::sendResponse('no_content', true, null, 'No Table records available');
            }
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Tables');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index_by_section($id, Request $request)
    {
        try {
            if (!auth()->user()->can('view_table')) {
                abort(403, 'Unauthorized action.');
            }
            $per_page = $request->perPage;
            $section = Table::where('section_id', $id)->paginate($per_page);
            return Helper::sendResponse('ok', true, $section, 'Tables from Section fetched successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Tables from Section');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            if (!auth()->user()->can('add_edit_table')) {
                abort(403, 'Unauthorized action.');
            }
            // dd($request->name);
            $validator = Validator::make($request->all(), [
                'name' => [Rule::Unique('tables', 'name')->withoutTrashed(), 'required'],
                'status' => ['required', Rule::in(['Available', 'Running', 'Assigned'])],
                'capacity' => ['required', 'numeric', 'between:1,25'],
                'section_id' => ['numeric', 'lt:99'],
            ], $message = ['name.unique' => 'Table already exists']);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());
            }

            $table = new Table();
            $table->name = $request->name;
            $table->status = $request->status;
            $table->capacity = $request->capacity;
            if ($request->section_id) {
                $section = Section::find($request->section_id);
                $table->section()->associate($section);
            }
            $table->save();

            return Helper::sendResponse('created', true, null, 'Table added successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error adding Table');
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
            $table = Table::find($id);
            if ($table) {
                return Helper::sendResponse('ok', true, $table, 'Tables fetched successfully');
            } else {
                return Helper::sendResponse('not_found', false, null, 'Table not found');
            }
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Table');
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
                'name' => [Rule::Unique('tables', 'name')->ignore($request->id)->withoutTrashed(), 'required'],
                'status' => ['required', Rule::in(['Available', 'Running', 'Assigned'])],
                'capacity' => ['required', 'numeric', 'between:1,25'],
                'section_id' => ['numeric', 'lt:99'],
            ], $message = ['name.unique' => 'Table already exists']);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());
            }

            $table = Table::findOrFail($request->id);
            $table->name = $request->name;
            $table->status = $request->status;
            $table->capacity = $request->capacity;
            $table->section()->dissociate();
            $section = Section::find($request->section_id);
            $table->section()->associate($section);
            $table->save();

            return Helper::sendResponse('ok', true, null, 'Table updated successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error updating Table');
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
            $table = Table::find($id);


            if ($table && $table->status == "Available") {
                $table->delete();
                return Helper::sendResponse('ok', true, null, 'Table deleted successfully');
            } else if ($table && $table->status == "Running") {
                return Helper::sendResponse('ok', false, null, 'Table cannot be deleted when occupied');
            }

            return Helper::sendResponse('not_found', false, null, 'Table not found');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error deleting Table');
        }
    }

    public function search_table($search, Request $request)
    {
        try {
            if (!auth()->user()->can('view_table')) {
                abort(403, 'Unauthorized action.');
            }

            $per_page = $request->perPage;
            $tables = Table::where('name', 'like', "%$search%")->paginate($per_page);
            if ($tables->count() >= 1) {

                return Helper::sendResponse('302', true, $tables, 'Tables found');
            } else {

                return Helper::sendResponse('204', false, null, 'Tables not found');
            }
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error searching Table');
        }
    }
}
