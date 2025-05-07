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
use App\Helpers\Helper;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
          if (!auth()->user()->can('view_order')) {
              abort(403, 'Unauthorized action.');
          }
  
          $per_page = $request->perPage;
          $orders = Order::with('customer')->orderBy('created_at', 'desc')->paginate($per_page);
          if ($orders) {
  
              return Helper::sendResponse('ok', true, $orders, 'Orders fetched successfully');
          } else {
  
              return Helper::sendResponse('no_content', true, null, 'No Orders available');
          }
    
      } catch (\Throwable $th) {
        //throw $th;
        return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Orders');
      }
    }

    public function show($id)
    {
        try {
            if (!auth()->user()->can('view_order')) {
                abort(403, 'Unauthorized action.');
            }
            $orders = Order::with('customer')->where('id', 'like', "%$id%")->get();
            if ($orders->count() >= 1) {
    
                return Helper::sendResponse('ok', true, $orders, 'Orders found');
            } else {
    
                return Helper::sendResponse('no_content', true, null, 'Order not found');
            }
    
        } catch (\Throwable $th) {
          //throw $th;
          return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Order');
        }
    }


    /**
     * Store a newly created resource in storage.
     */

    // placing new order
    public function store(Request $request)
    {
        try {
            if (!auth()->user()->can('add_edit_order')) {
                abort(403, 'Unauthorized action.');
            }
            // dd($request->order_data);
    
            $validator = Validator::make($request->all(), [
                'customer_id' => ['numeric', 'required'],
                'order_data' => ['json', 'required'],
                'amount' => ['numeric', 'required']
            ]);
    
            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());
                // return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
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
    
    
            return Helper::sendResponse('ok', true, $newOrder->id, 'Order placed successfully');
    
        } catch (\Throwable $th) {
          //throw $th;
          return Helper::sendResponse('error', false, $th->getMessage(), 'Error placing Orders');
        }
    }

    /**
     * Mark specific order as completed.
     */
    public function complete_order($id)
    {
        
        try {
            if (!auth()->user()->can('add_edit_order')) {
                abort(403, 'Unauthorized action.');
            }
    
            // $validator = Validator::make([$id], ['numeric', 'required']);
    
            // if ($validator->fails()) {
            //     return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
            // }
    
    
            $order = Order::find($id);
            $order->order_status = 'Completed';
            $order->payment_status = 'Completed';
            $order->payment_mode = "Cash";
    
            $kots = KOT::where('order_id', $order->id)->get();
    
            // Delete each KOT
            foreach ($kots as $singleKot) {
                $singleKot->delete();
            }
    
            // $kot = KOT::where('id', $id);
            // dd($kot);
            // foreach ($kot as $singleKot) {
            //     dd($singleKot);
            //     $singleKot->delete();
            // }
    
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
            return Helper::sendResponse();
    
        } catch (\Throwable $th) {
          //throw $th;
          return Helper::sendResponse('error', false, $th->getMessage(), 'Error while completing Order');
        }
    }

    public function cancel_order(Request $request)
    {
        
        try {
            if (!auth()->user()->can('add_edit_order')) {
                abort(403, 'Unauthorized action.');
            }
            $validator = Validator::make($request->all(), [
                'table_ids' => ['array', 'required'],
                'customer_id' => ['required', 'numeric'],
                'order_id' => ['required', 'numeric']
            ]);
    
            if ($validator->fails()) {
            return Helper::sendResponse('bad_request', false,null ,$validator->messages()->first() );
    
            }
    
    
            $kot = KOT::find('order_id', $request->order_id);
    
            if ($kot->status == 0) {
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
            return Helper::sendResponse('ok', true, null, 'Order cancelled');
    
            } else {
                // return response()->json([
                //     "code" => "200",
                //     "status" => "false",
                //     "message" => "Items are already prepared"
                // ], 200);
            return Helper::sendResponse('bad_request', false, null, 'Items are already prepared');
    
            }
    
        } catch (\Throwable $th) {
          //throw $th;
          return Helper::sendResponse('error', false, $th->getMessage(), 'Error cancelling Order');
        }
    }

    public function customerFeedback(Request $request)
    {
        
        try {
            if (!auth()->user()->can('add_edit_order')) {
                abort(403, 'Unauthorized action.');
            }
            $validator = Validator::make($request->all(), [
                'orderId' => ['numeric', 'required'],
            ]);
    
            if ($validator->fails()) {
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());
            }
    
            $rating = json_decode($request->rating, true);
    
            $order = Order::find($request->orderId);
            if ($order) {
                $order->rating = $rating;
                $order->comment = $request->comment;
                $order->save();
              
            return Helper::sendResponse('ok', true, null, 'Feedback added');
    
            } else {
                return response()->json([
                    "code" => "500",
                    "status" => "false",
                    "message" => "Internal Serve Error"
                ], 500);
            return Helper::sendResponse('404', false, null, 'Order not found');
    
            }
    
        } catch (\Throwable $th) {
          //throw $th;
          return Helper::sendResponse('error', false, $th->getMessage(), 'Error adding Feedback');
        }
    }

    public function dashboard_data($filter)
    {
        
        try {
            if (!auth()->user()->can('view_order')) {
                abort(403, 'Unauthorized action.');
            }
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
    
            // // $startDate = Carbon::now()->startOfMonth()->toDateString();
            // $endDate = Carbon::now();
            // // ->endOfMonth()->toDateString()
    
            // $daily_revenue = DB::table('orders')
            //     ->selectRaw('DATE(created_at) as date, SUM(bill_amount) as total')
            //     ->whereBetween('created_at', [$startDate, $endDate])
            //     ->groupBy('date')
            //     ->orderBy('date')
            //     ->pluck('total', 'date');
    
            // $daysInMonth = Carbon::now()->daysInMonth;
            // $revenue_labels = [];
            // $revenue_data = [];
    
    
            // for ($day = 1; $day <= $daysInMonth; $day++) {
            //     $date = Carbon::now()->startOfMonth()->addDays($day - 1)->toDateString();
            //     $revenue_labels[] = (string) $day;
            //     $revenue_data[] = isset($daily_revenue[$date]) ? $daily_revenue[$date] : 0;
            // }
    
    
            // $startDate = Carbon::now()->startOfMonth()->toDateString();
            // ->endOfMonth()->toDateString()
    
    
            // $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now();
            // ->endOfMonth()->toDateString()
    
            $daily_revenue = DB::table('orders')
                ->selectRaw('DATE(created_at) as date, SUM(bill_amount) as total')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date');
            // dd($daily_revenue);
    
            $filterStartDate = Carbon::now()->subDays($timeSpan);
            $revenue_labels = [];
            $revenue_data = [];
    
    
            if ($filter != 3) {
                $filterStartDate = Carbon::now()->subDays($timeSpan);
                $filterSpan = $timeSpan;
            } else {
                $filterStartDate = Carbon::now()->startOfMonth()->subDays(1);
                $filterSpan = $filterStartDate->diffInDays($endDate);
            }
    
    
    
            // $endDateDay = $endDate->day;
            // $filterStartDateDay = $filterStartDate->day;
            // $filterStartDate->diffInDays($endDate)
    
    
            for ($day = 0; $day <= $filterSpan; $day++) {
    
                $date = $filterStartDate->addDays(1);
                $dateString = $date->toDateString();
                $revenue_labels[] = $date->day;
                $revenue_data[] = isset($daily_revenue[$dateString]) ? $daily_revenue[$dateString] : 0;
            }
    
    
    
    
    
            $filterStartDate = Carbon::now()->subDays($timeSpan);
            if ($filter != 3) {
                $filterStartDate = Carbon::now()->subDays($timeSpan);
                $filterSpan = $timeSpan;
            } else {
                $filterStartDate = Carbon::now()->startOfMonth()->subDays(1);
                $filterSpan = $filterStartDate->diffInDays($endDate);
            }
    
            $customer_growth = DB::table('customers')
                ->selectRaw('DATE(created_at) as date, count(*) as number')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('number', 'date');
    
            $customer_growth_labels = [];
            $custome_growth_data = [];
    
            for ($day = 0; $day <= $filterSpan; $day++) {
                $date = $filterStartDate->addDays(1);
                $dateString = $date->toDateString();
                $customer_growth_labels[] = $date->day;
                $custome_growth_data[] = isset($customer_growth[$dateString]) ? $customer_growth[$dateString] : 0;
            }
    
            $data = [
                'total_sales' => $total_sales,
                'order_count' => $order_count,
                'waitinglist_count' => $waitinglist_count,
                'average_waiting_minutes' => $average_waiting_minutes,
                'new_customer_count' => $new_customer_count,
                'revenue' => [
                    'labels' => $revenue_labels,
                    'data' => $revenue_data
                ],
                'customer_growth' => [
                    'labels' => $customer_growth_labels,
                    'data' => $custome_growth_data
                ],
                'top_selling' => $top_selling,
                'least_selling' => $least_selling
            ];
    
            return response()->json([
                'code' => '200',
                'status' => 'true',
                'data' => [
                    'total_sales' => $total_sales,
                    'order_count' => $order_count,
                    'waitinglist_count' => $waitinglist_count,
                    'average_waiting_minutes' => $average_waiting_minutes,
                    'new_customer_count' => $new_customer_count,
                    'revenue' => [
                        'labels' => $revenue_labels,
                        'data' => $revenue_data
                    ],
                    'customer_growth' => [
                        'labels' => $customer_growth_labels,
                        'data' => $custome_growth_data
                    ],
                    'top_selling' => $top_selling,
                    'least_selling' => $least_selling
                ],
                'message' => 'Dashboard data fetched successfully'
            ], 200);
            return Helper::sendResponse('ok', true, $data, 'Dashboard data fetched successfully');
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
    
        } catch (\Throwable $th) {
          //throw $th;
          return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetchin Dashboard data');
        }
    }

    public function exportToExcel($filter)
    {
        
        try {
            if (!auth()->user()->can('view_order')) {
                abort(403, 'Unauthorized action.');
            }
    
            $filters = ['id' => $filter];
            $order_data = "";
            if ($filter == 0) {
    
                $order_data = Order::join('customers', 'orders.customer_id', '=', 'customers.id')
                    ->select(
                        'orders.id',
                        'orders.created_at',
                        'customers.name as customer_name',
                        'orders.payment_status',
                        'orders.payment_mode',
                        DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.rating, '$.food')) as food_rating"),
                        'orders.bill_amount'
                    )->get();
            } else {
                $order_data = Order::where('orders.id', 'like', "%$filter%")->join('customers', 'orders.customer_id', '=', 'customers.id')
                    ->select(
                        'orders.id',
                        'orders.created_at',
                        'customers.name as customer_name',
                        'orders.payment_status',
                        'orders.payment_mode',
                        DB::raw("JSON_UNQUOTE(JSON_EXTRACT(orders.rating, '$.food')) as food_rating"),
                        'orders.bill_amount'
                    )->get();
            }
            $headings =
                ['ID', 'Order Date', 'Name', 'Payment', 'Mode', 'Rating', 'Amount'];
            return Excel::download(new OrdersExport($order_data, $headings), 'orders.xlsx');
    
    
        } catch (\Throwable $th) {
          //throw $th;
          return Helper::sendResponse('error', false, $th->getMessage(), 'Error exporting Orders');
        }

        // return response()->json([
        //     'code'=>200,
        //     'status'=>'true',
        //     'data'=>Excel::download(new OrdersExport($order_data, $headings), 'users.xlsx'),
        //     'message'=>"Orders exported successfully"
        // ]);

    }
}
