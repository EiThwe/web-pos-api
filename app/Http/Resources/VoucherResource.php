<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "customer" => $this->customer,
            "phone" => $this->phone,
            "voucher_number" => $this->voucher_number,
            "total" => $this->total,
            "tax" => $this->tax,
            "net_total" => $this->net_total,
            "user" => $this->user->name,
            "date" => Carbon::parse($this->created_at)->format("h:i A"),
            // "voucher_records" => VoucherRecordResource::collection($this->voucher_records)
        ];
    }
}
