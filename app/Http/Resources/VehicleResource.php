<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'brand' => $this->brand,
            'registration_number' => $this->registration_number,
            'color' => $this->color,
            'image' => asset("storage/" . $this->image),
            'vehicletype' => new VehicleTypeResource($this->vehicletype),
            'vehiclecategory' => [
                'id' => $this->vehicleCategory->id,
                'name' => $this->vehicleCategory->name,
                'slug' => $this->vehicleCategory->slug
            ]
        ];
    }
}
