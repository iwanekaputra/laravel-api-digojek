<?php

namespace App\Http\Controllers\Api\PaymentMethod;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $methods = PaymentMethod::query()
            ->where('is_active', true)
            ->with([
                'banks' => function ($q) {
                    $q->where('is_active', true)
                        ->select(
                            'id',
                            'payment_method_id',
                            'bank_name',
                            'account_number',
                            'account_name'
                        );
                }
            ])
            ->orderBy('name')
            ->get();

        $data = $methods->map(function ($method) {
            return [
                'id' => $method->id,
                'name' => $method->name,
                'code' => $method->code,
                'icon_url' => $method->icon_url,
                'type' => $method->type,
                'channel_type' => $method->channel_type,
                'banks' => $method->type === 'manual'
                    ? $method->banks
                    : [],
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $data,
        ]);
    }
}
