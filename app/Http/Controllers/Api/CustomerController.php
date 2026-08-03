<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{

    public function inquiryCustomerCekUser(Request $request)
    {

        try {

            /* ===============================
             * 1. Ambil User Login
             * =============================== */
            $authUser = $request->user();

            /* ===============================
             * 2. Validasi Input
             * =============================== */
            $request->validate([
                'type_user' => 'required|in:customer,driver,merchant',
                'nohp'      => 'required|string'
            ]);

            $typeUser = $request->type_user;
            $nohp     = $request->nohp;

            /* ===============================
             * 3. Cek User Sendiri
             * =============================== */

            $authType = class_basename(get_class($authUser));
            // Customer / Driver / Merchant

            if (
                strtolower($authType) === $typeUser &&
                $authUser->nohp === $nohp
            ) {
                return response()->json([
                    'error' => 'tidak boleh dengan user yang sama'
                ], 400);
            }

            /* ===============================
             * 4. Cari Target User
             * =============================== */

            switch ($typeUser) {

                case 'customer':
                    $user = DB::table('customers')
                        ->where('nohp', $nohp)
                        ->first();
                    break;

                case 'driver':
                    $user = DB::table('drivers')
                        ->where('nohp', $nohp)
                        ->first();
                    break;

                case 'merchant':
                    $user = DB::table('merchants')
                        ->where('nohp', $nohp)
                        ->first();
                    break;

                default:
                    $user = null;
            }

            if (!$user) {
                return response()->json([
                    'message' => $typeUser . ' tidak ditemukan'
                ], 400);
            }

            /* ===============================
             * 5. Return Success
             * =============================== */

            $user->type_user = $typeUser;

            return response()->json([
                'status'  => 200,
                'message' => 'success',
                'data'    => $user
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'error' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'internal server error'
            ], 500);
        }
    }

    public function getUserByDeviceToken(Request $request)
    {
        // Ambil user dari token
        $user = $request->user();

        // Kalau belum login
        if (!$user) {
            return response()->json([
                'error' => 'Unauthenticated'
            ], 401);
        }

        // Set link image (kalau perlu)
        if (!empty($user->image)) {
            $user->link_image =
                env('LINK_URL_IMAGE') . $user->image;
        } else {
            $user->link_image = '';
        }

        $user->pin = (int) $user->pin;

        return response()->json([
            'status'  => 200,
            'message' => 'success',
            'data'    => $user
        ]);
    }

    public function customers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'code' => 422,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = Customer::find($request->id);

        if (!$customer) {
            return response()->json([
                'status' => false,
                'code' => 404,
                'message' => 'There is no customer',
            ], 404);
        }

        if ($customer->id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'code' => 403,
                'message' => 'Unauthorized access',
            ], 403);
        }


        return response()->json([
            'status' => true,
            'message' => 'get customer',
            'data' => new CustomerResource($customer)
        ]);
    }
}
