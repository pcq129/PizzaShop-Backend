<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\Table;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\Paginator;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with('customer')->paginate(10);
        if ($orders) {
            return response()->json([
                "code" => "200",
                "status" => "true",
                "data" => $orders,
                "message" => "Orders fetched successfully"
            ]);
        } else {
            return response()->json([
                "code" => "200",
                "status" => "false",
                "message" => "Error fetching orders"
            ]);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->order_data);

        $validator = Validator::make($request->all(), [
            'customer_id' => ['numeric', 'required'],
            'order_data'=>['json', 'required'],
            'amount'=> ['numeric', 'required']
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }


        // {"tables":[13],"items":[{"item_id":6,"item_name":"Cheeze Tweeze","item_rate":352.82,"modifiers":[{"modifier_id":4,"modifier_name":"BBQ","modifier_rate":50},{"modifier_id":5,"modifier_name":"Alfredo","modifier_rate":200}]}],"taxes":{"GST":108.36,"SGST":108.36,"CGST":0,"Service charges":230},"subTotal":602,"total":1048.72}

        $newOrder = new Order();
        $newOrder->customer_id = $request->customer_id;
        $newOrder->order_status = "Ordered";
        $newOrder->payment_mode = 'Card'; //for now, not getting data from FE
        $newOrder->rating = '4';
        $newOrder->order_data = $request->order_data;
        $newOrder->bill_amount = $request->amount;
        $newOrder->save();



        $table_ids = json_decode($request->order_data)->table_ids;
        Table::whereIn('id', $table_ids)->update(['status' => 'Running']);


        return response()->json([
            "code" => "200",
            "status" => "true",
            "data"=> $newOrder->id,
            "message" => "Order placed successfully"
        ]);
    }

    /**
     * Mark specific order as completed.
     */
    public function complete_order($id) {


        $validator = Validator::make($id,['numeric', 'required']);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }


        $order = Order::find($id);
        $order->order_status = "Completed";
        $order->payment_status = "completed";
        $order->payment_mode = "Cash";

        $table_ids = json_decode($order->order_data)->table_ids;
        // dd($table_ids);
        Table::whereIn('id', $table_ids)->update(['status' => 'Available']);

        $order->save();


        return response()->json([
            "code"=>'200',
            "status"=>"true",
            "message"=>"Order marked as completed and tables freed."
        ]);

    }

    public function cancel_order(Request $request){

        $validator = Validator::make($request->all(), [
            'table_ids' => ['array', 'required'],
            'customer_id'=> ['required', 'numeric']
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        }



        $table_ids = $request->table_ids;
        // dd($table_ids);
        Table::whereIn('id', $table_ids)->update(['status' => 'Available']);

        $customer = Customer::find($request->customer_id);
        $customer->status = null;
        $customer->save();

        // Table::whereIn('id', $request)->update(['status' => 'Available']);
        // $customer = Customer::find($request->customer_id);
        // $customer->delete();

        return response()->json([
            "code"=>"200",
            "status"=>"true",
            "message"=>"Order cancelled"
        ]);

    }
}
