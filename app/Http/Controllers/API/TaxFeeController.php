<?php

namespace App\Http\Controllers\API;

use App\Models\TaxFee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaxFeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $taxes = TaxFee::all();
        return response()->json([
            'code' => '200',
            'status' => 'true',
            'data' => $taxes,
            'message' => 'Taxes fetched successfully'
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'name' => [Rule::Unique('tax_fees', 'name')->ignore($request->id)->withoutTrashed(), 'required'],
            'type' => ['required', Rule::in(['percentage', 'flat_amount'])],
            'amount' => [Rule::excludeIf(request('type') === 'flat_amount'), 'required', 'numeric', 'min:1', 'max:50'],

            // 'amount'=> ['exclude_if:type,flat_amount','required','min:1','max:50'],
            // 'amount'=> ['exclude_if:type,percentage','required','min:0'],
            'enabled' => ['required', 'boolean'],
            'default' => ['required', 'boolean'],
        ], $message = [
            'amount' => 'Tax amount cannot exceed 50%'
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }

        $tax = new TaxFee();
        $tax->name = $request->name;
        $tax->type = $request->type;
        $tax->amount = $request->amount;
        $tax->enabled = $request->enabled;
        $tax->default = $request->default;
        $tax->save();

        return response()->json([
            'code' => '200',
            'status' => 'true',
            'message' => "Tax added successfully"
        ], 200);
    }

    // let data = {
    //     'state': state,
    //     'toggle': toggle
    //   }
    public function toggle($id, Request $request)
    {
        $field = $request->toggle;
        $tax = TaxFee::find($id);
        $tax->$field = $request->state;
        $tax->save();
        return response()->json([
            'code' => '200',
            'status' => 'true',
            'message' => 'Successfully toggled ' . $field . '.'
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {



        $validator = Validator::make($request->all(), [

            'name' => [Rule::Unique('tax_fees', 'name')->ignore($request->id)->withoutTrashed(), 'required'],
            'type' => ['required', Rule::in(['percentage', 'flat_amount'])],
            'amount' => [Rule::excludeIf(request('type') === 'flat_amount'), 'required', 'numeric', 'min:1', 'max:50'],
            'enabled' => ['required', 'boolean'],
        ], $message = [
            'amount' => 'Tax amount cannot exceed 50%'
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }

        $tax = TaxFee::findOrFail($id);
        $tax->name = $request->name;
        $tax->type = $request->type;
        $tax->amount = $request->amount;
        $tax->enabled = $request->enabled;
        $tax->default = $request->default;
        $tax->save();

        return response()->json([
            'code' => '200',
            'status' => 'true',
            'message' => "Tax updated successfully"
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tax = TaxFee::findOrFail($id);
        if ($tax) {
            $tax->delete();
            return response()->json([
                'code' => '200',
                'status' => 'true',
                'message' => 'Tax deleted successfully'
            ], 200);
        }
        return response()->json([
            'code' => '404',
            'status' => 'false',
            'message' => 'Tax not found',
        ], 200);
    }

    public function search_tax($search)
    {
        $tax = TaxFee::where('name', 'like', "%$search%")->get();
        if ($tax->count() >= 1) {
            return response()->json([
                'code' => '200',
                'status' => 'true',
                'data' => $tax,
                'message' => 'Taxes found'
            ],  200);
        } else {
            return response()->json([
                'code' => '404',
                'status' => 'false',
                'message' => 'Taxes not found'
            ],  404);
        }
    }
}
