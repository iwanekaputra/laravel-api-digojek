<?php

namespace App\Http\Resources;

use App\Models\CategoryProduct;
use App\Models\GalleryProduct;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'product_name' => $this->product_name,
            'merchant' => Merchant::find($this->merchant_id),
            'product_stock' => $this->product_stock,
            'description' => $this->description,
            'status' => $this->status,
            'category_product_id' => CategoryProduct::find($this->category_product_id),
            'weight' => $this->weight,
            'price' => $this->price,
            'images' => GalleryProduct::where('product_id', $this->id)->get(),
            'duration_in_hours' => $this->duration_in_hours,
            'duration_minutes' => $this->duration_minutes,
            'price_box' => $this->price_box,
            'unit_type' => $this->unit_type,
            'weight_box' => $this->weight_box
        ];
    }
}
