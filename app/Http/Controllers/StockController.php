<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Http\Resources\StockOverviewListResource;
use App\Http\Resources\StockResource;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Stock;
use App\Models\VoucherRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $stocks = Stock::latest("id")->paginate(10)->withQueryString();
        // return StockResource::collection($stocks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockRequest $request)
    {
        if ($request->more_information && Str::length($request->more_formation < 50)) {
            return response()->json(["message" => "more_formation must be greater than 50 characters"]);
        }

        $stock = Stock::create([
            "user_id" => Auth::id(),
            "product_id" => $request->product_id,
            "quantity" => $request->quantity,
            "more_information" => $request->more_information,
        ]);
        $stock->product->total_stock += $request->quantity;
        $stock->product->save();
        return response()->json(["message" => "A stock is created successfully"], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStockRequest $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    private function generateStockPercentage($op, $quantity, $total)
    {
        $stock = Product::where("total_stock", $op, $quantity)->count();
        $percentage = $stock / $total * 100 . "%";

        return ["stock" => $stock, "percentage" => $percentage];
    }

    public function stockOverview()
    {
        $total_products = Product::count();
        $total_brands = Brand::count();

        $instock = $this->generateStockPercentage(">", 10, $total_products);
        $low_stock =  $this->generateStockPercentage("<=", 10, $total_products);
        $out_of_stock =  $this->generateStockPercentage("==", 0, $total_products);

        $product_quantity = VoucherRecord::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->get();

        for ($i = 0; $i < count($product_quantity); $i++) {
            $product = Product::find($product_quantity[$i]["product_id"]);
            $product_quantity[$i]["brand_id"] = $product->brand_id;
            $product_quantity[$i]["brand_name"] = Brand::find($product->brand_id)->name;
        };


        $product_quantity = json_decode($product_quantity, true);

        $best_seller_brands = array();

        foreach ($product_quantity as $item) {
            $isExist = array_search($item["brand_id"], array_column($best_seller_brands, "brand_id"));

            if (is_numeric($isExist)) {
                $best_seller_brands[$isExist]["total_quantity"] += $item["total_quantity"];
            } else {
                array_push($best_seller_brands, [
                    "brand_id" => $item["brand_id"],
                    "brand_name" => $item["brand_name"],
                    "total_quantity" => $item["total_quantity"]
                ]);
            }
        }

        usort($best_seller_brands, fn ($a, $b) => $b['total_quantity'] - $a['total_quantity']);

        $best_seller_brands = array_slice($best_seller_brands, 0, 5);

        return response()->json([
            "total_products" => $total_products,
            "total_brands" => $total_brands,
            "overview" => ["instock" => $instock, "low_stock" => $low_stock, "out_of_stock" => $out_of_stock],
            "best_seller_brands" => $best_seller_brands,

        ], 200);
    }
    
    public function stockOverviewList(){
        $products = Product::latest("id")->paginate(10)->withQueryString();
        return StockOverviewListResource::collection($products);
    }
}
