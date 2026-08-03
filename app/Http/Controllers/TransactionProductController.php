<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Merchant;
use App\Models\Setting;
use App\Models\TransactionDriver;
use App\Models\TransactionMerchant;
use Illuminate\Http\Request;

class TransactionProductController extends Controller
{
    public function finishTransactionProduct(Request $request)
    {
        // "payment_method" : "Tunai",
        // "price_trip" : "10000",
        // "driver_id" : "1",
        // "merchants" : [2,3],
        // "products" : [{
        //     "merchant_id" : "2"
        // }]

        $driver = Driver::find($request->driver_id);

        $setting = Setting::get()[0];

        if ($request->payment_method == 'Tunai') {
            // add transaction driver
            $priceDriver = (int) $request->price_trip * $setting->potongan_admin_driver / 100;

            // create transaction driver
            $transactionDriver = TransactionDriver::create([
                'driver_id' => $driver->id,
                'price' => $priceDriver,
                'mode' => 'keluar',
                'type' => 'produk',
                'payment_method' => $request->payment_method,
                'balance' => $driver->balance - $priceDriver
            ]);

            // update saldo driver

            $driver->update([
                'balance' => $driver->balance - $priceDriver
            ]);

            // add transaction merchants
            foreach ($request->merchants as $merchant) {
                $totalPriceProduct = 0;
                foreach ($request->products as $product) {
                    if ((int)$product['merchant_id'] == (int)$merchant['merchant_id']) {
                        $totalPriceProduct += (int)$product['total_price'];
                    }
                }

                $priceMerchant = $totalPriceProduct * $setting->potongan_admin_merchant / 100;
                $userMerchant = Merchant::find((int)$merchant['merchant_id']);

                // create transaction merchant
                $transactionMerchant = TransactionMerchant::create([
                    'merchant_id' => (int)$merchant['merchant_id'],
                    'price' => $priceMerchant,
                    'mode' => 'keluar',
                    'type' => 'produk',
                    'payment_method' => $request->payment_method,
                    'balance' => $userMerchant->balance - $priceMerchant
                ]);


                // update saldo merchant
                $userMerchant->update([
                    'balance' => $userMerchant->balance - $priceMerchant
                ]);
            }


            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    "driver" => $driver,
                    "transaction_driver" => $transactionDriver
                ]
            ]);
        }

        if ($request->payment_method == 'Saldo') {
            $priceDriver = (int) $request->price_trip - (int) $request->price_trip * $setting->potongan_admin_driver / 100;

            // create transaction driver
            $transactionDriver = TransactionDriver::create([
                'driver_id' => $driver->id,
                'price' => $priceDriver,
                'mode' => 'masuk',
                'type' => 'produk',
                'payment_method' => $request->payment_method,
                'balance' => $driver->balance + $priceDriver
            ]);

            // update saldo driver

            $driver->update([
                'balance' => $driver->balance + $priceDriver
            ]);


            // add transaction merchants
            foreach ($request->merchants as $merchant) {
                $totalPriceProduct = 0;
                foreach ($request->products as $product) {
                    if ((int)$product['merchant_id'] == (int)$merchant['merchant_id']) {
                        $totalPriceProduct += (int)$product['total_price'];
                    }
                }

                $priceMerchant = $totalPriceProduct - $totalPriceProduct * $setting->potongan_admin_merchant / 100;
                $userMerchant = Merchant::find((int)$merchant['merchant_id']);

                // create transaction merchant
                $transactionMerchant = TransactionMerchant::create([
                    'merchant_id' => (int)$merchant['merchant_id'],
                    'price' => $priceMerchant,
                    'mode' => 'masuk',
                    'type' => 'produk',
                    'payment_method' => $request->payment_method,
                    'balance' => $userMerchant->balance + $priceMerchant
                ]);


                // update saldo merchant
                $userMerchant->update([
                    'balance' => $userMerchant->balance + $priceMerchant
                ]);
            }


            return response()->json([
                'status' => 200,
                'message' => 'success',
                'data' => [
                    "driver" => $driver,
                    "transaction_driver" => $transactionDriver
                ]
            ]);
        }
    }
}
