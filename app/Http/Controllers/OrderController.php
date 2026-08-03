<?php

namespace App\Http\Controllers;

use App\Jobs\SendFcmNotification;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderCustomer;
use App\Models\OrderMerchant;
use App\Models\OrderProduct;
use App\Models\Setting;
use App\Models\TransactionCustomer;
use App\Models\TransactionDriver;
use App\Models\TransactionMerchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;



class OrderController extends Controller
{





    public function updateStatusOrder(Request $request)
    {
        DB::transaction(function () use ($request, &$order) {
            $order = Order::find($request->order_id);

            $order->update([
                'status' => $request->status
            ]);

            $order->orderCustomers()->first()->update([
                'status' => $request->status
            ]);
        });

        $order->orderCustomers;
        return response()->json([
            'success' => 200,
            'data' => $order
        ]);
    }

    public function updateStatusOrderPijat(Request $request)
    {
        DB::transaction(function () use ($request, &$order) {
            $order = Order::find($request->order_id);

            $order->update([
                'status' => $request->status
            ]);

            $order->orderCustomers()->first()->update([
                'status' => $request->status
            ]);
        });

        $order->orderCustomers;
        return response()->json([
            'success' => 200,
            'data' => $order
        ]);
    }

    public function driverTakeOrder(Request $request)
    {
        $order = Order::find($request->order_id);

        if ($order->driver_id) {
            return response()->json([
                'success' => true,
                'data' => [
                    'is-ordered' => true
                ]
            ]);
        }

        DB::transaction(function () use ($order, $request) {
            $order->update([
                'driver_id' => $request->driver_id,
                'status' => $request->status,
            ]);

            $order->orderCustomers()->first()->update([
                'status' => $request->status
            ]);
        });

        if ($order->type_order == 'produk') {

            $notifications = [];

            foreach ($order->orderMerchants as $orderMerchant) {
                $notification = [
                    'token' => $orderMerchant->merchant->device_token,
                    'title' => 'Yeay, Pesanan baru menantimu!',
                    'body' => ''
                ];

                $notifications[] = $notification;
            }

            SendFcmNotification::dispatch($notifications);
        }

        $order->orderCustomers;

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }


    public function driverTakeOrderMober(Request $request)
    {
        $orderCustomer = OrderCustomer::find($request->order_customer_id);

        if ($orderCustomer->order_id) {
            return response()->json([
                'success' => true,
                'data' => [
                    'is-ordered' => true
                ]
            ]);
        }

        DB::transaction(function () use ($request, $orderCustomer, &$order) {
            $order = Order::create([
                "driver_id" => $request->driver_id,
                'no_order' => Str::random(12),
                "price_trip" => $request->price_trip,
                "type_order" => $request->type_order,
                "grand_total" => $request->grand_total,
                "discount" => $request->discount ? $request->discount : null,
                "status" => $request->status
            ]);

            // update order customer
            $orderCustomer->update([
                'order_id' => $order->id,
                'status' => 'diterima'
            ]);
        });


        $order->orderCustomers;

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function addCustomerMober(Request $request)
    {
        $orderCustomer = OrderCustomer::find($request->order_customer_id);

        if ($orderCustomer->order_id) {
            return response()->json([
                'success' => true,
                'data' => [
                    'is-ordered' => true
                ]
            ]);
        }

        $orderCustomer->update([
            'order_id' => $request->order_id
        ]);

        return response()->json([
            'success' => true,
            'data' => $orderCustomer
        ]);
    }




    public function finishOrderRide(Request $request)
    {
        $order = Order::find($request->order_id);

        $setting = Setting::get()[0];

        $orderCustomer = $order->orderCustomers()->first();

        DB::transaction(function () use ($order, $orderCustomer, $setting) {
            $order->update([
                'status' => 'selesai'
            ]);

            $orderCustomer->update([
                'status' => 'selesai'
            ]);

            if ($orderCustomer->payment_method == 'Tunai') {

                $priceDriver = $orderCustomer->price_trip * $setting->potongan_admin_driver / 100;

                // create transaction driver
                $transactionDriver = TransactionDriver::create([
                    'driver_id' => $order->driver->id,
                    'price' => $priceDriver,
                    'mode' => 'keluar',
                    'type' => $order->type_order,
                    'payment_method' => 'Saldo',
                    'balance' => $order->driver->balance - $priceDriver
                ]);

                // update saldo driver
                $order->driver->update([
                    'balance' => $order->driver->balance - $priceDriver
                ]);
            }

            if ($orderCustomer->payment_method == 'Saldo') {
                $priceDriver = $orderCustomer->price_trip - $orderCustomer->price_trip * $setting->potongan_admin_driver / 100;
                $transactionDriver = TransactionDriver::create([
                    'driver_id' => $order->driver->id,
                    'price' => $priceDriver,
                    'mode' => 'masuk',
                    'type' => $order->type_order,
                    'payment_method' => $orderCustomer->payment_method,
                    'balance' => $order->driver->balance + $priceDriver
                ]);

                // update saldo driver
                $order->driver->update([
                    'balance' => $order->driver->balance + $priceDriver
                ]);
            }
        });

        if ($order) {
            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        }

        return response()->json([
            'success' => false,
        ], 404);
    }


    public function finishOrderProduct(Request $request)
    {
        $order = Order::find($request->order_id);

        $setting = Setting::get()[0];

        $orderCustomer = $order->orderCustomers()->first();

        DB::transaction(function () use ($request, $order, $orderCustomer, $setting) {
            $order->update([
                'status' => 'selesai'
            ]);
            $orderCustomer->update([
                'status' => 'selesai'
            ]);

            if ($orderCustomer->payment_method == 'Tunai') {

                $priceDriver = $orderCustomer->price_trip * $setting->potongan_admin_driver / 100;

                // create transaction driver
                $transactionDriver = TransactionDriver::create([
                    'driver_id' => $order->driver->id,
                    'price' => $priceDriver,
                    'mode' => 'keluar',
                    'type' => $order->type_order,
                    'payment_method' => 'Saldo',
                    'balance' => $order->driver->balance - $priceDriver
                ]);

                // update saldo driver
                $order->driver->update([
                    'balance' => $order->driver->balance - $priceDriver
                ]);



                // merchant
                foreach ($order->orderMerchants as $orderMerchant) {
                    $priceMerchant = $orderMerchant->grand_total * $setting->potongan_admin_merchant / 100;

                    // create transaction driver
                    $transactionMerchant = TransactionMerchant::create([
                        'merchant_id' => $orderMerchant->merchant_id,
                        'price' => $priceMerchant,
                        'mode' => 'keluar',
                        'type' => $order->type_order,
                        'payment_method' => 'Saldo',
                        'balance' => $orderMerchant->merchant->balance - $priceMerchant
                    ]);

                    // update saldo driver
                    $orderMerchant->merchant->update([
                        'balance' => $orderMerchant->merchant->balance - $priceMerchant
                    ]);
                }
            }

            if ($orderCustomer->payment_method == 'Saldo') {
                $priceDriver = $orderCustomer->price_trip - ($orderCustomer->price_trip * $setting->potongan_admin_driver / 100);
                $transactionDriver = TransactionDriver::create([
                    'driver_id' => $order->driver->id,
                    'price' => $priceDriver,
                    'mode' => 'masuk',
                    'type' => $order->type_order,
                    'payment_method' => $orderCustomer->payment_method,
                    'balance' => $order->driver->balance + $priceDriver
                ]);

                // update saldo driver
                $order->driver->update([
                    'balance' => $order->driver->balance + $priceDriver
                ]);

                // merchant
                foreach ($order->orderMerchants as $orderMerchant) {
                    $priceMerchant = $orderMerchant->grand_total - $orderMerchant->grand_total * $setting->potongan_admin_merchant / 100;
                    $transactionMerchant = TransactionMerchant::create([
                        'merchant_id' => $orderMerchant->merchant_id,
                        'price' => $priceMerchant,
                        'mode' => 'masuk',
                        'type' => $order->type_order,
                        'payment_method' => $orderCustomer->payment_method,
                        'balance' => $orderMerchant->merchant->balance + $priceMerchant
                    ]);

                    // update saldo driver
                    $orderMerchant->merchant->update([
                        'balance' => $orderMerchant->merchant->balance + $priceMerchant
                    ]);
                }
            }
        });

        if ($order) {
            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        }

        return response()->json([
            'success' => false,
        ], 404);
    }


    public function finishOrderPijat(Request $request)
    {
        $order = Order::find($request->order_id);

        $setting = Setting::get()[0];

        $orderCustomer = $order->orderCustomers()->first();

        DB::transaction(function () use ($request, $order, $orderCustomer, $setting) {
            $order->update([
                'status' => 'selesai'
            ]);
            $orderCustomer->update([
                'status' => 'selesai'
            ]);

            if ($orderCustomer->payment_method == 'Tunai') {

                // merchant
                foreach ($order->orderMerchants as $orderMerchant) {
                    $priceMerchant = $orderCustomer->grand_total * $setting->potongan_admin_driver / 100;

                    // create transaction driver
                    $transactionMerchant = TransactionMerchant::create([
                        'merchant_id' => $orderMerchant->merchant_id,
                        'price' => $priceMerchant,
                        'mode' => 'keluar',
                        'type' => $order->type_order,
                        'payment_method' => 'Saldo',
                        'balance' => $orderMerchant->merchant->balance - $priceMerchant
                    ]);

                    // update saldo driver
                    $orderMerchant->merchant->update([
                        'balance' => $orderMerchant->merchant->balance - $priceMerchant
                    ]);
                }
            }

            if ($orderCustomer->payment_method == 'Saldo') {


                // merchant
                foreach ($order->orderMerchants as $orderMerchant) {
                    $priceMerchant = $orderCustomer->grand_total - $orderCustomer->grand_total * $setting->potongan_admin_driver / 100;
                    $transactionMerchant = TransactionMerchant::create([
                        'merchant_id' => $orderMerchant->merchant_id,
                        'price' => $priceMerchant,
                        'mode' => 'masuk',
                        'type' => $order->type_order,
                        'payment_method' => $orderCustomer->payment_method,
                        'balance' => $orderMerchant->merchant->balance + $priceMerchant
                    ]);

                    // update saldo driver
                    $orderMerchant->merchant->update([
                        'balance' => $orderMerchant->merchant->balance + $priceMerchant
                    ]);
                }
            }
        });

        if ($order) {
            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        }

        return response()->json([
            'success' => false,
        ], 404);
    }




















    public function getOrderByDriverId(Request $request)
    {
        $order = Order::with(['orderCustomers'])->where('driver_id', $request->driver_id)->orderBy('created_at', 'DESC')->get();
        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }


    public function getOrderByDriverIdV2(Order $order)
    {
        $order = Order::with(['orderCustomers'])->find($order->id);
        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }





    public function orderMober(Request $request)
    {


        DB::transaction(function () use ($request, &$orderCustomer) {
            $getCustomer = Customer::find(auth()->user()->id);

            $orderCustomer =  OrderCustomer::create([
                'address_current_customer' => $request->address_current_customer,
                "address_destination" => $request->address_destination,
                "city_current_customer" => $request->city_current_customer,
                "city_destination" => $request->city_destination,
                "customer_id" => $getCustomer->id,
                "discount" => $request->discount ? $request->discount : null,
                "distance" => $request->distance,
                "grand_total" => $request->grand_total,
                "latitude_current_customer" => $request->latitude_current_customer,
                "latitude_destination" => $request->latitude_destination,
                "longitude_current_customer" => $request->longitude_current_customer,
                "longitude_destination" => $request->longitude_destination,
                "payment_method" => $request->payment_method,
                "price_trip" => $request->price_trip,
                "province_current_customer" => $request->province_current_customer,
                "province_destination" => $request->province_destination,
                "status" => $request->status,
                "status_payment" => $request->status_payment,
                "type_order" => $request->type_order,
            ]);

            $transactionCustomer = TransactionCustomer::create([
                'customer_id' => $getCustomer->id,
                'price' => $request->grand_total,
                'mode' => 'keluar',
                'type' => $request->type_order,
                'payment_method' => $request->payment_method
            ]);

            if ($request->payment_method == 'Saldo') {
                $getCustomer->update([
                    'balance' => $getCustomer->balance - $request->grand_total
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'data' => $orderCustomer
        ]);
    }


    public function updateStatusAngkutMober(Request $request)
    {
        $orderCustomer = OrderCustomer::find($request->order_customer_id);

        $orderCustomer->update([
            'status' => 'angkut'
        ]);

        return response()->json([
            'success' => true,
            'data' => $orderCustomer
        ]);
    }

    public function finishOrderCustomerMober(Request $request)
    {
        $orderCustomer = OrderCustomer::find($request->order_customer_id);

        $setting = Setting::get()[0];

        DB::transaction(function () use ($request, $orderCustomer, $setting) {
            $orderCustomer->update([
                'status' => 'selesai'
            ]);

            if ($orderCustomer->payment_method == 'Tunai') {

                $priceDriver = $orderCustomer->price_trip * $setting->potongan_admin_driver / 100;

                // create transaction driver
                $transactionDriver = TransactionDriver::create([
                    'driver_id' => $orderCustomer->order->driver_id,
                    'price' => $priceDriver,
                    'mode' => 'keluar',
                    'type' => $orderCustomer->type_order,
                    'payment_method' => 'Saldo',
                    'balance' => $orderCustomer->order->driver->balance - $priceDriver
                ]);

                // update saldo driver
                $orderCustomer->order->driver->update([
                    'balance' => $orderCustomer->order->driver->balance - $priceDriver
                ]);
            }

            if ($orderCustomer->payment_method == 'Saldo') {
                $priceDriver = $orderCustomer->price_trip - $orderCustomer->price_trip * $setting->potongan_admin_driver / 100;
                $transactionDriver = TransactionDriver::create([
                    'driver_id' => $orderCustomer->order->driver_id,
                    'price' => $priceDriver,
                    'mode' => 'masuk',
                    'type' => $orderCustomer->type_order,
                    'payment_method' => $orderCustomer->payment_method,
                    'balance' => $orderCustomer->order->driver->balance + $priceDriver
                ]);

                // update saldo driver
                $orderCustomer->order->driver->update([
                    'balance' => $orderCustomer->order->driver->balance + $priceDriver
                ]);
            }
        });


        return response()->json([
            'success' => true,
            'data' => $orderCustomer
        ]);
    }

    public function earningDriver(Request $request)
    {
        $orders = Order::where('driver_id', $request->driver_id)->whereDate("created_at", now())->get();
        $totalPrice = $orders->sum('price_trip');

        return response()->json([
            'status' => 200,
            'data' => [
                "count_order" => $orders->count(),
                "earning" => $totalPrice
            ]
        ]);
    }

    public function earningMerchant(Request $request)
    {
        $merchant = auth()->user();
        $orders = OrderMerchant::where('merchant_id', $merchant->id)->whereDate("created_at", now())->get();
        $totalPrice = $orders->sum('grand_total');

        $orderProducts = OrderProduct::where('merchant_id', $merchant->id)->whereDate('created_at', now())->get();


        return response()->json([
            'status' => 200,
            'data' => [
                "count_order" => $orders->count(),
                "earning" => $totalPrice,
                "count_product" => $orderProducts->count()
            ]
        ]);
    }

    public function finishOrderMober(Request $request)
    {
        $order =  Order::find($request->order_id);

        foreach ($order->orderCustomers as $orderCustomer) {
            if ($orderCustomer->status != 'selesai') {
                return response()->json([
                    'status' => 200,
                    'message' => 'masih ada penumpang yang belum di antar',
                    'data' => [
                        'is_finish' => false
                    ],
                ]);
            }
        }

        $order->update([
            'status' => 'selesai'
        ]);

        return response()->json([
            'status' => 200,
            'data' => $order
        ]);
    }
}
