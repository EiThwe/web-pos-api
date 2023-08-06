<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
            [
                "id" => $this->id,
                "created_user" => ["name" => $this->user->name, "user_photo" => $this->user->user_photo],
                "product_name" => $this->product->name,
                "product_id"=>$this->product_id,
                "quantity" => $this->quantity,
                "more_information" => $this->more_information
            ];
    }
}
