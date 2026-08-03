<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderChatController extends Controller
{
    /**
     * Tampilkan semua pesan berdasarkan Order ID
     */
    public function index($order_id)
    {
        $order = Order::find($order_id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan.'
            ], 404);
        }

        // Mengambil chat dan urutkan dari yang terlama ke terbaru
        $chats = $order->chats;

        return response()->json([
            'success' => true,
            'message' => 'Riwayat chat berhasil diambil.',
            'data' => $chats
        ], 200);
    }

    /**
     * Kirim pesan baru
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'order_id'    => 'required|exists:orders,id',
            'sender_type' => 'required|in:customer,driver',
            'sender_id'   => 'required',
            'message'     => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 1. Simpan chat ke database
        $chat = OrderChat::create([
            'order_id'    => $request->order_id,
            'sender_type' => $request->sender_type,
            'sender_id'   => $request->sender_id,
            'message'     => $request->message,
            'is_read'     => 0
        ]);

        // 2. Logika Push Notification memanfaatkan device_token yang Anda miliki
        // $this->sendPushNotification($chat);

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dikirim.',
            'data' => $chat
        ], 201);
    }

    /**
     * Helper untuk mencari token tujuan dan memicu push notification
     */
    private function sendPushNotification($chat)
    {
        // Ambil data order beserta relasi kustomer / drivernya
        $order = Order::with(['drivers'])->find($chat->order_id);
        if (!$order) return;

        // Ambil data relasi customer (karena order_customers terpisah di tabel Anda)
        // Kita asumsikan Anda memiliki relasi customer di model Order melalui order_customers
        $targetToken = null;

        if ($chat->sender_type === 'customer') {
            // Jika pengirimnya customer, maka target penerimanya adalah Driver
            $targetToken = $order->drivers?->device_token;
        } else {
            // Jika pengirimnya driver, maka target penerimanya adalah Customer
            // Query manual ke order_customers untuk mendapatkan customer_id lalu ambil device_token-nya
            $orderCustomer = DB::table('order_customers')
                ->join('customers', 'order_customers.customer_id', '=', 'customers.id')
                ->where('order_customers.order_id', $chat->order_id)
                ->select('customers.device_token')
                ->first();

            $targetToken = $orderCustomer?->device_token;
        }

        if ($targetToken) {
            // TODO: Integrasikan dengan service FCM / Firebase Anda di sini
            // Contoh: FcmService::send($targetToken, 'Pesan Baru', $chat->message);
        }
    }
}
