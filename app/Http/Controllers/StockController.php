<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Http\Resources\StockHistoryResource;
use App\Http\Resources\StockOverviewListResource;
use App\Http\Resources\StockResource;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Stock;
use App\Models\VoucherRecord;
use Exception;
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
        if ($request->more_information && strlen($request->more_information) < 50) {
            return response()->json(["message" => "more_formation must be greater than 50 characters"]);
        }
        try {
            DB::beginTransaction();

            $stock = Stock::create([
                "user_id" => Auth::id(),
                "product_id" => $request->product_id,
                "quantity" => $request->quantity,
                "more_information" => $request->more_information || "",
            ]);
            $stock->product->total_stock += $request->quantity;
            $stock->product->save();
            DB::commit();

            return response()->json(["message" => "A stock is created successfully"], 201);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function stockHistory($productId)
    {
        $stock_history = Stock::where("product_id", $productId)->paginate(5)->withQueryString();

        return StockHistoryResource::collection($stock_history);
    }
}
