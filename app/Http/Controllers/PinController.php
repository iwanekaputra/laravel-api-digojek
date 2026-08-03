<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PinController extends Controller
{

    public function isPinExists()
    {
        $customer = Customer::find(auth()->user()->id);

        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }


        if ($customer->pin == null) {
            return response()->json(['exists' => false], 200);  // Jika PIN belum ada
        }

        return response()->json(['exists' => true], 200);  // Jika PIN sudah ada
    }


    public function verifyPin(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'pin' => 'required|digits_between:1,6', // Memastikan pin terdiri dari antara 5 hingga 6 digit
        ]);

        if ($validator->fails()) {

            $response = [
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ];

            return response()->json($response, 422);
        }

        $user = auth()->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);
        }


        $customer = Customer::find(auth()->user()->id);

        if (strlen((string)$request->pin) == 5) {
            $pin = '0' . (string)$request->pin;
            if ((string)$customer->pin == $pin) {
                return response()->json(['message' => 'PIN valid.'], 200); // Jika PIN valid
            }
        } else if (strlen((string)$request->pin) == 1 && $request->pin == 0) {
            $pin = '000000';
            if ((string)$customer->pin == $pin) {
                return response()->json(['message' => 'PIN valid.'], 200); // Jika PIN valid
            }
        } else if (strlen((string)$request->pin) == 6) {
            if ((string)$customer->pin == (string)$request->pin) {
                return response()->json(['message' => 'PIN valid.'], 200); // Jika PIN valid
            }
        } else {
            if ((string)$customer->pin == (string)$request->pin) {
                return response()->json(['message' => 'PIN valid.'], 200); // Jika PIN valid
            }
        }



        return response()->json(['message' => 'PIN tidak valid.'], 400); // Jika PIN valid
    }
}
