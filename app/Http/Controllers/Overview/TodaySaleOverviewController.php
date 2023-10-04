<?php

namespace App\Http\Controllers\Overview;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TodaySaleOverviewController extends Controller
{
    public function todaySaleOverview()
    {
        $today = Carbon::today();
        $vouchers = Voucher::whereDate("created_at", $today)->orderBy("net_total", "desc")->get();

        $totalAmount = $vouchers->sum("net_total");

        $top3Vouchers = $vouchers->take(3)->map(function ($voucher) use ($totalAmount) {
            return [
                "voucher_number" => $voucher->voucher_number,
                "net_total" => $voucher->net_total,
                "percentage" => round($voucher->net_total / $totalAmount * 100, 1) . "%"
            ];
        });

        return response()->json([
            "total_amount" => round($totalAmount, 2),
            "vouchers" => $top3Vouchers
        ]);
    }
}
