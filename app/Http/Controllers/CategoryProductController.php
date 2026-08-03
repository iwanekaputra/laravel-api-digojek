<?php

namespace App\Http\Controllers;

use App\Models\CategoryProduct;
use Illuminate\Http\Request;

class CategoryProductController extends Controller
{
    public function index(Request $request)
    {
        $categoryProducts = CategoryProduct::when($request->category_merchant_id != null, function ($query) use ($request) {
            $query->where("category_merchant_id", $request->category_merchant_id);
        })->get();

        if ($categoryProducts->count()) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $categoryProducts
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'There no category product',
        ]);
    }
}
