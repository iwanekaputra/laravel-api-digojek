<?php

namespace App\Http\Controllers;

use App\Jobs\SendFcmNotification;
use App\Models\BlockedIp;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\DepositCustomer;
use App\Models\DepositDriver;
use App\Models\DepositMerchant;
use App\Models\Driver;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionCustomer;
use App\Services\FonnteService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MootaController extends Controller
{

    public function getListBank()
    {


        // 1. Simpan JSON response ke dalam variabel (Hardcoded)
        $mockResponse = [
            "current_page" => 1,
            "data" => [
                [
                    "corporate_id" => "OKE",
                    "username" => "DEDI",
                    "atas_nama" => "PT DIGOJEK",
                    "balance" => "40403076.00",
                    "account_number" => "81923891283",
                    "bank_type" => "BRI",
                    "login_retry" => 0,
                    "date_from" => "2026-03-13 00:00:00",
                    "date_to" => "2026-03-13 00:00:00",
                    "meta" => [
                        "ref_id" => null,
                        "otp" => null,
                        "otp_last_attempt" => null,
                        "first_token" => "15070033",
                        "second_token" => "24544305",
                        "place" => "dr",
                        "activity_summary" => "Terjadi Masalah teknis"
                    ],
                    "channels" => null,
                    "interval_refresh" => 2,
                    "next_queue" => null,
                    "is_active" => false,
                    "in_queue" => 0,
                    "in_progress" => 0,
                    "is_big" => 0,
                    "is_auto_start" => 0,
                    "is_able_switch_status" => 1,
                    "recurred_at" => "2026-03-14 10:18:02",
                    "status" => null,
                    "ip_address" => null,
                    "ip_address_expired_at" => null,
                    "created_at" => "2025-03-21 09:34:05",
                    "qr_url" => null,
                    "bank_id" => "asodkaos",
                    "label" => "bri",
                    "last_update" => "2026-03-13T10:00:05.000000Z",
                    "icon" => "https://app.moota.co/images/icon-bank-ibbizBri.png"
                ],
                [
                    "corporate_id" => "PK",
                    "username" => "DEDI",
                    "atas_nama" => "PT DIGOJEK",
                    "balance" => "28384198.00",
                    "account_number" => "00181231923",
                    "bank_type" => "BCA",
                    "login_retry" => 0,
                    "date_from" => "2026-03-31 00:00:00",
                    "date_to" => "2026-03-31 00:00:00",
                    "meta" => [
                        "ref_id" => null,
                        "otp" => null,
                        "otp_last_attempt" => null,
                        "place" => "dr",
                        "session_id" => "f29f5a94-3eb4-4965-961d-94aedc55fac0",
                        "activity_summary" => "Terjadi Masalah teknis"
                    ],
                    "channels" => null,
                    "interval_refresh" => 15,
                    "next_queue" => null,
                    "is_active" => false,
                    "in_queue" => 0,
                    "in_progress" => 0,
                    "is_big" => 0,
                    "is_auto_start" => 0,
                    "is_able_switch_status" => 1,
                    "recurred_at" => "2026-03-31 17:53:05",
                    "status" => null,
                    "ip_address" => null,
                    "ip_address_expired_at" => null,
                    "created_at" => "2026-02-28 14:31:16",
                    "qr_url" => null,
                    "bank_id" => "aoskd",
                    "label" => "bca",
                    "last_update" => "2026-03-31T10:54:09.000000Z",
                    https://app.moota.co/images/icon-bank-bcaGiro.png
                    "icon" => "https://app.moota.co/images/icon-bank-bcaGiro.png"
                ]
            ],
            "first_page_url" => "https://app.moota.co/api/v2/bank?page=1",
            "from" => 1,
            "last_page" => 1,
            "last_page_url" => "https://app.moota.co/api/v2/bank?page=1",
            "links" => [
                [
                    "url" => null,
                    "label" => "Sebelumnya",
                    "page" => null,
                    "active" => false
                ],
                [
                    "url" => "https://app.moota.co/api/v2/bank?page=1",
                    "label" => "1",
                    "page" => 1,
                    "active" => true
                ],
                [
                    "url" => null,
                    "label" => "Selanjutnya",
                    "page" => null,
                    "active" => false
                ]
            ],
            "next_page_url" => null,
            "path" => "https://app.moota.co/api/v2/bank",
            "per_page" => 20,
            "prev_page_url" => null,
            "to" => 2,
            "total" => 2
        ];

        // 2. Simulasi Log (Opsional, jika masih ingin memantau via log)
        Log::channel('apiexternal')->info('Hardcoded API call used', [
            'url'      => 'https://app.moota.co/api/v2/bank',
            'status'   => 200,
            'body'     => $mockResponse,
            'duration' => '0ms (Hardcoded)',
            'ip'       => request()->ip(),
        ]);

        // 3. Proses Data (Logika mapping bank_type Anda)
        $data = [];
        foreach ($mockResponse['data'] as $v) {
            $bank_type = $v['bank_type'];

            if ($bank_type == 'ibbizBri') {
                $v['bank_type'] = 'BRI';
            } elseif ($bank_type == 'bcaGiro') {
                $v['bank_type'] = 'BCA';
            }

            $data[] = $v;
        }

        // 4. Return Response
        return response()->json([
            'message' => 'success',
            'data' => $data
        ]);
    }

    public function parseIndoDate($date)
    {
        $bulan = [
            'Januari' => 'January',
            'Februari' => 'February',
            'Maret' => 'March',
            'April' => 'April',
            'Mei' => 'May',
            'Juni' => 'June',
            'Juli' => 'July',
            'Agustus' => 'August',
            'September' => 'September',
            'Oktober' => 'October',
            'November' => 'November',
            'Desember' => 'December',
        ];

        $date = str_replace(array_keys($bulan), array_values($bulan), $date);

        return Carbon::parse($date);
    }

    public function transaction(Request $request)
    {
        // $fonnte = new FonnteService();
        // $fonnte->sendMessage('0895404816031',  "moota callback");

        // log info request
        Log::channel('api')->info('API Request', [
            'ip'      => $request->ip(),
            'method'  => $request->method(),
            'url'     => $request->fullUrl(),
            'payload' => $request->all()
        ]);

        // 1️⃣ Batasi hanya IP resmi Digiflazz (cek dokumentasi Digiflazz)
        $allowedIps = [
            '103.236.201.178',
            '104.28.156.139',
            '202.10.36.138'
        ];

        if (!in_array($request->ip(), $allowedIps)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }



        // === SIMPAN LOG CALLBACK ===
        Transaction::create([
            'account_number' => 'MOOTA',
            'balance' => 0,
            'amount' => 0,
            'data_request' => json_encode($request->toArray()),
        ]);

        // Decode data transaksi
        $transactions = $request->toArray();

        if (!$transactions) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid payload'
            ], 400);
        }

        $notifications = [];

        DB::beginTransaction();
        try {
            foreach ($transactions as $r) {
                if ($r['type'] !== 'CR') {
                    continue; // hanya proses uang masuk
                }

                $amount = (int)$r['amount'];
                $code = substr($amount, -3);
                $createdAt = $this->parseIndoDate($r['date']);

                $deposit = DepositCustomer::where('grand_total', $amount)
                    ->where('code', $code)
                    ->where('expire_at', '>=', $createdAt)
                    ->where('status', 'proses')
                    ->lockForUpdate()
                    ->first();

                $type = 'customer';
                $model = Customer::class;

                if (!$deposit) {
                    $deposit = DepositDriver::where('grand_total', $amount)
                        ->where('code', $code)
                        ->where('expire_at', '>=', $createdAt)
                        ->where('status', 'proses')
                        ->lockForUpdate()
                        ->first();
                    $type = 'driver';
                    $model = Driver::class;
                }

                if (!$deposit) {
                    $deposit = DepositMerchant::where('grand_total', $amount)
                        ->where('code', $code)
                        ->where('expire_at', '>=', $createdAt)
                        ->where('status', 'proses')
                        ->lockForUpdate()
                        ->first();
                    $type = 'merchant';
                    $model = Merchant::class;
                }

                if (!$deposit) {
                    continue; // skip jika tidak cocok
                }

                // === CEGAH CALLBACK GANDA ===
                if ($deposit->status === 'lunas') {
                    if (!$deposit->is_bank_confirmed) {
                        $deposit->update(['is_bank_confirmed' => true, 'bank_confirmed_at' => now()]);
                    }
                    continue; // sudah diproses sebelumnya
                };

                $user = $model::find($deposit->{$type . '_id'});
                $beforeBalance = $user->balance;

                if ($user) {
                    $user->increment('balance', $deposit->total_price);
                }

                if ($type == 'customer') {
                    TransactionCustomer::create([
                        'customer_id' => $deposit->customer_id,
                        'price' => $deposit->total_price,
                        'mode' => 'masuk',
                        'type' => 'Deposit',
                        'payment_method' => 'Saldo',
                        'balance' => $beforeBalance + $deposit->total_price,
                    ]);
                }


                $deposit->update([
                    'status' => 'lunas',
                    'paid_at' => now(),
                    'verified_by' => 'moota',
                    'has_unique_code' => true,
                    'is_bank_confirmed' => true,
                    'bank_confirmed_at' => now(),
                ]);

                $notifications[] = [
                    'token' => $user->device_token ?? null,
                    'title' => 'Top Up Berhasil',
                    'body' => 'Saldo Rp ' . number_format($deposit->total_price, 0, ',', '.') . ' telah masuk ke akun Anda.',
                ];
            }

            DB::commit();

            if (!empty($notifications)) {
                SendFcmNotification::dispatch($notifications);
            }

            return response()->json([
                'status' => true,
                'message' => 'Callback processed securely',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Moota callback error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Internal error',
            ], 500);
        }
    }
}
