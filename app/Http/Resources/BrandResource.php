<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //
        return [
            "brand_id" => $this->id,
            "name" => $this->name,
            "company" => $this->company,
            "information" => $this->information,
            "created_user" => ["name" => $this->user->name, "user_photo" => $this->user->user_photo],
            "photo" => $this->photo
        ];
    }
}
