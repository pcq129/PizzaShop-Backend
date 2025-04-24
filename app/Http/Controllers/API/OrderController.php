<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\Table;
use App\Models\KOT;
use App\Models\Item;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\waitingToken;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::with('customer')->get();
        if ($orders) {
            return response()->json([
                "code" => "200",
                "status" => "true",
                "data" => $orders,
                "message" => "Orders fetched successfully"
            ], 200);
        } else {
            return response()->json([
                "code" => "200",
                "status" => "false",
                "message" => "Error fetching orders"
            ], 200);
        }
    }

    public function show($id)
    {
        $orders = Order::with('customer')->where('id', 'like', "%$id%")->get();
        if ($orders->count() >= 1) {
            return response()->json([
                'code' => '200',
                'status' => 'true',
                'data' => $orders,
                'message' => 'Orders found'
            ],  200);
        } else {
            return response()->json([
                'code' => '404',
                'status' => 'false',
                'message' => 'Orders not found'
            ],  404);
        }
    }


    /**
     * Store a newly created resource in storage.
     */

    // placing new order
    public function store(Request $request)
    {
        // dd($request->order_data);

        $validator = Validator::make($request->all(), [
            'customer_id' => ['numeric', 'required'],
            'order_data' => ['json', 'required'],
            'amount' => ['numeric', 'required']
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


        $order_data = json_decode($request->order_data);
        $items = $order_data->items;
        $tables = $order_data->table_names;
        $section = $order_data->section_name;

        foreach ($items as $item) {
            $Item = Item::find($item->item_id);
            $Item->sell_count += 1;
            // dd($item);
            $Item->save();

            $newKOT = new KOT();
            $newKOT->order_id = $newOrder->id;
            $newKOT->item_data = json_encode([
                'items' => $item,
                'tables' => $tables,
                'section' => $section
            ]);
            $newKOT->item_category = $Item->category_id;
            $newKOT->save();
        }


        return response()->json([
            "code" => "200",
            "status" => "true",
            "data" => $newOrder->id,
            "message" => "Order placed successfully"
        ], 200);
    }

    /**
     * Mark specific order as completed.
     */
    public function complete_order($id)
    {


        // $validator = Validator::make([$id], ['numeric', 'required']);

        // if ($validator->fails()) {
        //     return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages(),], 200);
        // }


        $order = Order::find($id);
        $order->order_status = 'Completed';
        $order->payment_status = 'Completed';
        $order->payment_mode = "Cash";

        $table_ids = json_decode($order->order_data)->table_ids;
        // dd($table_ids);
        Table::whereIn('id', $table_ids)->update(['status' => 'Available']);

        $order->save();


        return response()->json([
            "code" => '200',
            "status" => "true",
            "message" => "Order marked as completed and tables freed.",
            200
        ]);
    }

    public function cancel_order(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'table_ids' => ['array', 'required'],
            'customer_id' => ['required', 'numeric']
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
            "code" => "200",
            "status" => "true",
            "message" => "Order cancelled"
        ], 200);
    }

    public function customerFeedback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderId' => ['numeric', 'required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $validator->messages()], 200);
        }

        $rating = json_decode($request->rating, true);

        $order = Order::find($request->orderId);
        if ($order) {
            $order->rating = $rating;
            $order->comment = $request->comment;
            $order->save();
            return response()->json([
                "code" => "200",
                "status" => "true",
                "message" => "Feedback added"
            ], 200);
        } else {
            return response()->json([
                "code" => "500",
                "status" => "false",
                "message" => "Internal Serve Error"
            ], 500);
        }
    }

    public function dashboard_data($filter)
    {
        switch ($filter) {
            case 0:
                $timeSpan = 1;
                break;

            case 1:
                $timeSpan = 7;
                break;

            case 2:
                $timeSpan = 30;
                break;

            case 3:
                $timeSpan = 3;
            default:
                $timeSpan = 30;
                break;
        }
        if ($filter != 3) {
            $startDate = Carbon::now()->subDays($timeSpan)->toDateTimeString();
        } else {
            $startDate = Carbon::now()->startOfMonth()->toDateTimeString();
        }
        // dd($timeSpan);

        // try {
        $total_sales = Order::where(
            'created_at',
            '>=',
            $startDate
        )
            ->sum('bill_amount');
        // dd($total_sales);
        $order_count = Order::where(
            'created_at',
            '>=',
            $startDate
        )->count();
        $waitinglist_count = waitingToken::where(
            'created_at',
            '>=',
            $startDate
        )->count();


        // average waiting time calculation
        $tokens = DB::table('waiting_tokens')->where(
            'created_at',
            '>=',
            $startDate
        )
            ->whereNotNull('deleted_at') // only consider served tokens
            ->select('created_at', 'deleted_at')
            ->get();
        // dd($tokens);

        $total_minutes = 0;
        $count = $tokens->count();

        foreach ($tokens as $token) {
            $start = Carbon::parse($token->created_at);
            $end = Carbon::parse($token->deleted_at);
            $total_minutes += $start->diffInMinutes($end);
        }

        $average_waiting_minutes = $count > 0 ? round($total_minutes / $count) : 0;


        // new customer count

        // $new_customer_count = Customer::where(
        //     'created_at',
        //     '>=',
        //     Carbon::now()->subDays($timeSpan)->toDateTimeString()
        // )->count();

        $new_customer_count = Customer::where(
            'created_at',
            '>=',
            $startDate
        )->has(
            'Orders',
            '<=',
            1
        )->count();



        //top selling and least selling items;
        $top_selling = Item::where(
            'updated_at',
            '>=',
            $startDate
        )->orderBy('sell_count', 'desc')->orderBy('created_at', 'asc')->take(2)->select('name', 'sell_count')->get();
        $least_selling = Item::where(
            'updated_at',
            '>=',
            $startDate
        )->orderBy('sell_count', 'asc')->orderBy('created_at', 'desc')->take(2)->select('name', 'sell_count')->get();




        //graphs

        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->endOfMonth()->toDateString();

        $dailyRevenue = DB::table('orders')
            ->selectRaw('DATE(created_at) as date, SUM(bill_amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $daysInMonth = Carbon::now()->daysInMonth;
        $chartLabels = [];
        $chartData = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::now()->startOfMonth()->addDays($day - 1)->toDateString();
            $chartLabels[] = (string) $day;
            $chartData[] = isset($dailyRevenue[$date]) ? $dailyRevenue[$date] : 0;
        }



        return response()->json([
            'code' => '200',
            'status' => 'true',
            'data' => [
                'total_sales' => $total_sales,
                'order_count' => $order_count,
                'waitinglist_count' => $waitinglist_count,
                'average_waiting_minutes' => $average_waiting_minutes,
                'new_customer_count' => $new_customer_count,
                'chart1' => [
                    'labels' => $chartLabels,
                    'data' => $chartData
                ],
                'top_selling' => $top_selling,
                'least_selling' => $least_selling
            ],
            'message' => 'Dashboard data fetched successfully'
        ], 200);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'code' => '500',
        //         'status' => 'false',
        //         'data' => [
        //             $th
        //         ],
        //         'message' => 'Internal Server Error'
        //     ]);
        // }
    }

    public function exportToExcel($filter)
    {

        $filters = ['id'=>$filter];
        $order_data = "";
        if($filter == 0){
            $order_data = Order::join('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.id', 'orders.created_at', 'customers.name as customer_name', 'orders.payment_status', 'orders.payment_mode', 'orders.rating', 'orders.bill_amount')
            ->get();
        }
        else{
            $order_data = Order::where('orders.id','like',"%$filter%")->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->select('orders.id', 'orders.created_at', 'customers.name as customer_name', 'orders.payment_status', 'orders.payment_mode', 'orders.rating', 'orders.bill_amount')
            ->get();

        }
        $headings =
            ['ID', 'Order Date', 'Name', 'Payment', 'Mode', 'Rating', 'Amount']
        ;
        return Excel::download(new OrdersExport($order_data, $headings), 'orders.xlsx');


        // return response()->json([
        //     'code'=>200,
        //     'status'=>'true',
        //     'data'=>Excel::download(new OrdersExport($order_data, $headings), 'users.xlsx'),
        //     'message'=>"Orders exported successfully"
        // ]);

    }
}
