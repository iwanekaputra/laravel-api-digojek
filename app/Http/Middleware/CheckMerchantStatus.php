<?php

namespace App\Http\Middleware;

use App\Services\FonnteService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMerchantStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Cek apakah user sudah login
        if (!Auth::check()) {
            $fonnte = new FonnteService();
            $fonnte->sendMessage('0895404816031',  "not login");
            return response()->json(['message' => 'User not logged in'], 401);
        }



        // Cek apakah merchant aktif (misalnya ada kolom 'is_active' di tabel users)
        $merchant = Auth::user();

        if ($merchant->status == 'inactive') {
            return response()->json(['message' => 'merchant is inactive'], 403);
        }

        return $next($request);
    }
}
