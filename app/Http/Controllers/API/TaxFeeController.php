<?php

namespace App\Http\Controllers\API;

use App\Models\TaxFee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class TaxFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if (!auth()->user()->can('view_tax')) {
                abort(403, 'Unauthorized action.');
            }
            $taxes = TaxFee::all();

            if ($taxes->count() != 0) {
                return Helper::sendResponse('ok', true, $taxes, 'Taxes fetched successfully');
            } else {
                return Helper::sendResponse('no_content', true, null, 'No Tax records found');
            }
        } catch (\Throwable $e) {
            return Helper::sendResponse('error', false, $e->getMessage(), 'An error occured while fetching Taxes');
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            if (!auth()->user()->can('add_edit_tax')) {
                abort(403, 'Unauthorized action.');
            }



            $validator = Validator::make($request->all(), [
                'name' => [Rule::Unique('tax_fees', 'name')->ignore($request->id)->withoutTrashed(), 'required'],
                'type' => ['required', Rule::in(['percentage', 'flat_amount'])],
                'amount' => [Rule::excludeIf(request('type') === 'flat_amount'), 'required', 'numeric', 'min:1', 'max:50'],

                // 'amount'=> ['exclude_if:type,flat_amount','required','min:1','max:50'],
                // 'amount'=> ['exclude_if:type,percentage','required','min:0'],
                'enabled' => ['required', 'boolean'],
                'default' => ['required', 'boolean'],
            ], $message = [
                'amount.max' => 'Tax amount cannot exceed 50%',
                'name.unique' => 'Tax already exists',
                'enabled.required' => 'Tax state not found',
                'default.required' => 'Tax status not found'
            ]);

            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());
            }

            $tax = new TaxFee();
            $tax->name = $request->name;
            $tax->type = $request->type;
            $tax->amount = $request->amount;
            $tax->enabled = $request->enabled;
            $tax->default = $request->default;
            $tax->save();


            return Helper::sendResponse('created', true, null, 'Tax added successfully');


            // return response()->json([
            //     'code' => '200',
            //     'status' => 'true',
            //     'message' => "Tax added successfully"
            // ], 200);
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'An error occured while storing Tax');
        }
    }

    // let data = {
    //     'state': state,
    //     'toggle': toggle
    //   }
    public function toggle($id, Request $request)
    {
        try {
            if (!auth()->user()->can('add_edit_tax')) {
                abort(403, 'Unauthorized action.');
            }
            $field = $request->toggle;
            $tax = TaxFee::find($id);
            $tax->$field = $request->state;
            $tax->save();
            return Helper::sendResponse('ok', true, null, 'Successfully toggled ' . $field . '.');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error toggling tax');
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            if (!auth()->user()->can('add_edit_tax')) {
                abort(403, 'Unauthorized action.');
            }



            $validator = Validator::make($request->all(), [

                'name' => [Rule::Unique('tax_fees', 'name')->ignore($request->id)->withoutTrashed(), 'required'],
                'type' => ['required', Rule::in(['percentage', 'flat_amount'])],
                'amount' => [Rule::excludeIf(request('type') === 'flat_amount'), 'required', 'numeric', 'min:1', 'max:50'],
                'enabled' => ['required', 'boolean'],
            ], $message = [
                'amount' => 'Tax amount cannot exceed 50%'
            ]);

            if ($validator->fails()) {
                return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            }

            $tax = TaxFee::find($id);

            if (!$tax) {
                return Helper::sendResponse('not_found', false, null, 'Tax not found');
            }
            $tax->name = $request->name;
            $tax->type = $request->type;
            $tax->amount = $request->amount;
            $tax->enabled = $request->enabled;
            $tax->default = $request->default;
            $tax->save();

            return Helper::sendResponse('ok', true, null, 'Tax updated successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'An error occured while updating Tax');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            if (!auth()->user()->can('delete_tax')) {
                abort(403, 'Unauthorized action.');
            }
            $tax = TaxFee::findOrFail($id);
            if ($tax) {
                $tax->delete();
                return response()->json([
                    'code' => '200',
                    'status' => 'true',
                    'message' => 'Tax deleted successfully'
                ], 200);
                return Helper::sendResponse('ok', true, null, 'Tax deleted successfully');
            }
            return Helper::sendResponse('not_found', false, null, 'Tax not found');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'An error occured while deleting Tax');
        }
    }

    public function search_tax($search)
    {
        try {
            if (!auth()->user()->can('view_tax')) {
                abort(403, 'Unauthorized action.');
            }
            $tax = TaxFee::where('name', 'like', "%$search%")->get();
            if ($tax->count() >= 1) {
                return Helper::sendResponse('found', false, $tax, 'Tax found');
            } else {
                return Helper::sendResponse('no_content', false, null, 'Tax not found');
            }
        } catch (\Throwable $th) {
            //throw $th;
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error while searching Tax');

        }
    }
}
