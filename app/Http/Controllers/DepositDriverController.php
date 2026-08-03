<?php

namespace App\Http\Controllers;

use App\Models\DepositDriver;
use App\Models\Driver;
use App\Models\PaymentBank;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepositDriverController extends Controller
{
    public function index()
    {
        $driver = auth()->user(); // lebih aman daripada Driver::find()

        $deposits = DepositDriver::where('driver_id', $driver->id)
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

    public function show($id)
    {
        $driver = auth()->user(); // lebih aman

        $deposit = DepositDriver::where('id', $id)
            ->where('driver_id', $driver->id)
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

    public function generateCodeUniqueDepositDriver()
    {
        do {
            $code = rand(100, 999);
            $exists = DepositDriver::whereDate('created_at', today())
                ->where('code', $code)
                ->exists();
        } while ($exists);

        return $code;
    }
}
