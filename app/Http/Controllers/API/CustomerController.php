<?php

namespace App\Http\Controllers\API;

use App\Models\Customer;
use App\Models\Table;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::with('orders')->get();
        return response()->json([
            "code"=>"200",
            "status"=>"true",
            "data"=>$customers,
            "message"=>"Customers list fetched successfully"
        ]);
    }

    public function waiting_token(){
        $tokens = Customer::with('section')->where('status','waiting')->get();
        return response()->json([
            "code"=>"200",
            "status"=>"true",
            "data"=>$tokens,
            "message"=>"Waiting list fetched successfully"
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function searchCustomer(Request $request)
    {

        $search = $request->email;
        $section_id = $request->sectionId;
        // dd($request);
        $customer = Customer::where('email', 'like', "%$search%")->where('status','=', 'waiting')->where('section_id','=',$section_id)->get();
        // dd($customer);
        if($customer){
            return response()->json([
                "status"=>"true",
                "code"=>"200",
                "data"=> $customer,
                "message"=>"found existing customer",

            ]);
        }else{
            return response()->json([
                "status"=>"false",
                "code"=>"404",
                "message"=>"customer not found",

            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        //
    }

    public function assign_table(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'table_ids'=>['array', 'required'],
            'email'=> ['email','required'],
            'mobile'=> ['required'],
            'name'=> ['required','string','max:140']
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
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
            $customer->status = "dining";
            $customer->save();
        }
        $allAvailable = 1;
        if (!$customer) {
            $customer = new Customer();
            $customer->email = $request->email;
            $customer->name = $request->name;
            $customer->mobile = $request->mobile;
            $customer->status = "dining";
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
    {
        $customer_id = null;
        $customer = Customer::where('email', $request->email)->first();
        if ($customer) {
            $customer_id = $customer->id;
            $customer->name = $request->name;

            if($customer->status != "waiting"){
                $customer->status = "waiting";
                $customer->section_id = $request->sectionId;
                $customer->head_count = $request->headCount;
                $customer->save();
            }else{
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
            $customer->status = "waiting";
            $customer->head_count = $request->headCount;
            $customer->section_id = $request->sectionId;
            $customer->save();
            $customer_id = $customer->id;

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
        $id = $request->id;
        $customer = Customer::find($id);
        if ($customer && !$request->delete) {
            $customer_id = $customer->id;
            $customer->email = $request->email;
            $customer->name = $request->name;
            $customer->mobile = $request->mobile;
            $customer->status = "waiting";
            $customer->head_count = $request->headCount;
            $customer->section_id = $request->sectionId;
            if($request->delete){
                $customer->status = null;
            }
            $customer->save();


            return response()->json([
                "code" => "200",
                "status" => "true",
                "message" => "Waiting token updated",
                "data" => $customer_id
            ], 200);
        }


    }
}
