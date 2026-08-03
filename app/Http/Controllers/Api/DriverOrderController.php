<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CityVehiclePrice;
use App\Models\Driver;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\DriverOrderRequest;
use App\Models\Order;
use App\Models\OrderCustomer;
use App\Models\TransactionDriver;
use App\Models\VehicleCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverOrderController extends Controller
{

    public function orderDetail($id)
    {
        $driver = Auth::user();

        /*
    =====================================
    1. AMBIL ORDER BERDASARKAN ID
    =====================================
    */

        $order = Order::with([
            'driver',
            'orderCustomers'
        ])
            ->where('driver_id', $driver->id)
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan'
            ]);
        }

        $customer = $order->orderCustomers->first();
        $driver   = $order->driver;

        $responseData = [

            'order_id'   => $order->id,
            'no_order'   => $order->no_order,
            'status'     => $order->status,
            'type_order' => $order->type_order,
            'price'      => $order->grand_total,

            /*
            PICKUP & DESTINATION
            */

            'pickup_address' =>
            $customer->address_current_customer,

            'destination_address' =>
            $customer->address_destination,

            'pickup_lat' =>
            $customer->latitude_current_customer,

            'pickup_lng' =>
            $customer->longitude_current_customer,

            'destination_lat' =>
            $customer->latitude_destination,

            'destination_lng' =>
            $customer->longitude_destination,
            'services' => [
                'link_image' => 'https://api.digojek.com/storage/3zbW89gxpQ.png'
            ],

            /*

                /*
            CUSTOMER
            */

            'customer' => $customer ? [
                'name' => $customer->customer->name ?? '-',
                'phone' => $customer->customer->nohp ?? '-',
                'link_image' => $customer->customer->link_image ?? null
            ] : null,


            /*
            PAYMENT
            */

            'payment_method' =>
            $customer->payment_method,

            'status_payment' =>
            $customer->status_payment,
            'created_at' => $order->created_at,

            'ship_cargo' => null

        ];

        if ($order->type_order === 'SHIP_PORTER' && $order->shipCargo) {
            $responseData['ship_cargo'] = [
                'weight_kg'            => (float) $order->shipCargo->weight_kg,
                'length_cm'            => $order->shipCargo->length_cm,
                'width_cm'             => $order->shipCargo->width_cm,
                'height_cm'            => $order->shipCargo->height_cm,
                'service_type'         => $order->shipCargo->service_type, // turun_ke_kapal, dsb
                'origin_location'      => $order->shipCargo->origin_location,
                'destination_location' => $order->shipCargo->destination_location,
                'ship_name'            => $order->shipCargo->ship_name,
                'notes'                => $order->shipCargo->notes,
            ];
        }

        /*
    =====================================
    2. RESPONSE (SAMA DENGAN TRACK ORDER)
    =====================================
    */

        return response()->json([
            'success' => true,
            'data' => $responseData
        ]);
    }
    public function incoming(Request $request)
    {

        $driver = Auth::user();



        /*
        ===================================
        1. EXPIRE REQUEST YANG SUDAH LEWAT
        ===================================
        */

        // DriverOrderRequest::where('driver_id', $driver->id)
        //     ->where('status', 'pending')
        //     ->where('expired_at', '<=', Carbon::now())
        //     ->update([
        //         'status' => 'expired'
        //     ]);

        /*
        ===================================
        2. AMBIL ORDER MASUK DRIVER
        ===================================
        */

        $rows = DriverOrderRequest::with([
            'order',
            'order.orderCustomers'
        ])
            ->where('driver_id', $driver->id)
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->get();

        $data = [];

        foreach ($rows as $row) {

            $customer = $row->order->orderCustomers->first();

            $data[] = [
                'request_id' => $row->id,
                'order_id' => $row->order_id,
                'no_order' => $row->order->no_order,

                'pickup_address' =>
                $customer->address_current_customer,

                'destination_address' =>
                $customer->address_destination,

                'distance' =>
                $customer->distance,

                'price' =>
                $row->order->grand_total,

                'type_order' =>
                $row->order->type_order,

                'payment_method' =>
                $customer->payment_method,

                'expired_at' =>
                $row->expired_at,

                'status' =>
                $row->status
            ];
        }

        return response()->json([
            'success' => true,
            'total' => count($data),
            'data' => $data
        ]);
    }


    public function acceptOrder(Request $request)
    {
        $request->validate([
            'order_id'  => 'required'
        ]);

        $driver = Auth::user();

        DB::beginTransaction();

        try {

            /*
            =====================================
            1. VALIDASI REQUEST DRIVER
            =====================================
            */

            $driverRequest = DriverOrderRequest::where('order_id', $request->order_id)
                ->where('driver_id', $driver->id)
                ->lockForUpdate()
                ->first();

            if (!$driverRequest) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request tidak ditemukan'
                ], 404);
            }

            if ($driverRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order sudah tidak tersedia'
                ], 400);
            }

            // if (
            //     $driverRequest->expired_at &&
            //     Carbon::now()->gt($driverRequest->expired_at)
            // ) {

            //     $driverRequest->update([
            //         'status' => 'expired'
            //     ]);

            //     DB::commit();

            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Order sudah expired'
            //     ], 400);
            // }

            /*
            =====================================
            2. LOCK ORDER (SIAPA CEPAT DIA DAPAT)
            =====================================
            */

            $order = Order::lockForUpdate()->find($request->order_id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            if ($order->driver_id != null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order sudah diambil driver lain'
                ], 400);
            }

            $orderCustomer = OrderCustomer::where('order_id', $order->id)->first();

            if ($orderCustomer) {
                // Ambil setting batas minimum saldo berdasarkan kota dan jenis kendaraan orderan ini
                $priceSetting = CityVehiclePrice::where('city', $orderCustomer->city_current_customer)
                    ->where('is_active', 1)
                    ->whereHas('vehicle', function ($q) use ($order) {
                        $q->where('slug', $order->type_order);
                    })
                    ->first();

                // Gunakan nilai dari database jika ada, jika tidak ada default ke 0
                $minRequiredBalance = $priceSetting ? $priceSetting->minimum_driver_balance : 0;

                // Jika saldo driver saat ini di bawah batas minimal kota tersebut
                if ($driver->balance < $minRequiredBalance) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Saldo dompet Anda tidak cukup untuk mengambil orderan ini. Minimal saldo: Rp ' . number_format($minRequiredBalance, 0, ',', '.')
                    ], 422); // Status 422 Unprocessable Entity
                }
            }

            /*
            =====================================
            3. DRIVER MENANG
            =====================================
            */

            $order->update([
                'driver_id' => $driver->id,
                'status' => 'accepted'
            ]);

            /*
            =====================================
            4. UPDATE DRIVER
            =====================================
            */

            Driver::where('id', $driver->id)
                ->update([
                    'is_delivering' => 1
                ]);

            /*
            =====================================
            5. UPDATE REQUEST PEMENANG
            =====================================
            */

            $driverRequest->update([
                'status' => 'accepted',
                'responded_at' => Carbon::now()
            ]);

            /*
            =====================================
            6. DRIVER LAIN GAGAL
            =====================================
            */

            DriverOrderRequest::where('order_id', $request->order_id)
                ->where('driver_id', '!=', $driver->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'expired',
                    'responded_at' => Carbon::now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil diambil',
                'order_id' => $order->id,
                'driver_id' => $driver->id,
                'status' => 'accepted'
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function currentOrder(Request $request)
    {
        $driver = Auth::user();

        /*
        =====================================
        1. AMBIL ORDER AKTIF DRIVER
        =====================================
        */

        $order = Order::where('driver_id', $driver->id)
            ->whereIn('status', [
                'accepted',
                'arrived_pickup',
                'on_trip'
            ])
            ->latest('id')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada order aktif'
            ]);
        }

        $customer = $order->orderCustomers->first();

        /*
        =====================================
        2. RESPONSE
        =====================================
        */

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'no_order' => $order->no_order,

                'status' => $order->status,
                'type_order' => $order->type_order,

                'price' => $order->grand_total,

                'pickup_address' =>
                $customer->address_current_customer,

                'destination_address' =>
                $customer->address_destination,

                'pickup_lat' =>
                $customer->latitude_current_customer,

                'pickup_lng' =>
                $customer->longitude_current_customer,

                'destination_lat' =>
                $customer->latitude_destination,

                'destination_lng' =>
                $customer->longitude_destination,

                'distance' =>
                $customer->distance,

                'payment_method' =>
                $customer->payment_method
            ]
        ]);
    }


    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $driver = Auth::user();

        /*
        =====================================
        1. UPDATE LOKASI DRIVER
        =====================================
        */

        Driver::where('id', $driver->id)
            ->update([
                'latitude'  => $request->latitude,
                'longitude' => $request->longitude
            ]);

        /*
        =====================================
        2. UPDATE KE ORDER AKTIF (OPTIONAL)
        customer tracking lebih cepat
        =====================================
        */

        Order::where('driver_id', $driver->id)
            ->whereIn('status', [
                'accepted',
                'arrived_pickup',
                'on_trip'
            ])
            ->update([
                'latitude_current_driver'  => $request->latitude,
                'longitude_current_driver' => $request->longitude
            ]);

        /*
        =====================================
        3. RESPONSE
        =====================================
        */

        return response()->json([
            'success' => true,
            'message' => 'Lokasi driver diperbarui',
            'data' => [
                'driver_id' => $driver->id,
                'latitude'  => $request->latitude,
                'longitude' => $request->longitude
            ]
        ]);
    }


    public function updateOrderStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required',
            'status' => 'required'
        ]);

        $driver = Auth::user();

        /*
        =====================================
        STATUS YANG DIIZINKAN
        =====================================
        */

        $allowedStatus = [
            'arrived_pickup',
            'on_trip',
            'completed'
        ];

        if (!in_array($request->status, $allowedStatus)) {
            return response()->json([
                'success' => false,
                'message' => 'Status tidak valid'
            ], 422);
        }

        DB::beginTransaction();

        try {

            /*
            =====================================
            VALIDASI ORDER MILIK DRIVER
            =====================================
            */

            $order = Order::where('id', $request->order_id)
                ->where('driver_id', $driver->id)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            /*
            =====================================
            UPDATE ORDER
            =====================================
            */

            $order->update([
                'status' => $request->status
            ]);

            /*
            =====================================
            UPDATE ORDER_CUSTOMERS
            =====================================
            */

            OrderCustomer::where('order_id', $order->id)
                ->update([
                    'status' => $request->status
                ]);

            /*
            =====================================
            JIKA SELESAI
            =====================================
            */

            if ($request->status == 'completed') {

                Driver::where('id', $driver->id)
                    ->update([
                        'is_delivering' => 0
                    ]);

                OrderCustomer::where('order_id', $order->id)
                    ->update([
                        'status_payment' => 'paid'
                    ]);

                $vehicleCategory = VehicleCategory::where('slug', strtolower($order->orderCustomers()->first()->type_order))->first();

                $cityVehiclePrice = CityVehiclePrice::where('city', strtolower($order->orderCustomers()->first()->city_current_customer))->where('vehicle_category_id', $vehicleCategory->id)->first();

                if (strtolower($order->orderCustomers()->first()->payment_method) == 'cash') {
                    $priceCutPriceTrip = $order->price_trip * ($cityVehiclePrice->driver_cut_percentage / 100);
                    $newBalance = $order->driver->balance - $priceCutPriceTrip;
                    $order->driver->update([
                        'balance' => $newBalance
                    ]);

                    TransactionDriver::create([
                        'driver_id' => $driver->id,
                        'price' => $priceCutPriceTrip,
                        'mode' => 'keluar',
                        'type' => 'Ship Porter',
                        'payment_method' => 'Saldo',
                        'balance' => $newBalance
                    ]);
                } else if ($order->orderCustomers()->first()->payment_method == 'saldo') {
                    $priceCutPriceTrip = ($order->price_trip - ($order->price_trip * ($cityVehiclePrice->driver_cut_percentage / 100)));
                    $newBalance = $order->driver->balance + $priceCutPriceTrip;
                    $order->driver->update([
                        'balance' => $newBalance
                    ]);

                    TransactionDriver::create([
                        'driver_id' => $driver->id,
                        'price' => $order->price_trip,
                        'mode' => 'masuk',
                        'type' => 'Ship Porter',
                        'payment_method' => 'Saldo',
                        'balance' => $newBalance
                    ]);
                }
            }




            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status order diperbarui',
                'data' => [
                    'order_id' => $order->id,
                    'status' => $request->status
                ]
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function cancelOrder(Request $request)
    {
        $request->validate([
            'order_id'  => 'required',
            'reason'    => 'nullable|string|max:255'
        ]);

        $driver = Auth::user();

        DB::beginTransaction();

        try {

            /*
            =====================================
            1. VALIDASI ORDER MILIK DRIVER
            =====================================
            */

            $order = Order::lockForUpdate()
                ->find($request->order_id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            if ($order->driver_id != $driver->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order bukan milik driver'
                ], 403);
            }

            /*
            =====================================
            2. STATUS YANG BOLEH DICANCEL DRIVER
            =====================================
            */

            $allowed = [
                'accepted',
                'arrived_pickup'
            ];

            if (!in_array($order->status, $allowed)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak bisa dibatalkan'
                ], 400);
            }

            /*
            =====================================
            3. LEPAS DRIVER DARI ORDER
            =====================================
            */

            $order->update([
                'driver_id' => null,
                'status' => 'searching_driver'
            ]);

            OrderCustomer::where('order_id', $order->id)
                ->update([
                    'status' => 'searching_driver'
                ]);

            /*
            =====================================
            4. DRIVER KEMBALI AVAILABLE
            =====================================
            */

            Driver::where('id', $driver->id)
                ->update([
                    'is_delivering' => 0
                ]);

            /*
            =====================================
            5. REQUEST DRIVER INI = CANCELLED
            =====================================
            */

            DriverOrderRequest::where('order_id', $order->id)
                ->where('driver_id', $driver->id)
                ->update([
                    'status' => 'cancelled',
                    'responded_at' => Carbon::now()
                ]);

            /*
            =====================================
            6. CARI DRIVER BARU (3 DRIVER)
            =====================================
            */

            $newDrivers = Driver::where('city', function ($q) use ($order) {
                $q->select('city_current_customer')
                    ->from('order_customers')
                    ->whereColumn('order_id', 'orders.id')
                    ->limit(1);
            })
                ->where('status_driver', 'online')
                ->where('is_delivering', 0)
                ->where('id', '!=', $driver->id)
                ->limit(3)
                ->get();

            foreach ($newDrivers as $driver) {

                DriverOrderRequest::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'driver_id' => $driver->id
                    ],
                    [
                        'status' => 'pending',
                        'sent_at' => Carbon::now(),
                        'expired_at' => Carbon::now()->addSeconds(20)
                    ]
                );

                // kirim notif FCM di sini
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order dibatalkan driver, mencari driver baru',
                'data' => [
                    'order_id' => $order->id,
                    'status' => 'searching_driver',
                    'new_drivers_sent' => $newDrivers->count(),
                    'reason' => $request->reason
                ]
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function orderHistory(Request $request)
    {
        $driver = Auth::user();

        /*
        =====================================
        1. AMBIL HISTORY ORDER DRIVER
        =====================================
        */

        $rows = Order::where('driver_id', $driver->id)

            ->latest('id')
            ->paginate(10);

        /*
        =====================================
        2. FORMAT DATA
        =====================================
        */

        $data = [];

        foreach ($rows as $row) {

            $customer = $row->orderCustomer;

            $data[] = [
                'order_id'   => $row->id,
                'no_order'   => $row->no_order,
                'status'     => $row->status,
                'type_order' => $row->type_order,
                'flow_type' => 'ride',
                'price'      => $row->grand_total,

                'pickup_address' =>
                $customer->address_current_customer ?? '-',

                'destination_address' =>
                $customer->address_destination ?? '-',

                'distance' =>
                $customer->distance ?? 0,

                'payment_method' =>
                $customer->payment_method ?? '-',

                'created_at' =>
                $row->created_at,
                'services' => [
                    'link_image' => 'https://api.digojek.com/storage/3zbW89gxpQ.png'
                ],

                'customer' => [
                    'id' => $customer->customer_id ?? null,
                    'name' => $customer->customer->name ?? '-',
                    'phone' => $customer->customer->nohp ?? '-',
                ]
            ];
        }

        /*
        =====================================
        3. RESPONSE
        =====================================
        */

        return response()->json([
            'success' => true,
            'current_page' => $rows->currentPage(),
            'last_page'    => $rows->lastPage(),
            'per_page'     => $rows->perPage(),
            'total'        => $rows->total(),
            'data'         => $data
        ]);
    }
}
