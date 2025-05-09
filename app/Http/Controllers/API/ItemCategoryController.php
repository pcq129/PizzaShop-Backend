<?php

namespace App\Http\Controllers\API;

use App\Models\ItemCategory;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;

class ItemCategoryController extends Controller
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
            $categories = ItemCategory::with('Items.ModifierGroups')->get();
            // return response()->json([
            //     'code' => '200',
            //     'status' => 'true',
            //     'data'=> $categories,
            //     'message' => 'Categories fetched successfully',
            // ], 200);
            return Helper::sendResponse('ok', true, $categories, 'Categories fetched successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Item-categories');

        }
    }


    // function for getting categories but with only name and id
    public function get_list()
    {



        try {



            $categories = ItemCategory::all(['name', 'id']);
            // return $categories;
            return Helper::sendResponse('ok', true, $categories, 'Categories fetched successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Item-categories');

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
                'name' => ['required', 'string', 'max:50', Rule::unique('item_categories', 'name')->withoutTrashed()],
                'description' => 'string|nullable|max:180',
            ], [
                'name.unique' => 'category with the same name already exists',
                'name' => 'invalid name',
                'description.max' => 'description should be of maximum 180 characters'
            ]);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());

                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }



            $item_category = new ItemCategory();
            $item_category->name = $request->name;
            $item_category->description = $request->description;
            $item_category->save();

            // return response()->json([
            //     'code' => '201',
            //     'status' => 'true',
            //     'message' => 'Category added successfully'
            // ],  201);
            return Helper::sendResponse('ok', true, null, 'Category added successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error adding Item-category');

        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {



        try {



            // $validator = Validator::make($request->all(), [
            //     'id'=>'required'
            // ]);
            // if($validator->fails()){
            //     return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
            // }

            if (!auth()->user()->can('view_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $itemCategory = ItemCategory::find($id);
            if ($itemCategory) {
                // return response()->json([
                //     'code' => '200',
                //     'status' => 'true',
                //     'data' => $itemCategory,
                //     'message' => 'Category fetched successfully'
                // ], 200);
                return Helper::sendResponse('ok', true, $itemCategory, 'Catgory fetched successfully');
            }
            // return response()->json([
            //     'code' => '404',
            //     'status' => 'true',
            //     'message' => 'Category not found'
            // ], 204);
            return Helper::sendResponse('no_content', false, nul, 'Category not found');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Item-category');

        }
    }

    // /**
    //  * Update the specified resource in storage.
    //  */
    public function update(Request $request)
    {



        try {

            if (!auth()->user()->can('add_edit_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $itemCategory = ItemCategory::find($request->id);



            $validator = Validator::make($request->all(), [
                // 'name' =>
                'name' => ['required', 'string', 'max:50', Rule::unique('item_categories', 'name')->withoutTrashed()->ignore($request->id)],
                // 'name' => Rule::unique('ItemCategory', 'name')->ignore($ItemCategory->name)->whereNull('deleted_at')->orWhereNotNull('deleted-at');
                'description' => 'string|nullable|max:180',
            ], [
                'name.unique' => 'category with the same name already exists',
                'name' => 'invalid name',
                'description.max' => 'description should be of maximum 180 characters'
            ]);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());

                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }


            if ($itemCategory) {
                $itemCategory->name = $request->name;
                $itemCategory->description = $request->description;
                $itemCategory->update();

                // return response()->json([
                //     'code' => '201',
                //     'status' => 'true',
                //     'message' => 'Category updated successfully'
                // ],  201);
                return Helper::sendResponse('ok', true, null, 'Category updated successfully');
            }
            // return response()->json([
            //     'code' => '404',
            //     'status' => 'true',
            //     'message' => 'not found'
            // ], 200);
            return Helper::sendResponse('not_found', true, null, 'Category not found');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error updaging Item-category');

        }
    }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    public function destroy($id)
    {



        try {



            if (!auth()->user()->can('delete_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $itemCategory = ItemCategory::find($id);
            if ($itemCategory) {
                $itemCategory->items()->delete();
                $itemCategory->delete();

                // return response()->json([
                //     'code' => '204',
                //     'status' => 'true',
                //     'message' => 'Category deleted successfully'
                // ],  200);
                return Helper::sendResponse('ok', true, null, 'Category deleted successfully');
            }
            // return response()->json([
            //     'code' => '404',
            //     'status' => 'false',
            //     'message' => 'Category not found'
            // ],  200);
            return Helper::sendResponse('nog_found', false, null, 'Category not found');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error deleting Item-category');

        }
    }
}
