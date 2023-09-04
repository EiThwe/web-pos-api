<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MonthlyRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "date" => $this->created_at->format("j M Y"),
            "vounchers" => $this->total_vouchers,
            "cash" => $this->total_cash,
            "tax" => $this->total_tax,
            "total" => $this->total_cash + $this->total_tax,

        ];
    }
}
