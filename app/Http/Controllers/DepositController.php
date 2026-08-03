<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DepositCustomer;
use App\Models\DepositDriver;
use App\Models\DepositMerchant;
use App\Models\Driver;
use App\Models\PaymentBank;
use App\Models\PaymentMethod;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class DepositController extends Controller
{

    public function index()
    {
        $customer = Customer::find(auth()->user()->id);

        $deposits = DepositCustomer::where('customer_id', $customer->id)
            ->with([
                'paymentMethod:id,name,type,icon',
                'paymentBank:id,bank_name,account_number,account_name'
            ])
            ->orderByDesc('created_at')
            ->get();

        $data = $deposits->map(function ($deposit) {

            $paymentMethod = $deposit->paymentMethod;
            $type = $paymentMethod?->type ?? 'manual';

            return [
                'id' => $deposit->id,
                'type' => $type,

                'payment_method' => $paymentMethod
                    ? [
                        'id' => $paymentMethod->id,
                        'name' => $paymentMethod->name,
                        'icon_url' => $paymentMethod->icon
                            ? asset('storage/images/slider/' . $paymentMethod->icon)
                            : null,
                    ]
                    : [
                        'id' => null,
                        'name' => 'Transfer Manual',
                        'icon_url' => null,
                    ],

                // ===== VA DATA =====
                'va' => $type === 'va'
                    ? [
                        'company_code' => $deposit->va_company_code,
                        'va_number' => $deposit->va_number,
                    ]
                    : null,

                // ===== MANUAL BANK =====
                'payment_bank' => $type === 'manual'
                    ? (
                        $deposit->paymentBank
                        ? [
                            'bank_name' => $deposit->paymentBank->bank_name,
                            'account_number' => $deposit->paymentBank->account_number,
                            'account_name' => $deposit->paymentBank->account_name,
                        ]
                        : [
                            'bank_name' => $deposit->bank_type,
                            'account_number' => $deposit->account_number,
                            'account_name' => $deposit->name_owner_bank,
                        ]
                    )
                    : null,

                'total_price' => (int) $deposit->total_price,
                'grand_total' => (int) $deposit->grand_total,
                'status' => $deposit->status,
                'expired_at' => optional($deposit->expire_at),
                'created_at' => $deposit->created_at,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $data,
        ]);
    }


    public function show($id)
    {
        $customer = Customer::find(auth()->user()->id);

        $deposit = DepositCustomer::where('id', $id)
            ->where('customer_id', $customer->id)
            ->with([
                'paymentMethod:id,name,type,icon,description',
                'paymentBank:id,bank_name,account_number,account_name'
            ])
            ->firstOrFail();

        $paymentMethod = $deposit->paymentMethod;
        $type = $paymentMethod?->type ?? 'manual';

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'id' => $deposit->id,
                'type' => $type,

                'payment_method' => $paymentMethod
                    ? [
                        'id' => $paymentMethod->id,
                        'name' => $paymentMethod->name,
                        'icon_url' => $paymentMethod->icon
                            ? asset('storage/images/slider/' . $paymentMethod->icon)
                            : null,
                    ]
                    : [
                        'id' => null,
                        'name' => 'Transfer Manual',
                        'icon_url' => null,
                    ],

                // ===== VA =====
                'va' => $type === 'va'
                    ? [
                        'company_code' => $deposit->va_company_code,
                        'va_number' => $deposit->va_company_code . $deposit->va_number,
                    ]
                    : null,

                // ===== MANUAL =====
                'payment_bank' => $type === 'manual'
                    ? (
                        $deposit->paymentBank
                        ? [
                            'bank_name' => $deposit->paymentBank->bank_name,
                            'account_number' => $deposit->paymentBank->account_number,
                            'account_name' => $deposit->paymentBank->account_name,
                        ]
                        : [
                            'bank_name' => $deposit->bank_type,
                            'account_number' => $deposit->account_number,
                            'account_name' => $deposit->name_owner_bank,
                        ]
                    )
                    : null,

                'total_price' => (int) $deposit->total_price,
                'grand_total' => (int) $deposit->grand_total,
                'status' => $deposit->status,
                'expired_at' => optional($deposit->expire_at),

                'description' => $paymentMethod?->description ?? null,
            ]
        ]);
    }



    public function getDepositCustomers()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $customer = Customer::find(auth()->user()->id);
        $deposits = DepositCustomer::where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get(); // gunakan pagination

        return response()->json([
            'status' => true,
            'message' => 'Success get deposit',
            'data' => $deposits
        ]);
    }

    public function getDepositDrivers(Request $request)
    {
        $deposits = DepositDriver::when($request->driver_id != null, function ($query) use ($request) {
            $query->where("driver_id", $request->driver_id);
        })->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => true,
            'message' => 'Success get deposit',
            'data' => $deposits
        ]);
    }

    public function getDepositMerchants(Request $request)
    {
        $merchant = auth()->user();
        $deposits = DepositMerchant::when($merchant->id != null, function ($query) use ($merchant) {
            $query->where("merchant_id", $merchant->id);
        })->orderBy('created_at', 'DESC')->get();

        return response()->json([
            'status' => true,
            'message' => 'Success get deposit',
            'data' => $deposits
        ]);
    }


    public function createDeposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_bank_id'   => 'nullable|exists:payment_banks,id',
            'total_price'       => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // $customer = auth()->user();
        $customer = Customer::find(auth()->user()->id);
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
        $now = now();

        // ================= MANUAL =================
        if ($paymentMethod->type === 'manual') {

            if (!$request->payment_bank_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment bank wajib dipilih'
                ], 422);
            }

            $paymentBank = PaymentBank::where('id', $request->payment_bank_id)
                ->where('payment_method_id', $paymentMethod->id)
                ->where('is_active', true)
                ->firstOrFail();

            $codesToday = DepositCustomer::whereDate('created_at', today())
                ->pluck('code');

            $kodeUnik = $this->generateCodeUniqueDepositCustomer($codesToday);
            $grandTotal = $request->total_price + $kodeUnik;

            $deposit = DepositCustomer::create([
                'customer_id' => $customer->id,
                'payment_method_id' => $paymentMethod->id,
                'payment_bank_id' => $paymentBank->id,
                'bank_id' => 1,
                // legacy fields
                'code' => $kodeUnik,
                'name_owner_bank' => $paymentBank->account_name,
                'account_number' => $paymentBank->account_number,
                'bank_type' => $paymentBank->bank_name,

                'total_price' => $request->total_price,
                'grand_total' => $grandTotal,
                'status' => 'proses',
                'expire_at' => $now->addMinutes(60),
            ]);
        }

        // ================= VA =================
        if ($paymentMethod->type === 'va') {

            /**
             * VA NUMBER STRATEGY
             * contoh:
             * CompanyCode + customer_id + timestamp / sequence
             */
            $vaNumber = $paymentMethod->sub_code
                . str_pad($customer->id, 6, '0', STR_PAD_LEFT)
                . rand(100, 999);

            $deposit = DepositCustomer::create([
                'customer_id' => $customer->id,
                'payment_method_id' => $paymentMethod->id,
                'code' => $paymentMethod->price_admin,
                'name_owner_bank' => '-',
                'account_number' => 000,
                'bank_type' => '-',
                'bank_id' => 0,
                // === VA FIELDS (KRUSIAL) ===
                'va_company_code' => $paymentMethod->company_code,
                'va_number' => $vaNumber,

                'total_price' => $request->total_price,
                'grand_total' => $request->total_price + $paymentMethod->price_admin,

                // === HARUS proses (BIAR BISA DI-INQUIRY) ===
                'status' => 'proses',

                'expire_at' => now()->addMinutes(30),
            ]);
        }


        return response()->json([
            'status' => true,
            'message' => 'Deposit berhasil dibuat',
            'data' => [
                'deposit_id' => $deposit->id,
                'payment_type' => $paymentMethod->type,
                'total_price' => $deposit->total_price,
                'grand_total' => $deposit->grand_total,
                'expired_at' => $deposit->expire_at,
            ]
        ]);
    }

    public function createDepositDriver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_bank_id'   => 'nullable|exists:payment_banks,id',
            'total_price'       => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $driver = Driver::find(auth()->user()->id);
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);
        $now = now();

        // ================= MANUAL =================
        if ($paymentMethod->type === 'manual') {

            if (!$request->payment_bank_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment bank wajib dipilih'
                ], 422);
            }

            $paymentBank = PaymentBank::where('id', $request->payment_bank_id)
                ->where('payment_method_id', $paymentMethod->id)
                ->where('is_active', true)
                ->firstOrFail();

            $codesToday = DepositDriver::whereDate('created_at', today())
                ->pluck('code');

            $kodeUnik = $this->generateCodeUniqueDepositDriver($codesToday);
            $grandTotal = $request->total_price + $kodeUnik;

            $deposit = DepositDriver::create([
                'driver_id' => $driver->id,
                'payment_method_id' => $paymentMethod->id,
                'payment_bank_id' => $paymentBank->id,
                'bank_id' => 1,

                // legacy fields
                'code' => $kodeUnik,
                'name_owner_bank' => $paymentBank->account_name,
                'account_number' => $paymentBank->account_number,
                'bank_type' => $paymentBank->bank_name,

                'total_price' => $request->total_price,
                'grand_total' => $grandTotal,
                'status' => 'proses',
                'expire_at' => $now->addMinutes(60),
            ]);
        }

        // ================= VA =================
        if ($paymentMethod->type === 'va') {

            $vaNumber = $paymentMethod->sub_code
                . str_pad($driver->id, 6, '0', STR_PAD_LEFT)
                . rand(100, 999);

            $deposit = DepositDriver::create([
                'driver_id' => $driver->id,
                'payment_method_id' => $paymentMethod->id,

                'code' => $paymentMethod->price_admin,
                'name_owner_bank' => '-',
                'account_number' => 000,
                'bank_type' => '-',
                'bank_id' => 0,

                // VA fields
                'va_company_code' => $paymentMethod->company_code,
                'va_number' => $vaNumber,

                'total_price' => $request->total_price,
                'grand_total' => $request->total_price + $paymentMethod->price_admin,

                'status' => 'proses',
                'expire_at' => now()->addMinutes(30),
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Deposit driver berhasil dibuat',
            'data' => [
                'deposit_id' => $deposit->id,
                'payment_type' => $paymentMethod->type,
                'total_price' => $deposit->total_price,
                'grand_total' => $deposit->grand_total,
                'expired_at' => $deposit->expire_at,
            ]
        ]);
    }


    public function customersDeposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_owner_bank' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'bank_type' => 'required|string|max:255',
            'total_price' => 'required|numeric|min:1',
            'bank_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {

            $response = [
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ];

            return response()->json($response, 422);
        }

        $customer = Customer::find(auth()->user()->id);

        $depositCustomers = DepositCustomer::whereDate('created_at', Carbon::today())->get()->pluck("code");



        $angkaRandom = $this->generateCodeUniqueDepositCustomer($depositCustomers);
        $now = now();
        $grandTotal = $request->total_price + $angkaRandom;

        $deposit = DepositCustomer::create([
            'customer_id' => $customer->id,
            'code' => (int)substr($grandTotal, -3),
            'name_owner_bank' => $request->name_owner_bank,
            'account_number' => $request->account_number,
            'bank_type' => $request->bank_type,
            'total_price' => (int)$request->total_price,
            'grand_total' => $grandTotal,
            'bank_id' => $request->bank_id,
            'status' => 'proses',
            'expire_at' => $now->addMinutes(60)
        ]);

        $deposit['image_transfer'] = '';

        if ($request->bank_type == 'BCA') {
            $fonnte = new FonnteService();
            $fonnte->sendMessage('0895404816031',  "Deposit baru dengan total harga: Rp " . number_format($request->total_price, 0, ',', '.') . " dan kode unik: " . substr($grandTotal, -3) . ". Segera lakukan pengecekan.");
        }

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

    public function generateCodeUniqueDepositCustomer($depositCustomers)
    {
        do {
            $code = rand(100, 999);
        } while ($depositCustomers->contains($code));

        return $code;
    }

    public function generateCodeUniqueDepositDriver($depositDrivers)
    {
        do {
            # code...
            $angkaRandom = rand(100, 999);

            foreach ($depositDrivers as $depositDriver) {
                if ($depositDriver == $angkaRandom) {
                    $exists = true;
                } else {
                    $exists = false;
                }
            }

            $exists = false;
        } while ($exists);

        return $angkaRandom;
    }

    public function generateCodeUniqueDepositMerchant($depositMerchants)
    {
        do {
            # code...
            $angkaRandom = rand(100, 999);

            foreach ($depositMerchants as $depositMerchant) {
                if ($depositMerchant == $angkaRandom) {
                    $exists = true;
                } else {
                    $exists = false;
                }
            }

            $exists = false;
        } while ($exists);

        return $angkaRandom;
    }

    public function driversDeposit(Request $request)
    {
        $depositDrivers = DepositDriver::whereDate('created_at', Carbon::today())->get()->pluck("code");

        $angkaRandom = $this->generateCodeUniqueDepositDriver($depositDrivers);
        $now = now();
        $grandTotal = $request->total_price + $angkaRandom;

        $deposit = DepositDriver::create([
            'driver_id' => $request->driver_id,
            'code' => substr($grandTotal, -3),
            'name_owner_bank' => $request->name_owner_bank,
            'account_number' => $request->account_number,
            'bank_type' => $request->bank_type,
            'total_price' => $request->total_price,
            'grand_total' => $grandTotal,
            'bank_id' => $request->bank_id,
            'status' => 'proses',
            'expire_at' => $now->addMinutes(60)
        ]);

        $deposit['image_transfer'] = '';


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

    public function merchantsDeposit(Request $request)
    {

        $depositMerchants = DepositMerchant::whereDate('created_at', Carbon::today())->get()->pluck("code");


        $angkaRandom = $this->generateCodeUniqueDepositMerchant($depositMerchants);
        $now = now();
        $grandTotal = $request->total_price + $angkaRandom;

        $deposit = DepositMerchant::create([
            'merchant_id' => $request->merchant_id,
            'code' => substr($grandTotal, -3),
            'name_owner_bank' => $request->name_owner_bank,
            'account_number' => $request->account_number,
            'bank_type' => $request->bank_type,
            'total_price' => $request->total_price,
            'grand_total' => $grandTotal,
            'bank_id' => $request->bank_id,
            'status' => 'proses',
            'expire_at' => $now->addMinutes(60)
        ]);

        $deposit['image_transfer'] = '';

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


    public function updateDepositCustomer(DepositCustomer $depositCustomer, Request $request)
    {
        if ($depositCustomer->customer_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        if ($depositCustomer->status !== 'proses') {
            return response()->json([
                'status' => false,
                'message' => 'Deposit cannot be updated after processing.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'image' => ['nullable', 'string', 'regex:/^data:image\/(png|jpeg|jpg);base64,/', 'max:5000'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->image) {
            $image = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
            $imageName = 'deposit_' . Str::random(10) . '.png';
            Storage::disk('public')->put('deposit_images/' . $imageName, base64_decode($image));
            $depositCustomer->image_transfer = 'deposit_images/' . $imageName;
        }

        $depositCustomer->save();

        return response()->json([
            'status' => 200,
            'message' => 'Success update deposit customer',
            'data' => $depositCustomer
        ]);
    }


    public function updateDepositDriver(DepositDriver $depositDriver, Request $request)
    {

        if ($request->image) {
            $image = $request->image;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(10) . '.' . 'png';
            File::put(storage_path() . '/app/public' . '/' . $imageName, base64_decode($image));
        }

        $depositDriver->update([
            'image_transfer' => $request->image ? $imageName : $depositDriver->image_transfer
        ]);

        // $depositDriver['image_transfer'] = asset("storage/" . $depositDriver->image_transfer);


        return response()->json([
            'status' => 200,
            'message' => 'Success update deposit driver',
            'data' => $depositDriver
        ]);
    }

    public function updateDepositMerchant(DepositMerchant $depositMerchant, Request $request)
    {

        if ($request->image) {
            $image = $request->image;  // your base64 encoded
            $image = str_replace('data:image/png;base64,', '', $image);
            $image = str_replace(' ', '+', $image);
            $imageName = Str::random(10) . '.' . 'png';
            File::put(storage_path() . '/app/public' . '/' . $imageName, base64_decode($image));
        }

        $depositMerchant->update([
            'image_transfer' => $request->image ? $imageName : $depositMerchant->image_transfer
        ]);

        // $depositMerchant['image_transfer'] = asset("storage/" . $depositMerchant->image_transfer);


        return response()->json([
            'status' => 200,
            'message' => 'Success update deposit merchant',
            'data' => $depositMerchant
        ]);
    }
}
