<?php

namespace App\Http\Controllers\Overview;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\SaleRecord;
use App\Models\VoucherRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleOverviewController extends Controller
{
    public function saleOverview($type)
    {
        $dates = $this->calculateDateRange($type);
        $status = ($type == "yearly") ? "monthly" : "daily";


        $query = SaleRecord::whereBetween("created_at", $dates)->where("status", $status);
        // dd($query);
        $query2 = SaleRecord::whereBetween("created_at", $dates)->where("status", $status);

        $average = $query->avg("total_net_total");
        $records = $query->select("total_net_total", "created_at")->get();
        // dd($records);
        $max = $this->getMinMaxRecord($query, 'max');
        $min = $this->getMinMaxRecord($query2, 'min');
        $maxValue = $max['total_net_total'];
        $minValue = $min['total_net_total'];

        $recordsTotal = $records->sum('total_net_total');

        $minPercentage = ($minValue / $recordsTotal) * 100;
        $maxPercentage = ($maxValue / $recordsTotal) * 100;

        $product_sales = $this->getTopProductSales($dates);
        $best_seller_brands = $this->getBestSellerBrands($dates);

        return response()->json([
            "average" => round($average, 1),
            "totalSale"=>$recordsTotal,
            "max" => [
                "total_net_total" => $maxValue,
                "percentage" => round($maxPercentage, 1) . "%",
                "created_at" => $max["created_at"]
            ],
            "min" => [
                "total_net_total" => $minValue,
                "percentage" => round($minPercentage, 1) . "%",
                "created_at" => $min["created_at"]
            ],
            "sale_records" => $records,
            "product_sales" => $product_sales,
            "brand_sales" => $best_seller_brands
        ]);
    }

    private function calculateDateRange($type)
    {
        $currentDate = Carbon::now();
        // $startOfWeek = $currentDate->startOfWeek();
        // logger($startOfWeek);
        $startDate = '';
        $endDate = '';

        if ($type == "weekly") {
            $startDate = $currentDate->copy()->startOfWeek();
            $endDate =  $currentDate->copy()->endOfWeek();
        } else if ($type == "monthly") {
            $startDate = $currentDate->copy()->startOfMonth();
            $endDate =  $currentDate->copy()->endOfMonth();
        } else if ($type == "yearly") {
            $startDate = $currentDate->copy()->startOfYear();
            $endDate =  $currentDate->copy()->endOfYear();
        } else {
            abort(400, "weekly or monthly or yearly is required");
        }
        // dd($startDate, $endDate);
        return [$startDate, $endDate];
    }

    private function getMinMaxRecord($query, $type)
    {
        return $query->where('total_net_total', $query->{$type}('total_net_total'))
            ->select("total_net_total", "created_at")
            ->first();
    }

    private function getTopProductSales($dates)
    {
        $product_sales = VoucherRecord::whereBetween("created_at", $dates)
            ->with('product.brand') // Eager load relationships
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc("total_quantity")
            ->limit(5)
            ->get();

        return $product_sales->map(function ($voucherRecord) {
            $product = $voucherRecord->product;
            return [
                "product_name" => $product->name,
                "brand" => $product->brand->name,
                "sale_price" => $product->sale_price
            ];
        });
    }

    private function getBestSellerBrands($dates)
    {
        $brands = Brand::with(['voucherRecords' => function ($query) use ($dates) {
            $query->whereBetween('voucher_records.created_at', $dates);
        }])->get();

        // Calculate total quantity for each brand
        $bestSellerBrands = [];
        foreach ($brands as $brand) {
            $totalQuantity = $brand->voucherRecords->sum('quantity');
            $bestSellerBrands[] = [
                "brand_id" => $brand->id,
                "brand_name" => $brand->name,
                "total_quantity" => $totalQuantity,

            ];
        };

        usort($bestSellerBrands, function ($a, $b) {
            return $b['total_quantity'] - $a['total_quantity'];
        });

        $top5Brands = array_slice($bestSellerBrands, 0, 5);
        $totalQuantity = array_sum(array_column($top5Brands, 'total_quantity'));
        return array_map(function ($brand) use ($totalQuantity) {
            $brand["percentage"] = round($brand["total_quantity"] / $totalQuantity * 100, 1) . "%";
            return $brand;
        }, $top5Brands);
    }
}
