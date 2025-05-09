<?php

namespace App\Http\Controllers\API;

use App\Models\Customer;
use App\Models\Table;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\waitingToken;
use Illuminate\Support\Facades\Validator;
use App\Helpers\Helper;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerExports;



class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            if (!auth()->user()->can('view_customer')) {
                abort(403, 'Unauthorized action.');
            }
            $per_page = $request->perPage;
            $customers = Customer::with('orders')->orderBy('created_at', 'desc')->paginate($per_page);

            return Helper::sendResponse('ok', true, $customers, 'Customers fetched successfully');
        } catch (\Throwable $th) {
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Customers');
        }
    }

    public function waiting_token()
    {

        try {
            if (!auth()->user()->can('view_customer')) {
                abort(403, 'Unauthorized action.');
            }

            $tokens = Customer::with('section')->where('status', 1)->get();
            dd($tokens);
            return Helper::sendResponse('ok', true, $tokens, 'Waiting List fetched successfully');
        } catch (\Throwable $th) {
            //throw $th;
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error fetching Waiting List');
        }
    }


    /**
     * Display the specified resource.
     */
    public function search_customer(Request $request)
    {


        try {

            if (!auth()->user()->can('view_customer')) {
                abort(403, 'Unauthorized action.');
            }

            $search = $request->email;
            $section_id = $request->sectionId;
            $customer = Customer::where('email', 'like', "%$search%")->where('status', '=', 1)->where('section_id', '=', $section_id)->get();
            if ($customer) {

                return Helper::sendResponse('found', true, $customer, 'Customers found');
            } else {

                return Helper::sendResponse('no_content', true, null, 'No data found');
            }
        } catch (\Throwable $th) {
            //throw $th;
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error searching Customers');
        }
    }


    public function assign_table(Request $request)
    {
        try {
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
                return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());
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


                return Helper::sendResponse('ok', true, $customer_id, 'Tables assigned successfully');
            } else {

                return Helper::sendResponse('ok', false, null, 'Only available tables are assigned');
            }
        } catch (\Throwable $th) {
            //throw $th;
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error assigning Tables');
        }
    }



    //function for adding customer to waiting lists

    public function create_waiting_token(Request $request)
    {
        try {
            if (!auth()->user()->can('add_edit_customer')) {
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

                    return Helper::sendResponse('ok', false, $customer_id, "Customer is already in waiting");
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





                return Helper::sendResponse('created', true, $customer_id, 'Waiting Token generated');
            }
        } catch (\Throwable $th) {
            //throw $th;
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error generating Waiting Token');
        }
    }


    public function update_waiting_token(Request $request)
    {
        try {
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

                return Helper::sendResponse('ok', true, null, 'Waiting Token deleted successfully');
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

                return Helper::sendResponse('ok', true, $customer_id, 'Waiting Token updated');
            }
        } catch (\Throwable $th) {
            //throw $th;
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error updating Waiting Tokens');
        }
    }

    public function search_customer_by_name($search, Request $request)
    {
        try {
            if (!auth()->user()->can('view_customer')) {
                abort(403, 'Unauthorized action.');
            }
            $start_date = $request->startDate;
            $end_date= $request->endDate;
            $per_page = $request->perPage;
            $customers = Customer::with('orders')->orderBy('created_at', 'desc');
            if ($start_date!=0 && $end_date!=0) {
                try {
                    $start = Carbon::createFromFormat('d/m/Y', $start_date)->startOfDay();
                    $end = Carbon::createFromFormat('d/m/Y', $end_date)->endOfDay();
                    $customers->whereBetween('created_at', [$start, $end]);
                } catch (\Exception $e) {
                    return Helper::sendResponse('bad_request', false, $e->getMessage(), 'Invalid date format');
                }
            }

            if(isset($search) && $search!=0){
                $customers->where('name', 'like', "%$search%");
            }

            $result = $customers->paginate($per_page);
            if ($result->count() >= 1) {
                return Helper::sendResponse('found', true, $result, 'Customers found');
            } else {
                return Helper::sendResponse('no_content', false, null, 'Customers not found');
            }
        } catch (\Throwable $th) {
            //throw $th;
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error searching Customers');
        }
    }

    public function export_customers(Request $request)
    {
        try {
            if (!auth()->user()->can('view_customer')) {
                abort(403, 'Unauthorized action.');
            }
            $search = $request->search;
            $start_date = $request->startDate;
            $end_date= $request->endDate;
            $customers = Customer::withCount('orders')->orderBy('created_at', 'desc');
            if ($start_date!=0 && $end_date!=0) {
                try {
                    $start = Carbon::createFromFormat('d/m/Y', $start_date)->startOfDay();
                    $end = Carbon::createFromFormat('d/m/Y', $end_date)->endOfDay();
                    $customers->whereBetween('created_at', [$start, $end]);
                } catch (\Exception $e) {
                    return Helper::sendResponse('bad_request', false, $e->getMessage(), 'Invalid date format');
                }
            }else{
                $start_date=null;
                $end_date = null;
            }

            if(isset($search) && $search!=0){
                $customers->where('name', 'like', "%$search%");
            }

            $result = $customers->get([
                'id', 'mobile', 'email','name','created_at', 'order_count'
            ])->map(function ($customer) {
                return (object) [
                    'ID' => $customer->id,
                    'Name' => $customer->name ?? null,
                    'Email' => $customer->email,
                    'Date' => Carbon::parse($customer->created_at)->format('d/m/Y'),
                    'Mobile Number' => $customer->mobile,
                    'Total Orders' => $customer->orders_count
                ];
            });


            $count = $result->count(['*']);
            $headings =
                ['ID','Name', 'Email', 'Date', 'Mobile Number', 'Total Orders'];
            $filters = [
                $start_date,
                $end_date,
                $search,
                $count
            ];
            return Excel::download(new CustomerExports($result, $headings,$filters), 'customers.xlsx');


        } catch (\Throwable $th) {
            //throw $th;
            return Helper::sendResponse('error', false, $th->getMessage(), 'Error searching Customers');
        }
    }
}
