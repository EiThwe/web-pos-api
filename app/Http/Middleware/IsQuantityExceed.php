<?php

namespace App\Http\Middleware;

use App\Models\Product;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsQuantityExceed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $voucher_records = $request->voucher_records;
        $productIds = array_map(fn ($record) => $record["product_id"], $voucher_records);

        $products = Product::whereIn("id", $productIds)->get();
        // $products = json_decode($products, true);
        logger($products);

        foreach ($products as $product) {
            $record = array_values(array_filter($voucher_records, function ($rec) use ($product) {
                return $rec["product_id"] == $product->id;
            }))[0];

            if ($product->total_stock < $record["quantity"]) {
                return response()->json(["message" => "quantity exceed"], 400);
            }
        }
        return $next($request);
    }
}
