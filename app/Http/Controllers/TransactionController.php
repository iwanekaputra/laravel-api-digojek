<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\TransactionCustomer;
use App\Models\TransactionDriver;
use App\Models\TransactionMerchant;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function customersGetTransaction()
    {
        $customer = Customer::find(auth()->user()->id);
        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found'
            ], 404);
        }



        $transactions = TransactionCustomer::when($customer->id != null, function ($query) use ($customer) {
            $query->where('customer_id', $customer->id);
        })->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'message' => 'Success Get Transaction',
            'data' => $transactions,
        ]);
    }

    public function driversGetTransaction(Request $request)
    {
        $driver = auth()->user();
        $transactions = TransactionDriver::where('driver_id', $driver->id)->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'message' => 'Success Get Transaction',
            'data' => $transactions,
        ]);
    }

    public function merchantGetTransaction(Request $request)
    {
        $merchant = auth()->user();
        $transactions = TransactionMerchant::when($merchant->id != null, function ($query) use ($merchant) {
            $query->where('merchant_id', $merchant->id);
        })->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'message' => 'Success Get Transaction',
            'data' => $transactions,
        ]);
    }
}
