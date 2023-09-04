<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class YearlySaleRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "month" => $this->created_at->format("M"),
            "year" => $this->created_at->format("Y"),
            "vounchers" => $this->total_vouchers,
            "cash" => $this->total_cash,
            "tax" => $this->total_tax,
            "total" => $this->total_cash + $this->total_tax,

        ];
    }
}
