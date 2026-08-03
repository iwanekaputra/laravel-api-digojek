<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\DriverOrderRequest;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderCustomer;
use App\Models\ReviewDriver;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function trackOrder(Request $request)
    {


        $customer = Customer::find(auth()->user()->id);


        /*
        =====================================
        1. AMBIL ORDER AKTIF CUSTOMER
        =====================================
        */

        $order = Order::with([
            'driver',
            'orderCustomer'
        ])
            ->whereHas('orderCustomer', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->whereIn('status', [
                'searching_driver',
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

        $customer = $order->orderCustomer;
        $driver   = $order->driver;

        /*
        =====================================
        2. RESPONSE TRACKING
        =====================================
        */

        return response()->json([
            'success' => true,
            'data' => [

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

                /*
                DRIVER
                */

                'driver' => $driver ? [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'phone' => $driver->nohp,
                    'image' => $driver->image,
                    'latitude' =>
                    $order->latitude_current_driver
                        ?? $driver->latitude,

                    'longitude' =>
                    $order->longitude_current_driver
                        ?? $driver->longitude
                ] : null,

                /*
                PAYMENT
                */

                'payment_method' =>
                $customer->payment_method,

                'status_payment' =>
                $customer->status_payment
            ]
        ]);
    }

    public function orderDetail($id)
    {
        $customer = Customer::find(auth()->user()->id);

        /*
    =====================================
    1. AMBIL ORDER BERDASARKAN ID
    =====================================
    */

        $order = Order::with([
            'driver',
            'orderCustomers',
            'shipCargo' // Tambahkan relasi kargo Ship di eager load
        ])
            ->whereHas('orderCustomers', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->where('id', $id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan'
            ]);
        }

        $orderCustomer = $order->orderCustomers->first();
        $driver   = $order->driver;

        /*
    =====================================
    2. RESPONSE (SAMA DENGAN TRACK ORDER)
    =====================================
    */

        $responseData = [
            'order_id'   => $order->id,
            'no_order'   => $order->no_order,
            'status'     => $order->status,
            'type_order' => $order->type_order,
            'price'      => $order->grand_total,

            /*
        PICKUP & DESTINATION
        */
            'pickup_address'      => $orderCustomer->address_current_customer,
            'destination_address' => $orderCustomer->address_destination,
            'pickup_lat'          => $orderCustomer->latitude_current_customer,
            'pickup_lng'          => $orderCustomer->longitude_current_customer,
            'destination_lat'     => $orderCustomer->latitude_destination,
            'destination_lng'     => $orderCustomer->longitude_destination,

            /*
        DRIVER / PORTER
        */
            'driver' => $driver ? [
                'id'                  => $driver->id,
                'name'                => $driver->name,
                'phone'               => $driver->nohp,
                'image'               => $driver->image,
                'latitude'            => $order->latitude_current_driver ?? $driver->latitude,
                'longitude'           => $order->longitude_current_driver ?? $driver->longitude,
                'brand'               => $driver->vehicle->brand ?? '-',
                'registration_number' => $driver->vehicle->registration_number ?? '-',
                'link_image'          => $driver->link_image ?? null
            ] : null,

            /*
        PAYMENT
        */
            'payment_method' => $orderCustomer->payment_method,
            'status_payment' => $orderCustomer->status_payment,
            'created_at'     => $order->created_at,

            /*
        SHIP CARGO DETAILS (DEFAULT NULL UNTUK OJEK/MOBIL)
        */
            'ship_cargo' => null
        ];

        /*
    =====================================
    3. KONDISIONAL KHUSUS SHIP PORTER
    =====================================
    Jika tipenya adalah SHIP_PORTER dan data kargonya tersedia, masukkan detailnya
    */
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

        return response()->json([
            'success' => true,
            'data'    => $responseData
        ]);
    }


    public function cancelOrder(Request $request)
    {
        $request->validate([
            'order_id'    => 'required',
            'reason'      => 'nullable|string|max:255'
        ]);

        $customer = Customer::find(auth()->user()->id);


        DB::beginTransaction();

        try {

            /*
            =====================================
            1. VALIDASI ORDER MILIK CUSTOMER
            =====================================
            */

            $order = Order::where('id', $request->order_id)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            if (
                !$order->orderCustomers->first() ||
                $order->orderCustomers->first()->customer_id != $customer->id
            ) {

                return response()->json([
                    'success' => false,
                    'message' => 'Order bukan milik customer'
                ], 403);
            }

            /*
            =====================================
            2. STATUS YANG BOLEH DICANCEL
            =====================================
            */

            $allowed = [
                'searching_driver',
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
        2.5 REFUND SALDO CUSTOMER (JIKA PAKAI SALDO)
        =====================================
        */
            if (strtolower($order->orderCustomers->first()->payment_method) === 'saldo') {
                // Kembalikan saldo customer sebesar grand_total order tersebut
                $customer->increment('balance', $order->orderCustomers->first()->grand_total);
            }

            /*
            =====================================
            3. UPDATE ORDER
            =====================================
            */

            $order->update([
                'status' => 'cancelled'
            ]);

            /*
            =====================================
            4. UPDATE ORDER_CUSTOMERS
            =====================================
            */

            OrderCustomer::where('order_id', $order->id)
                ->update([
                    'status' => 'cancelled'
                ]);

            /*
            =====================================
            5. JIKA SUDAH ADA DRIVER
            =====================================
            */

            if ($order->driver_id) {

                Driver::where('id', $order->driver_id)
                    ->update([
                        'is_delivering' => 0
                    ]);
            }

            /*
            =====================================
            6. UPDATE REQUEST DRIVER
            =====================================
            */

            DriverOrderRequest::where('order_id', $order->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->update([
                    'status' => 'cancelled',
                    'responded_at' => Carbon::now()
                ]);

            /*
            =====================================
            7. OPTIONAL: SIMPAN ALASAN CANCEL
            (kalau nanti ada kolom cancel_reason)
            =====================================
            */

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibatalkan',
                'data' => [
                    'order_id' => $order->id,
                    'status'   => 'cancelled',
                    'reason'   => $request->reason
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

    public function rateOrder(Request $request)
    {
        $request->validate([
            'order_id'     => 'required',
            'rating'       => 'required|numeric|min:1|max:5',
            'description'  => 'nullable|string|max:1000'
        ]);

        $customer = Customer::find(auth()->user()->id);


        DB::beginTransaction();

        try {

            /*
            =====================================
            1. VALIDASI ORDER MILIK CUSTOMER
            =====================================
            */

            $order = Order::with('orderCustomer')
                ->where('id', $request->order_id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order tidak ditemukan'
                ], 404);
            }

            if (
                !$order->orderCustomer ||
                $order->orderCustomer->customer_id != $customer->id
            ) {

                return response()->json([
                    'success' => false,
                    'message' => 'Order bukan milik customer'
                ], 403);
            }

            /*
            =====================================
            2. HANYA ORDER SELESAI BISA RATING
            =====================================
            */

            if ($order->status != 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order belum selesai'
                ], 400);
            }

            if (!$order->driver_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver tidak ditemukan'
                ], 400);
            }

            /*
            =====================================
            3. CEK SUDAH REVIEW / BELUM
            =====================================
            */

            $check = ReviewDriver::where('order_id', $order->id)
                ->where('customer_id', $customer->id)
                ->first();

            if ($check) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order ini sudah diberi rating'
                ], 400);
            }

            /*
            =====================================
            4. SIMPAN REVIEW
            =====================================
            */

            $review = ReviewDriver::create([
                'order_id' => $order->id,
                'driver_id' => $order->driver_id,
                'customer_id' => $customer->id,
                'rating' => $request->rating,
                'description' => $request->description ?? ''
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Terima kasih atas penilaian Anda',
                'data' => [
                    'review_id' => $review->id,
                    'order_id' => $order->id,
                    'rating' => $review->rating
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


        $customer = Customer::find(auth()->user()->id);


        /*
        =====================================
        1. AMBIL HISTORY ORDER CUSTOMER
        =====================================
        */

        $rows = Order::whereHas('orderCustomers', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id);
        })

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
            $driver   = $row->driver;

            $data[] = [
                'order_id'   => $row->id,
                'no_order'   => $row->no_order,
                'status'     => $row->status,
                'type_order' => $row->type_order,
                'price'      => $row->grand_total,

                'pickup_address' =>
                $customer->address_current_customer ?? '-',

                'destination_address' =>
                $customer->address_destination ?? "-",

                'distance' =>
                $customer->distance ?? 0,

                'payment_method' =>
                $customer->payment_method ?? '-',

                'created_at' =>
                $row->created_at,

                'driver' => $driver ? [
                    'id'    => $driver->id,
                    'name'  => $driver->name,
                    'phone' => $driver->nohp,
                    'image' => $driver->image
                ] : null
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
