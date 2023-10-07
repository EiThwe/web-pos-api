<?php

namespace App\Http\Controllers\Overview;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockOverviewListResource;
use App\Models\Brand;
use App\Models\Product;
use App\Models\VoucherRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOverviewController extends Controller
{
    public function stockOverview()
    {
        $totalProducts = Product::count();
        $totalBrands = Brand::count();

        $instock = $this->generateStockPercentage(">", 10, $totalProducts);
        $lowStock = $this->generateStockPercentage("<=", 10, $totalProducts);
        $outOfStock = $this->generateStockPercentage("==", 0, $totalProducts);

        $productQuantity = VoucherRecord::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->get();

        $bestSellerBrands = $this->calculateBestSellerBrands($productQuantity);

        return response()->json([
            "total_products" => $totalProducts,
            "total_brands" => $totalBrands,
            "overview" => ["instock" => $instock, "low_stock" => $lowStock, "out_of_stock" => $outOfStock],
            "best_seller_brands" => $bestSellerBrands
        ], 200);
    }

    private function generateStockPercentage($operator, $value, $totalProducts)
    {
        $count = Product::where('total_stock', $operator, $value)->count();
        return round(($count / $totalProducts) * 100, 2);
    }

    private function calculateBestSellerBrands($productQuantity)
    {
        $bestSellerBrands = [];

        foreach ($productQuantity as $item) {
            $product = Product::find($item->product_id);
            $brandId = $product->brand_id;
            $brandName = Brand::find($brandId)->name;

            if (isset($bestSellerBrands[$brandId])) {
                $bestSellerBrands[$brandId]["total_quantity"] += $item->total_quantity;
            } else {
                $bestSellerBrands[$brandId] = [
                    "brand_id" => $brandId,
                    "brand_name" => $brandName,
                    "total_quantity" => $item->total_quantity
                ];
            }
        }

        usort($bestSellerBrands, fn ($a, $b) => $b['total_quantity'] - $a['total_quantity']);
        $bestFiveBrands = array_slice($bestSellerBrands, 0, 5);
        $totalQuantity = array_sum(array_column($bestFiveBrands, 'total_quantity'));

        // Calculate and add percentage to the array
        foreach ($bestFiveBrands as &$brand) {
            $percentage = ($brand['total_quantity'] / $totalQuantity) * 100;
            $brand['percentage'] = round($percentage, 1) . "%";
        }
        return $bestFiveBrands;
    }

    public function stockOverviewList()
    {
        $products = Product::when(request()->has("search"), function ($query) {
            $query->where(function (Builder $builder) {
                $search = request()->search;

                $builder->where("name", "like", "%" . $search . "%");
                $builder->orWhere("unit", "like", "%" . $search . "%");
                // $builder->orWhere("total_stock", "like", "%" . $search . "%");
            });
        })->when(request()->has('orderBy'), function ($query) {
            $sortType = request()->sort ?? 'asc';
            $query->orderBy(request()->orderBy, $sortType);
        })->latest("id")->paginate(10)->withQueryString();
        return StockOverviewListResource::collection($products);
    }
}
