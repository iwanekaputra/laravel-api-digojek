<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
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
            'birthday' => $this->birthday,
            'gender' => $this->gender,
            'email' => $this->email,
            'balance' => $this->balance,
            'city' => $this->city,
            'province' => $this->province,
            'address' => $this->address,
            'status' => $this->status,
            'vehicle' => new VehicleResource($this->vehicle),
            'agreement' => $this->agreement,
            'nohp' => $this->nohp,
            'image' => $this->image,
            'link_image' => $this->link_image,
            'token_driver' => $this->token,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status_driver' => $this->status_driver,
            'is_mober' => $this->is_mober,
            'is_delivering' => $this->is_delivering,
            'code_referal' => $this->code_referal,
            'referal' => $this->referal,
            'token' => $this->device_token
        ];
    }
}
