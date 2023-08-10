<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Http\Requests\StoreVoucherRequest;
use App\Http\Requests\UpdateVoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Models\Product;
use App\Models\VoucherRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            "voucher_number" => Str::random(10),
            "user_id" => Auth::id()
        ]);
        $voucher_records = [];

        foreach ($request->voucher_records as $record) {
            $record["voucher_id"] = $voucher->id;
            array_push($voucher_records, $record);

            $product = Product::find($record['product_id']);
            $product->total_stock -= $record["quantity"];

            $product->update();
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
        $voucher = Voucher::find($id);
        if (is_null($voucher)) {
            return response()->json(["message" => "voucher not found"], 404);
        };

        $voucher->customer = $request->customer ?? $voucher->customer;
        $voucher->phone = $request->phone ?? $voucher->phone;
        $voucher->total = $request->total ?? $voucher->total;
        $voucher->tax = $request->tax ?? $voucher->tax;
        $voucher->net_total = $request->net_total ?? $voucher->net_total;
        $voucher->user_id = Auth::id();
        $voucher->update();

        return response()->json(["message" => "voucher is updated successfully"]);
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
