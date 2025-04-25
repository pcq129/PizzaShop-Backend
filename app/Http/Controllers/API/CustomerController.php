<?php

namespace App\Http\Controllers\API;

use App\Models\Customer;
use App\Models\Table;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\waitingToken;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('view_customer')) {
            abort(403, 'Unauthorized action.');
        }
        $customers = Customer::with('orders')->get();
        return response()->json([
            "code" => "200",
            "status" => "true",
            "data" => $customers,
            "message" => "Customers list fetched successfully"
        ]);
    }

    public function waiting_token()
    {

        if (!auth()->user()->can('view_customer')) {
            abort(403, 'Unauthorized action.');
        }

        $tokens = Customer::with('section')->where('status', 1)->get();
        dd($tokens);
        return response()->json([
            "code" => "200",
            "status" => "true",
            "data" => $tokens,
            "message" => "Waiting list fetched successfully"
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function search_customer(Request $request)
    {

        if (!auth()->user()->can('view_customer')) {
            abort(403, 'Unauthorized action.');
        }

        $search = $request->email;
        $section_id = $request->sectionId;
        // dd($request);
        $customer = Customer::where('email', 'like', "%$search%")->where('status', '=', 1)->where('section_id', '=', $section_id)->get();
        // dd($customer);
        if ($customer) {
            return response()->json([
                "status" => "true",
                "code" => "200",
                "data" => $customer,
                "message" => "found existing customer",

            ]);
        } else {
            return response()->json([
                "status" => "false",
                "code" => "404",
                "message" => "customer not found",

            ]);
        }
    }


    public function assign_table(Request $request)
    {
        if (!auth()->user()->can('add_edit_customer')) {
            abort(403, 'Unauthorized action.');
        }
        $validator = Validator::make($request->all(), [
            'table_ids' => ['array', 'required'],
            'email' => ['email', 'required'],
            'mobile' => ['required'],
            'name' => ['required', 'string', 'max:140'],
            'section_id' => ['required', 'numeric']
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
        }
        // {
        //     "email": "test@test.com",
        //     "name": "Harmit",
        //     "mobile": "9485495837",
        //     "headCount": "75",
        //     "section": "Ground Floor",
        //     "section_id": 1,
        // "table_ids": [
        //     2
        // ]
        // }

        // dd($request);
        $customer_id = null;
        $customer = Customer::where('email', $request->email)->first();
        if ($customer) {
            $customer_id = $customer->id;
            $customer->name = $request->name;

            if (!$customer->section_id) {
                $customer->section_id = $request->section_id;
            }
            $customer->status = 2;
            $customer->waitingTokens()->delete();
            $customer->save();
        }
        $allAvailable = 1;
        if (!$customer) {
            $customer = new Customer();
            $customer->email = $request->email;
            $customer->name = $request->name;
            $customer->mobile = $request->mobile;
            $customer->section_id = $request->section_id;
            $customer->status = 2;
            $customer->waitingTokens()->delete();
            $customer->save();
            $customer_id = $customer->id;
        }
        foreach ($request->table_ids as $table_id) {
            $table = Table::find($table_id);

            if ($table->status == "Available") {
                $table->status = "Assigned";
                $table->assigned_to = $customer->id;
                $table->save();
            } else {
                $GLOBALS[$allAvailable] = 0;
            }
            $table->save();
        }

        if ($allAvailable) {
            return response()->json([
                "code" => "200",
                "status" => "true",
                "message" => "Tables assigned successfully",
                "data" => $customer_id
            ], 200);
        } else {
            return response()->json([
                "code" => "200",
                "status" => "false",
                "message" => "Only available tables are assigned",
            ], 200);
        }
    }



    //function for adding customer to waiting lists

    public function create_waiting_token(Request $request)
    {if (!auth()->user()->can('add_edit_customer')) {
        abort(403, 'Unauthorized action.');
    }
        $customer_id = null;
        $customer = Customer::where('email', $request->email)->first();
        if ($customer) {
            $customer_id = $customer->id;
            $customer->name = $request->name;

            if ($customer->status != 1) {
                $customer->status = 1;
                $customer->section_id = $request->sectionId;
                $customer->head_count = $request->headCount;
                $customer->save();

                $waitingToken = new waitingToken();
                $waitingToken->head_count = $request->headCount;
                $waitingToken->section_id = $request->sectionId;
                $waitingToken->customer_id = $customer_id;
                $waitingToken->save();
            } else {
                return response()->json([
                    "code" => "200",
                    "status" => "true",
                    "message" => "Customer is already in waiting",
                    "data" => $customer_id
                ], 200);
            }
        }
        if (!$customer) {
            $customer = new Customer();
            $customer->email = $request->email;
            $customer->name = $request->name;
            $customer->mobile = $request->mobile;
            // null = no status, 1 = waiting, 2 = ordered


            $customer->status = 1;
            $customer->head_count = $request->headCount;
            $customer->section_id = $request->sectionId;
            $customer->save();
            $customer_id = $customer->id;

            $waitingToken = new waitingToken();
            $waitingToken->head_count = $request->headCount;
            $waitingToken->section_id = $request->sectionId;
            $waitingToken->customer_id = $customer_id;
            $waitingToken->save();




            return response()->json([
                "code" => "200",
                "status" => "true",
                "message" => "Waiting token generated",
                "data" => $customer_id
            ], 200);
        }
    }


    public function update_waiting_token(Request $request)
    {
        if (!auth()->user()->can('add_edit_customer')) {
            abort(403, 'Unauthorized action.');
        }
        $id = $request->id;
        $customer = Customer::find($id);
        // dd($customer);

        if ($request->delete) {
            // dd('idelete');
            $customer->status = null;
            $customer->section_id = 0;
            $customer->save();
            // dd($customer);
            return response()->json([
                "code" => "200",
                "status" => "true",
                "message" => "Waiting token deleted",
            ], 200);
        }


        if ($customer && !$request->delete) {
            $customer_id = $customer->id;
            $customer->email = $request->email;
            $customer->name = $request->name;
            $customer->mobile = $request->mobile;
            $customer->status = 1;
            $customer->head_count = $request->headCount;
            $customer->section_id = $request->sectionId;


            $customer->save();


            return response()->json([
                "code" => "200",
                "status" => "true",
                "message" => "Waiting token updated",
                "data" => $customer_id
            ], 200);
        }
    }

    public function search_customer_by_name($search){
        if (!auth()->user()->can('view_customer')) {
            abort(403, 'Unauthorized action.');
        }
        $customers = Customer::where('name', 'like', "%$search%")->get();
        if($customers->count()>=1){
            return response()->json([
                'code' => '200',
                'status' => 'true',
                'data'=> $customers,
                'message' => 'Customers found'
            ],  200);
        }else{
            return response()->json([
                'code' => '404',
                'status' => 'false',
                'message' => 'Customers not found'
            ],  404);
        }
    }
}
