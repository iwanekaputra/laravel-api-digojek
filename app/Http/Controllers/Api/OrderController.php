<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\OrderCustomer;
use App\Models\Driver;
use App\Models\DriverOrderRequest;
use App\Models\CityVehiclePrice;
use App\Models\Customer;
use App\Models\TransactionCustomer;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'pickup_address' => 'required',
            'pickup_lat' => 'required',
            'pickup_lng' => 'required',
            'destination_address' => 'required',
            'destination_lat' => 'required',
            'destination_lng' => 'required',
            'city' => 'required',
            'vehicle_slug' => 'required',
            'payment_method' => 'required|string|in:saldo,cash', // Membatasi hanya saldo & cash
            'distance_km' => 'required|numeric|min:1'
        ]);

        $customer = Customer::find(auth()->user()->id);


        $km = ceil($request->distance_km);

        $price = CityVehiclePrice::where('city', $request->city)
            ->where('is_active', 1)
            ->whereHas('vehicle', function ($q) use ($request) {
                $q->where('slug', $request->vehicle_slug);
            })
            ->with('vehicle')
            ->firstOrFail();

        $billKm = max($km, $price->minimum_km);

        $total = $billKm * $price->price_per_km;

        if ($total < $price->minimum_price) {
            $total = $price->minimum_price;
        }

        /*
        =====================================
        2.5 VALIDASI SALDO CUSTOMER
        =====================================
        */
        if ($request->payment_method === 'saldo' && $customer->balance < $total) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak cukup untuk melakukan pemesanan.'
            ], 422); // Status 422 Unprocessable Entity
        }


        DB::beginTransaction();

        try {

            /*
            =====================================
            1. HITUNG ULANG HARGA DARI DATABASE
            =====================================
            */

            $km = ceil($request->distance_km);

            $price = CityVehiclePrice::where('city', $request->city)
                ->where('is_active', 1)
                ->whereHas('vehicle', function ($q) use ($request) {
                    $q->where('slug', $request->vehicle_slug);
                })
                ->with('vehicle')
                ->firstOrFail();

            $billKm = max($km, $price->minimum_km);

            $total = $billKm * $price->price_per_km;

            if ($total < $price->minimum_price) {
                $total = $price->minimum_price;
            }

            /*
            =====================================
            2. CREATE ORDER
            =====================================
            */

            if (strtolower($request->payment_method) === 'saldo') {
                $customer->decrement('balance', $total);
                $customer->refresh();

                TransactionCustomer::create([
                    'customer_id' => $customer->id,
                    'price' => $total,
                    'mode' => 'keluar',
                    'type' => $request->vehicle_slug,
                    'payment_method' => 'Saldo',
                    'balance' => $customer->balance
                ]);
            }


            $order = Order::create([
                'no_order' => 'ORD' . now()->timestamp,
                'price_trip' => $total,
                'type_order' => $request->vehicle_slug,
                'grand_total' => $total,
                'status' => 'searching_driver'
            ]);

            /*
            =====================================
            3. CREATE ORDER CUSTOMER
            =====================================
            */

            OrderCustomer::create([
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'address_current_customer' => $request->pickup_address,
                'address_destination' => $request->destination_address,
                'city_current_customer' => $request->city,
                'city_destination' => $request->city,
                'distance' => $km . ' km',
                'grand_total' => $total,
                'latitude_current_customer' => $request->pickup_lat,
                'longitude_current_customer' => $request->pickup_lng,
                'latitude_destination' => $request->destination_lat,
                'longitude_destination' => $request->destination_lng,
                'payment_method' => $request->payment_method,
                'price_trip' => $total,
                'province_current_customer' => '-',
                'province_destination' => '-',
                'status' => 'searching_driver',
                'status_payment' => 'pending',
                'type_order' => $request->vehicle_slug
            ]);

            /*
            =====================================
            4. CARI 5 DRIVER TERSEDIA
            =====================================
            */

            $drivers = Driver::where('city', $request->city)
                ->where('status_driver', 'online')
                ->where('is_delivering', 0)
                ->whereHas('vehicle.vehicleCategory', function ($query) use ($request) {
                    $query->where('slug', $request->vehicle_slug);
                })
                ->get();

            /*
            =====================================
            5. INSERT KE driver_order_requests
            =====================================
            */

            foreach ($drivers as $driver) {
                DriverOrderRequest::create([
                    'order_id' => $order->id,
                    'driver_id' => $driver->id,
                    'status' => 'pending',
                    'sent_at' => Carbon::now(),
                    'expired_at' => Carbon::now()->addSeconds(20),
                ]);

                // kirim notif FCM di sini
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mencari driver',
                'order_id' => $order->id,
                'drivers_sent' => $drivers->count()
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
