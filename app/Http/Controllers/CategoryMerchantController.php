<?php

namespace App\Http\Controllers;

use App\Models\CategoryMerchant;

class CategoryMerchantController extends Controller
{
    public function index()
    {

        $categoryMerchants = CategoryMerchant::get();

        if ($categoryMerchants->count()) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $categoryMerchants
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'There no category merchant',
        ]);
    }
}
