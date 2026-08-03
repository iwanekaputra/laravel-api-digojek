<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'image' => $this->image,
            'link_image' => $this->link_image,
            'balance' => $this->balance,
            'nohp' => $this->nohp,
            'status' => $this->status,
            'token' => $this->token,
            'device_token' => $this->device_token
        ];
    }
}
