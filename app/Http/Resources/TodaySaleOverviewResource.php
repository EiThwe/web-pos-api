<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TodaySaleOverviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "voucher_number" => $this->voucher_number,
            "total_sale" => $this->net_total,
            // "percentage" => $this->percentage
        ];
    }
}
