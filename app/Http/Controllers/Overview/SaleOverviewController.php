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
        $query2 = SaleRecord::whereBetween("created_at", $dates)->where("status", $status);

        $average = $query->avg("total_net_total");
        $records = $query->select("total_net_total", "created_at")->get();
        $max = $this->getMinMaxRecord($query, 'max');
        $min = $this->getMinMaxRecord($query2, 'min');

        $product_sales = $this->getTopProductSales($dates);
        $best_seller_brands = $this->getBestSellerBrands($dates);

        return response()->json([
            "average" => $average,
            "max" => $max,
            "min" => $min,
            "sale_records" => $records,
            "product_sales" => $product_sales,
            "brand_sales" => $best_seller_brands
        ]);
    }

    private function calculateDateRange($type)
    {
        $currentDate = Carbon::now();
        $previousDate = '';

        if ($type == "weekly") {
            $previousDate = $currentDate->copy()->subDays(7);
        } else if ($type == "monthly") {
            $previousDate = $currentDate->copy()->subDays(30);
        } else if ($type == "yearly") {
            $previousDate = $currentDate->copy()->subDays(365);
        } else {
            abort(400, "weekly or monthly or yearly is required");
        }

        return [$previousDate, $currentDate];
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

    // private function getBestSellerBrands($dates)
    // {
    //     $product_quantity = VoucherRecord::whereBetween("created_at", $dates)
    //         ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
    //         ->groupBy('product_id')
    //         ->get()
    //         ->toArray();

    //     $best_seller_brands = [];

    //     foreach ($product_quantity as $item) {
    //         $brand_id = Product::find($item["product_id"])->brand_id;
    //         $brand_name = Brand::find($brand_id)->name;

    //         if (isset($best_seller_brands[$brand_id])) {
    //             $best_seller_brands[$brand_id]["total_quantity"] += $item["total_quantity"];
    //         } else {
    //             $best_seller_brands[$brand_id] = [
    //                 "brand_id" => $brand_id,
    //                 "brand_name" => $brand_name,
    //                 "total_quantity" => $item["total_quantity"]
    //             ];
    //         }
    //     }

    //     usort($best_seller_brands, fn ($a, $b) => $b['total_quantity'] - $a['total_quantity']);
    //     $best_seller_brands = array_slice($best_seller_brands, 0, 5);

    //     $totalQuantity = array_sum(array_column($best_seller_brands, 'total_quantity'));

    //     return array_map(function ($brand) use ($totalQuantity) {
    //         $brand["percentage"] = round($brand["total_quantity"] / $totalQuantity * 100, 1) . "%";
    //         return $brand;
    //     }, $best_seller_brands);
    // }

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
