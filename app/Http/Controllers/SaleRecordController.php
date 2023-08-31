<?php

namespace App\Http\Controllers;

use App\Models\SaleRecord;
use App\Http\Requests\StoreSaleRecordRequest;
use App\Http\Requests\UpdateSaleRecordRequest;
use App\Http\Resources\VoucherResource;
use App\Models\Setting;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SaleRecordController extends Controller
{
    public function saleOpen()
    {
        $setting = Setting::find(1);
        $setting->update(["status" => "open"]);

        return response()->json(["message" => "shop is open"]);
    }

    public function saleClose()
    {
        $setting = Setting::find(1);
        $setting->update(["status" => "close"]);

        $today = Carbon::today();
        $vouchers = Voucher::whereDate("created_at", $today)->get();
        $total_cash = $vouchers->sum("net_total");
        $total_tax = $vouchers->sum("tax");
        $total_vouchers = $vouchers->count();

        SaleRecord::create([
            "total_cash" => $total_cash,
            "total_tax" => $total_tax,
            "total_vouchers" => $total_vouchers,
            "user_id" => Auth::id()
        ]);

        return response()->json(["message" => "shop is close"], 200);
    }

    public function recent()
    {
        $today = Carbon::today();
        $vouchers = Voucher::whereDate("created_at", $today)->latest("id")->paginate(10)->withQueryString();

        return VoucherResource::collection($vouchers);
    }
}
