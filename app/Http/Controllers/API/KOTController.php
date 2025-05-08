<?php

namespace App\Http\Controllers\API;

use App\Models\ItemCategory;
use App\Models\KOT;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Helpers\Helper;

class KOTController extends Controller
{

    public function all_kots()
    {
        try {
            if (!auth()->user()->can('view_kot')) {
                abort(403, 'Unauthorized action.');
            }
            $allKots = Order::with('kots')->where('order_status', '!=', 'Completed')->get();
            if ($allKots) {
                return response()->json([
                    "code" => "200",
                    "status" => "true",
                    "data" => $allKots,
                    "message" => "KOTS fetched successfully"
                ], 200);
                return Helper::sendResponse('ok', true, $allKots, 'KOTs fetched successfully');
            } else {
                return response()->json([
                    "code" => "200",
                    "status" => "true",
                    "message" => "No data available"
                ], 200);
                return Helper::sendResponse('no_content', true, null, 'No KOTs available');
            }
        } catch (\Throwable $th) {
            //throw $th;
            return Helper::sendResponse('error', true, $th->getMessage(), 'Error while fetching KOTs');
        }
    }


    public function complete_kots(Request $request)
    {
       try {
        if (!auth()->user()->can('view_kot')) {
            abort(403, 'Unauthorized action.');
        }


        $validator = Validator::make($request->all(), [
            'kotIds' => ['required', 'array'],
            'setState' => ['numeric', 'required']
        ]);

        if ($validator->fails()) {
            return Helper::sendResponse('bad_request', false, null, $validator->messages()->first());
        }


        KOT::whereIn('id', $request->kotIds)->update(['status' => $request->setState]);
        $message = '';
        if ($request->setState == 1) {
            $message = 'KOT marked as Prepared';
        } else if ($request->setState == 0) {
            $message = 'KOT marked as In Progress';
        }
        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => $message
        ]);
        return Helper::sendResponse('ok',true, null, $message);
       } catch (\Throwable $th) {
        //throw $th;
        return Helper::sendResponse('error', true, $th->getMessage(), 'Error while completing KOTs');

       }
    }



    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            if (!auth()->user()->can('view_kot')) {
                abort(403, 'Unauthorized action.');
            }
    
            $kots = ItemCategory::with('kots')->get();
    
            $kots->each(function ($category) {
                $category->kots = $category->kots->groupBy('order_id');
            });
            // $kots = ItemCategory::with('kots'->groupBy('kots.order_id'))->get();
    
            // $format_kots = $kots->map(function ($item_category) {
            //     return $item_category->map(fn($kot) => [
            //         'order_id' => $kot->order_id,
            //         'kot_id' => $kot->id,
            //         'status' => $kot->status,
            //         'item_data' => $kot->item_data
            //     ])->unique('order_id')->values();
            // });
            // $allKots = Order::with('kots')->get();
            if ($kots) {
                return response()->json([
                    "code" => "200",
                    "status" => "true",
                    "data" => $kots,
                    "message" => "KOTS fetched successfully"
                ], 200);
                return Helper::sendResponse('ok', true, $kots, 'KOTs fetched successfully');
            } else {
                return response()->json([
                    "code" => "200",
                    "status" => "true",
                    "message" => "No data available"
                ], 200);
                return Helper::sendResponse('no_content', true, null, 'No KOTs available');
            }
        } catch (\Throwable $th) {
            //throw $th;
            return Helper::sendResponse('error', true, $th->getMessage(), 'Error while fetching KOTs');

        }
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
    public function show(KOT $kOT)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(KOT $kOT)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, KOT $kOT)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(KOT $kOT)
    {
        //
    }
}
