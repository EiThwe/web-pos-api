<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class StockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = Stock::latest("id")->paginate(10)->withQueryString();
        return StockResource::collection($stocks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStockRequest $request)
    {
        $stock = Stock::create([
            "user_id" => Auth::id(),
            "product_id" => $request->product_id,
            "quantity" => $request->quantity,
            "more_information" => $request->more_information
        ]);
        $stock->product->total_stock += $request->quantity;
        $stock->product->save();
        return new StockResource($stock);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $stock = Stock::find($id);
        if (is_null($stock)) {
            return response()->json([
                "message" => "Stock not found"
            ], 404);
        }
        return new StockResource($stock);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStockRequest $request, string $id)
    {
        $stock = Stock::find($id);
        if (is_null($stock)) {
            return response()->json([
                "message" => "Stock not found"
            ], 404);
        }
        $stock->product_id = $request->product_id ?? $stock->product_id;
        $stock->quantity = $request->quantity ?? $stock->quantity;
        $stock->more_information = $request->more_information ?? $stock->more_information;
        $stock->update();
        
        $stock->product->total_stock += $request->quantity;
        $stock->product->save();
        return new StockResource($stock);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Gate::denies("isAdmin")) {
            return response()->json([
                "message" => "Unauthorized"
            ]);
        }
        $stock = Stock::find($id);
        if (is_null($stock)) {
            return response()->json([
                "message" => "stock not found"
            ], 404);
        }
        $stock->delete();
        return response()->json([
            "message" => "A stock is deleted successfully"
        ], 200);
    }
}
