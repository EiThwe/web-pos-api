<?php

namespace App\Http\Controllers\Overview;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SaleRecord;
use App\Models\User;
use App\Models\VoucherRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardOverviewController extends Controller
{
    public function dashboardOverview($type)
{
    $validTypes = ["weekly", "monthly", "yearly"];

    if (!in_array($type, $validTypes)) {
        return response()->json(["message" => "Invalid type. Allowed values: weekly, monthly, yearly"]);
    }

    $currentDate = Carbon::now();
    $previousDate = $this->getPreviousDate($type);
    $status = $this->getStatus($type);

    $records = $this->getSaleRecords($previousDate, $currentDate, $status);
    $totalStocks = $this->getTotalStocks();
    $totalStaff = $this->getTotalStaff();
    $stats = $this->getFinancialStats($previousDate, $currentDate);

    return response()->json([
        "total_stocks" => $totalStocks,
        "total_staff" => $totalStaff,
        "sale_records" => $records,
        "stats" => $stats
    ]);
}

private function getPreviousDate($type)
{
    $previousDate = Carbon::now();

    switch ($type) {
        case "weekly":
            $previousDate->subWeek();
            break;
        case "monthly":
            $previousDate->subMonth();
            break;
        case "yearly":
            $previousDate->subYear();
            break;
    }

    return $previousDate;
}

private function getStatus($type)
{
    return ($type == "yearly") ? "monthly" : "daily";
}

private function getSaleRecords($startDate, $endDate, $status)
{
    return SaleRecord::whereBetween("created_at", [$startDate, $endDate])
        ->where("status", $status)
        ->select("total_net_total", "created_at")
        ->get();
}

private function getTotalStocks()
{
    return Product::where("total_stock", ">=", 0)->sum("total_stock");
}

private function getTotalStaff()
{
    return User::where("id", "!=", 1)->count();
}

private function getFinancialStats($startDate, $endDate)
{
    $voucherRecords = VoucherRecord::whereBetween("created_at", [$startDate, $endDate])
        ->select("product_id", DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(cost) as total_cost'))
        ->groupBy("product_id")
        ->get();

    $totalIncome = $voucherRecords->sum("total_cost");
    $totalExpense = 0;

    foreach ($voucherRecords as $record) {
        $product = Product::find($record->product_id);
        $totalExpense += $product->actual_price * $record->total_quantity;
    }

    $totalProfit = $totalIncome - $totalExpense;

    return [
        "total_income" => $totalIncome,
        "total_expense" => $totalExpense,
        "total_profit" => $totalProfit
    ];
}

}
