<?php

namespace App\Http\Controllers\API;

use App\Models\ItemCategory;
use App\Models\KOT;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class KOTController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    { if (!auth()->user()->can('view_kot')) {
        abort(403, 'Unauthorized action.');
    }
        $kots = ItemCategory::has('kots')->with('kots.order')->get();
        if($kots){
            return response()->json([
                "code" => "200",
                "status" => "true",
                "data" => $kots,
                "message" => "KOTS fetched successfully"
            ], 200);
        }
        else{
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
