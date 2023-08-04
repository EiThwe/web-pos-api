<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Http\Requests\StoreVoucherRequest;
use App\Http\Requests\UpdateVoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Models\VoucherRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vouchers = Voucher::latest("id")->paginate(10)->withQueryString();

        return VoucherResource::collection($vouchers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVoucherRequest $request)
    {
        $net_total = array_reduce($request->voucher_records, function ($val, $record) {
            $val += $record["cost"];
            return $val;
        }, 0);
        $tax = $net_total * 0.03;
        $total = $net_total + $tax;

        $voucher = Voucher::create([
            "customer" => $request->customer ?? "unknown",
            "phone" => $request->phone,
            "net_total" => $net_total,
            "tax" => $tax,
            "total" => $total,
            "voucher_number" => rand(100000, 10000000),
            "user_id" => Auth::id()
        ]);
        $voucher_records = [];

        foreach ($request->voucher_records as $record) {
            $record["voucher_id"] = $voucher->id;
            array_push($voucher_records, $record);
        };

        $voucher->voucher_records()->createMany($voucher_records);

        return response()->json(["message" => "voucher is created successfully"], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $voucher = Voucher::find($id);
        if (is_null($voucher)) {
            return response()->json(["message" => "voucher not found"], 404);
        };

        return new VoucherResource($voucher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVoucherRequest $request,  $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $voucher = Voucher::find($id);
        if (is_null($voucher)) {
            return response()->json(["message" => "voucher not found"], 404);
        }
        $voucher->delete();

        return response()->json(["message" => "voucher is deleted successfully"], 200);
    }
}
