<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockOverviewListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array

    {
        $stock_level = "";
        if ($this->total_stock > 10) {
            $stock_level = "instock";
        } else if ($this->total_stock <= 0) {
            $stock_level = "out of stock";
        } else if ($this->total_stock <= 10) {
            $stock_level = "low stock";
        }

        return [
            "name" => $this->name,
            "brand" => $this->brand->name,
            "unit" => $this->unit,
            "sale_price" => $this->sale_price,
            "total_stock" => $this->total_stock,
            "stock_level" => $stock_level
        ];
    }
}
