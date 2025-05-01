<?php

namespace App\Http\Controllers\API;

use App\Models\ItemCategory;
use App\Models\KOT;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class KOTController extends Controller
{

    public function all_kots()
    {
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
        } else {
            return response()->json([
                "code" => "200",
                "status" => "true",
                "message" => "No data available"
            ], 200);
        }
    }


    public function complete_kots(Request $request)
    {
        if (!auth()->user()->can('view_kot')) {
            abort(403, 'Unauthorized action.');
        }


        $validator = Validator::make($request->all(), [
            'kotIds' => ['required', 'array'],
            'setState' => ['numeric', 'required']
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
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
    }

    public function progress_kots(Request $request)
    {
        if (!auth()->user()->can('view_kot')) {
            abort(403, 'Unauthorized action.');
        }


        $validator = Validator::make($request->all(), [
            'kotIds' => ['required', 'array']
        ]);

        if ($validator->fails()) {
            return response()->json(['code' => 400, 'status' => 'false', 'message' => $firstError = $validator->messages()->first(),], 200);
        }

        KOT::whereIn('id', $request->kotIds)->update(['status' => 0]);

        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => 'KOTs marked as In Progress'
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('view_kot')) {
            abort(403, 'Unauthorized action.');
        }
        $kots = ItemCategory::has('kots')->with('kots.order')->get();
        // $allKots = Order::with('kots')->get();
        if ($kots) {
            return response()->json([
                "code" => "200",
                "status" => "true",
                "data" => $kots,
                "message" => "KOTS fetched successfully"
            ], 200);
        } else {
            return response()->json([
                "code" => "200",
                "status" => "true",
                "message" => "No data available"
            ], 200);
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
