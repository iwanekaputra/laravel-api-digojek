<?php

namespace App\Http\Controllers;

use App\Http\Resources\VehicleTypeResource;
use App\Jobs\SendFcmNotification;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Merchant;
use App\Models\Price;
use App\Models\Product;
use App\Models\Slider;
use App\Models\TransactionPurchaseDriver;
use App\Models\TransactionPurchaseMerchant;
use App\Models\TransactionPurchaseSeller;
use App\Models\Vehicletype;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function PHPSTORM_META\map;

class SharedController extends Controller
{

    public function testFcm(Request $request)
    {
        // send notif kirim sesama

        $notifications = [
            [
                'token' => $request->device_token,
                'title' => 'test.',
                'body' => ''
            ]
        ];

        SendFcmNotification::dispatch($notifications);
    }

    public function getSliders()
    {
        $sliders = Cache::remember('sliders', '120', function () {
            return Slider::orderBy('sort')->get();
        });




        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => $sliders
        ]);
    }

    public function priceByCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $price = Price::where('city', $request->city)->first();

        if ($price) {
            return response()->json([
                'status' => true,
                'message' => 'success get data city by name city',
                'data' => $price
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'there no data city',
        ], 404);
    }

    public function priceByVehicle(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'vehicletype' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $vehicletype = Vehicletype::where('vehicletype', $request->vehicletype)->first();
        if ($vehicletype) {
            return response()->json([
                'status' => true,
                'message' => 'success get data vehicletype by name vehicletype',
                'data' => new VehicleTypeResource($vehicletype)
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'there no data vehicletype',
        ], 404);
    }


    public function deposit(Request $request)
    {
        $angkaRandom = rand(100, 999);
        $now = now();
        $grandTotal = $request->total_price + $angkaRandom;

        $deposit = Deposit::create([
            'user_id' => $request->user_id,
            'code' => substr($grandTotal, -3),
            'name_owner_bank' => $request->name_owner_bank,
            'account_number' => $request->account_number,
            'bank_type' => $request->bank_type,
            'total_price' => $request->total_price,
            'grand_total' => $grandTotal,
            'bank_id' => $request->bank_id,
            'status' => 'proses',
            'expire_at' => $now->addMinutes(30)
        ]);

        if ($deposit) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $deposit
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'success'
        ]);
    }


    public function listMerchants(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'menu' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;

        $now = Carbon::now()->format('H:i:s');

        $query = Merchant::query()
            ->with(['categoryMerchant'])
            ->select(
                'merchants.*',
                DB::raw("
                (
                    6371 * acos(
                        cos(radians($latitude))
                        * cos(radians(latitude))
                        * cos(radians(longitude) - radians($longitude))
                        + sin(radians($latitude))
                        * sin(radians(latitude))
                    )
                ) AS distance
            "),
                DB::raw("
    CASE
        WHEN TIME('$now') BETWEEN opening_hour AND closing_hour
        THEN 1
        ELSE 0
    END AS is_open
")
            );

        // filter city
        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        // filter menu
        if ($request->filled('menu')) {
            $query->whereHas('categoryMerchant', function ($q) use ($request) {
                $q->where('menu', $request->menu);
            });
        }

        // merchant aktif
        $query->where('status', 'active');

        // urutkan terdekat
        $query->orderBy('distance', 'asc');

        $merchants = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'List merchant berhasil diambil',
            'data' => $merchants
        ]);
    }


    public function storeCarts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required|integer|exists:merchants,id',
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1|max:1000',
            'type' => 'nullable|in:produk,grosir',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }

        $customer = Customer::find($user->id);
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer not found'], 404);
        }

        $product = Product::find($request->product_id);
        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Product not found'], 404);
        }

        // Pastikan merchant benar
        if ($product->merchant_id != $request->merchant_id) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid merchant-product relation'
            ], 422);
        }

        // Cek apakah sudah ada di cart
        $cart = Cart::where('customer_id', $customer->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($request->type === 'grosir') {
            $price = $product->price_box;
            $weight = $product->weight_box;
        } else {
            $price = $product->price;
            $weight = $product->weight;
        }

        if ($cart) {
            $cart->update([
                'quantity' => $request->quantity,
                'total_price' => $price * $request->quantity,
                'total_weight' => $weight * $request->quantity,
                'type' => $request->type ?? 'produk',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Cart updated successfully',
                'data' => $cart
            ]);
        }

        $createCart = Cart::create([
            'customer_id' => $customer->id,
            'merchant_id' => $request->merchant_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'total_price' => $price * $request->quantity,
            'total_weight' => $weight * $request->quantity,
            'type' => $request->type ?? 'produk'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cart added successfully',
            'data' => $createCart
        ]);
    }



    public function carts(Request $request)
    {

        // Validasi input
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'nullable|integer|exists:merchants,id',
            'type' => 'nullable|string|max:50',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }


        // Pastikan user autentikasi
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'code' => 401,
                'message' => 'Unauthorized'
            ], 401);
        }

        $customer = Customer::find(auth()->user()->id);
        $carts = Cart::when($customer->id != null, function ($query) use ($request, $customer) {
            $query->where("customer_id", $customer->id);
        })->when($request->merchant_id != null, function ($query) use ($request) {
            $query->where("merchant_id", $request->merchant_id);
        })->when($request->type != null, function ($query) use ($request) {
            $query->where("type", $request->type);
        })->get()->groupBy('merchant.name');

        if ($carts->count()) {
            return response()->json([
                'status' => true,
                'message' => 'success get cart',
                'data' => $carts
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'There no cart',
            'data' => $carts
        ], 404);
    }


    // Controller
    public function destroyCarts(Cart $cart)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // pastikan cart milik user yg login
        if ($cart->customer_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => 'Forbidden — You cannot delete this cart'
            ], 403);
        }

        // optional: kalau model pakai SoftDeletes, cukup panggil delete()
        $cart->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cart deleted successfully'
        ], 200);
    }











    public function getTransactionPurchasesByDriverId(Request $request)
    {
        $driver = auth()->user();
        $transactionPurchases = TransactionPurchaseDriver::where("driver_id", $driver->id)->whereNot('payment_method', 'CEK')->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'data' => $transactionPurchases
        ]);
    }
    public function getTransactionPurchasesByMerchantId(Request $request)
    {
        $merchant = auth()->user();
        $transactionPurchases = TransactionPurchaseMerchant::where("merchant_id", $merchant->id)->whereNot('payment_method', 'CEK')->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'data' => $transactionPurchases
        ]);
    }

    public function getTransactionPurchasesBySellerId(Request $request)
    {
        $transactionPurchases = TransactionPurchaseSeller::where("seller_id", $request->seller_id)->whereNot('payment_method', 'CEK')->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => 200,
            'data' => $transactionPurchases
        ]);
    }


    public function customerRegisterPin(Request $request)
    {
        $customer = Customer::find(auth()->user()->id);

        if ($customer) {
            $customer->update([
                'pin' => $request->pin
            ]);

            return response()->json([
                'status' => 200,
                'data' => $customer
            ]);
        }

        return response()->json([
            'status' => 400,
            'message' => 'error'
        ]);
    }
}
