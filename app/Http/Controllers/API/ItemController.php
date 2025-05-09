<?php

namespace App\Http\Controllers\API;

use App\Models\Item;
use App\Models\ModifierGroup;
use App\Models\Modifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use App\Enums\ServerStatus;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Helper;

class ItemController extends Controller
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
            $items = Item::with(['Category' => function ($query) {
                $query->select('item_categories.id', 'name');
            }, 'ModifierGroups' => function ($query) {
                $query->select('modifier_groups.id', 'name');
            }])->orderBy('created_at', 'desc')->get();
            // return response()->json([
            //     'code' => '200',
            //     'status' => 'true',
            //     'data' => $items,
            //     'message' => 'Items fetched successfully'
            // ], 200);
            return Helper::sendResponse('ok', true, $items, 'Items fetched successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Items');
        }
    }


    public function category_items($categoryId, Request $request)
    {
        try {
            if (!auth()->user()->can('view_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $per_page = $request->perPage;
            $items = Item::where('category_id', $categoryId)->with('ModifierGroups:id')->paginate($per_page);

            $transformed_items = $items->getCollection()->map(function ($item) {
                // Add modifier_group_ids to the item
                $item->modifier_group_ids = $item->ModifierGroups->pluck('id')->all();
                // Optionally remove the full modifierGroups if you only want the IDs
                unset($item->ModifierGroups);
                return $item;
            });

            $paginated_items = $items->setCollection($transformed_items);

            if ($paginated_items->count() > 0) {
                return Helper::sendResponse('ok', true, $paginated_items, 'Items fetched successfully');
            } else {
                return Helper::sendResponse('no_content', false, null, 'Items records not available');
            }
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Items');
        }
    }


    public function favourite_items()
    {
        try {
            if (!auth()->user()->can('view_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $items = Item::where('is_favourite', true)->get();
            if ($items->count() > 0) {
                return Helper::sendResponse('ok', true, $items, 'Favourite Items fetched successfully');
            } else {
                return Helper::sendResponse('no_content', false, null, 'No favourite Items found');
            }
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching favourite Items');
        }
    }


    public function add_to_favourite($id)
    {
        try {
            $item = Item::find($id);
            if ($item) {
                $item->is_favourite = !$item->is_favourite;
                $item->save();

                if ($item->is_favourite) {
                    return Helper::sendResponse('ok', true, null, 'Item added to favrouites');
                } else {
                    return Helper::sendResponse('ok', true, null, 'Item removed from favrouites');
                }
            } else {
                return Helper::sendResponse('not_found', false, null, 'Item not found');
            }
        } catch (\Throwable $th) {
            return Helper::sendResponse('bad_request', false, $th->getMessage(), 'Error while adding Item to favrourites');
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
                'name' => ['required', 'string', 'max:50', Rule::unique('items', 'name')->withoutTrashed()],
                'rate' => 'required|numeric',
                'unit' => ['required', Rule::in(['pcs', 'gms'])],
                'image' => 'nullable|string',
                'quantity' => 'required|numeric',
                'item_type' => ['required', Rule::in(['veg', 'non-veg', 'vegan'])],
                'available' => ['required', 'boolean'],
                'short_code' => ['numeric', 'nullable'],
                'default_tax' => ['required', 'boolean'],
                'category_id' => 'required|min_digits:1|max_digits:3|exists:item_categories,id',
                'description' => 'string|nullable|max:180',
                'tax_percentage' => 'numeric|min:0|max:60',
                'modifier_groups_id' => ['nullable'],
            ], [
                'name' => 'Invalid name',
                'rate' => 'Invalid price',
                'name.unique' => 'Please add unique name',
            ]);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());

                // return response()->json(['code' => 400, 'status' => 'false', 'message' => ($firstError = $validator->messages()->first()),], 200);
            }





            $newItem = new Item();
            $newItem->name = $request->name;
            $newItem->description = $request->description;
            $newItem->category_id = $request->category_id;
            $newItem->quantity = $request->quantity;
            $newItem->rate = $request->rate;
            $newItem->tax_percentage = $request->tax_percentage;
            $newItem->unit = $request->unit;
            $newItem->item_type = $request->item_type;
            $newItem->available = $request->available;
            $newItem->short_code = $request->short_code;
            $newItem->image = $request->image;
            $newItem->default_tax = $request->default_tax;
            $newItem->save();
            if ($request->modifier_groups_id) {
                $newItem->ModifierGroups()->sync($request->modifier_groups_id);
            }

            // return response()->json([
            //     'code' => '201',
            //     'status' => 'true',
            //     'message' => 'Item added successfully'
            // ],  201);
            return Helper::sendResponse('ok', true, null, 'Item added successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error adding Items');
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


            $item = Item::with(['ModifierGroups.Modifiers'])->find($id);
            if ($item) {
                // return response()->json([
                //     'code' => '200',
                //     'status' => 'true',
                //     'data' => $item,
                //     'message' => 'Item fetched successfully'
                // ], 200);
                return Helper::sendResponse('ok', true, $item, 'Itm fetched successfully');
            }
            // return response()->json([
            //     'code' => '404',
            //     'status' => 'false',
            //     'message' => 'Item not found',
            // ], 404);
            return Helper::sendResponse('no_content', false, null, 'Item not found');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Item');
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
            $item = Item::find($request->id);
            // dd($request);
            $validator = Validator::make($request->all(), [
                'id' => ['required'],
                'name' => ['required', 'string', 'max:50', Rule::unique('items', 'name')->ignore($request->id, 'id')->withoutTrashed()],
                'rate' => 'required|numeric',
                'unit' => ['required', Rule::in(['pcs', 'gms'])],
                'image' => 'nullable|string',
                'quantity' => 'required|numeric',
                'item_type' => ['required', Rule::in(['veg', 'non-veg', 'vegan'])],
                'available' => ['required', 'boolean'],
                'short_code' => ['numeric', 'nullable'],
                'default_tax' => ['required', 'boolean'],
                'category_id' => 'required|min_digits:1|max_digits:3|exists:item_categories,id',
                'description' => 'string|nullable|max:180',
                'tax_percentage' => 'numeric|min:0|max:60',
                'modifier_groups_id' => ['nullable'],
                // 'name' => ['required','string','max:50',Rule::unique('item_categories', 'name')->withoutTrashed()->ignore($request->id)],
                // // 'name' => 'required|string|max:50|unique:App\Models\Item,name,'.$request->id.',id',
                // 'description' => 'nullable|string|max:180',
                // 'category_id' => 'required|min_digits:1|max_digits:3|exists:item_categories,id',
                // 'rate' => 'required|numeric',
                // 'quantity' => 'required|numeric',
                // 'tax' => 'required|numeric',
                // // 'unit'=> [Rule::enum(ServerStatus::class)->only([ServerStatus::gms, ServerStatus::pcs])],
                // 'unit' => 'required'
            ], [
                'name->unique' => 'Please provide a unique name',
                // 'name'=> 'invalid',/
                'rate' => 'Invalid price'
            ]);


            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());

                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }
            $item = Item::find($request->id);
            $item->name = $request->name;
            $item->description = $request->description;
            $item->category_id = $request->category_id;
            $item->quantity = $request->quantity;
            $item->rate = $request->rate;
            $item->tax_percentage = $request->tax_percentage;
            $item->unit = $request->unit;
            $item->item_type = $request->item_type;
            $item->available = $request->available;
            $item->short_code = $request->short_code;
            if ($request->image) {
                $item->image = $request->image;
            }
            $item->default_tax = $request->default_tax;
            $item->update();
            if ($request->modifier_groups_id) {
                $item->ModifierGroups()->sync($request->modifier_groups_id);
            }

            // $item->name = $request->name;
            // $item->description = $request->description;
            // $item->category_id = $request->category_id;
            // $item->quantity = $request->quantity;
            // $item->rate = $request->rate;
            // $item->tax = $request->tax;
            // $item->unit = $request->unit;
            // $item->update();

            // return response()->json([
            //     'code' => '201',
            //     'status' => 'true',
            //     'message' => 'Item updated successfully'
            // ],  201);
            return Helper::sendResponse('ok', true, null, 'Item updated successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error updating Items');
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
            $item = Item::find($id);
            if ($item) {
                $item->delete();
                // return response()->json([
                //     'code' => '204',
                //     'status' => 'true',
                //     'message' => 'Item deleted successfully'
                // ],  200);
                return Helper::sendResponse('ok', true, null, 'Item deleted successfully');
            }

            // return response()->json([
            //     'code' => '404',
            //     'status' => 'false',
            //     'message' => 'Item not found'
            // ],  200);
            return Helper::sendResponse('not_found', false, null, 'Item not found');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error deleting Items');
        }
    }

    public function image(Request $request)
    {



        try {



            if (!auth()->user()->can('add_edit_menu')) {
                abort(403, 'Unauthorized action.');
            }
            if (!$request->hasFile('image')) {
                return Helper::sendResponse('bad_request', false, null, 'No file uploaded');
                // return response()->json(['message' => 'No file uploaded'], 400);
            }

            $file = $request->file('image');
            $path = $file->store('uploads', 'public'); // Saves in storage/app/public/uploads
            $filename = basename($path);
            return Helper::sendResponse('ok', true, $filename, 'Image uploaded successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error uploading image');
        }

        // return response()->json(['message' => 'Image uploaded successfully', 'path' => $filename], 200);
    }

    public function removeImage($image)
    {



        try {
            $file_path = public_path('storage\\uploads\\' . $image);
            if (File::exists($file_path)) {
                File::delete($file_path);
                // return response()->json([
                //     "message" => "Previous image deleted"
                // ]);
                return Helper::sendResponse('ok', true, null, "Previous image deleted");
            } else {
                // return response()->json([
                //     "message" => "file not found"
                // ]);
                return Helper::sendResponse('ok', false, null, "Previous image not found");
            }
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error deleting image');
        }
    }

    public function search_item($search, Request $request)
    {



        try {
            if (!auth()->user()->can('view_menu')) {
                abort(403, 'Unauthorized action.');
            }
            $per_page = $request->perPage;
            $item = Item::where('name', 'like', "%$search%")->paginate($per_page);
            if ($item->count() >= 1) {
                // return response()->json([
                //     'code' => '200',
                //     'status' => 'true',
                //     'data' => $item,
                //     'message' => 'Items found'
                // ],  200);
                return Helper::sendResponse('found', true, $item, 'Items found');
            } else {
                // return response()->json([
                //     'code' => '404',
                //     'status' => 'false',
                //     'message' => 'Items not found'
                // ],  404);
                return Helper::sendResponse('no_content', false, null, 'Items not found');
            }
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error searching Items');
        }
    }

    public function search_items_orderapp(Request $request)
    {
        try {

            $category = $request->categoryId;
            $item_name = $request->item;
            if (!auth()->user()->can('view_menu')) {
                abort(403, 'Unauthorized action.');
            }

            $item = '';
            if($category == -1){
                $item = Item::where('is_favourite', true)->where('name', 'like', "%$item_name%")->get();
            }
            else if ($category != 0) {
                // dd($category);
                $item = Item::where('category_id', $category)->where('name', 'like', "%$item_name%")->get();
            } else {
                // dd($item_name);
                $item = Item::where('name', 'like', "%$item_name%")->get();
            }
            if ($item->count() >= 1) {
                return Helper::sendResponse('found', true, $item, 'Items found');
            } else {
                return Helper::sendResponse('no_content', false, null, 'Items not found');
            }
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error searching Items');
        }
    }
}
