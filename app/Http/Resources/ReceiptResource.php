<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "total" => $this->total,
            "tax" => $this->tax,
            "net_total" => $this->net_total,
            "voucher_records" => $this->items

        ];
    }
}
