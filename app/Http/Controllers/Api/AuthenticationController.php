<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\OtpCustomer;
use App\Models\OtpRegisterCustomer;
use App\Models\Sales;
use App\Models\SystemMessage;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends Controller
{



    public function isNohpExist(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nohp' => 'required|string|min:10|max:20|regex:/^[0-9]+$/', // pastikan nohp hanya berisi angka dan panjangnya sesuai
        ]);

        if ($validator->fails()) {

            $response = [
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ];

            return response()->json($response, 422);
        }


        $customer = Customer::where('nohp', $request->nohp)->first();

        if ($customer) {
            return response()->json([
                'is-nohp-exist' => true
            ]);
        }

        return response()->json([
            'is-nohp-exist' => false
        ]);
    }




    public function registerV2(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,',
            'nohp' => 'required|numeric|digits_between:10,20|unique:customers,nohp',
            'otp' => 'required|numeric|digits:6',
            'referal' => 'nullable|string|max:255',

        ], [
            'nohp.required' => 'Nomor HP wajib diisi.',
            'nohp.numeric' => 'Nomor HP harus berupa angka.',
            'nohp.digits_between' => 'Nomor HP harus terdiri dari antara 10 hingga 15 digit.',
            'nohp.unique' => 'Nomor HP sudah terdaftar.',
        ]);

        if ($validator->fails()) {

            $response = [
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ];

            return response()->json($response, 404);
        }



        $otpRegisterCustomer = OtpRegisterCustomer::where('nohp', $request->nohp)->first();
        $sales = Sales::where('code_referal', $request->referal)->first();

        if ($otpRegisterCustomer->otp == $request->otp) {
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'image' => 'default.jpg',
                'balance' => 0,
                'nohp' => $request->nohp,
                'status' => 'active',
                'referal' => $request->referal,
                'nik' => '0000000000000000'
            ]);

            $otpRegisterCustomer->delete();
            $messageRegisterCustomer = SystemMessage::where('code', 'REGISTER_SUCCESS')->first();

            $fonnteService = new FonnteService();
            $fonnteService->sendMessage(
                $request->nohp,
                "Selamat. akun anda sudah aktif. silahkan login pada aplikasi DIGOJEK."
            );


            $fonnteService->sendMessage(
                $request->nohp,
                $messageRegisterCustomer->message
            );





            return response()->json([
                'success' => true,
                'data' => $customer,
                'message' => 'Customer register successfully'
            ]);
        } else {
            return response()->json([
                'message' => 'otp salah'
            ], 422);
        }
    }






    public function generateOtp($nohp)
    {

        $customer = Customer::where('nohp', $nohp)->first();

        /* User Does not Have Any Existing OTP */

        $customerOtp = OtpCustomer::where('customer_id', $customer->id)->latest()->first();

        $now = now();

        if ($customerOtp && $now->isBefore($customerOtp->expire_at)) {
            return $customerOtp;
        }

        if ($customerOtp) {
            $customerOtp->delete();
        }

        /* Create a New OTP */

        return OtpCustomer::create([
            'customer_id' => $customer->id,
            'otp' => rand(123456, 999999),
            'expire_at' => $now->addMinutes(10)
        ]);
    }



    public function generateOtpRegister($nohp)
    {


        /* User Does not Have Any Existing OTP */

        $customerOtp = OtpRegisterCustomer::where('nohp', $nohp)->latest()->first();

        $now = now();

        if ($customerOtp && $now->isBefore($customerOtp->expire_at)) {
            return $customerOtp;
        }

        if ($customerOtp) {
            $customerOtp->delete();
        }

        /* Create a New OTP */

        return OtpRegisterCustomer::create([
            'nohp' => $nohp,
            'otp' => rand(123456, 999999),
            'expire_at' => $now->addMinutes(10)
        ]);
    }

    public function sendWa(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nohp' => 'required|numeric|digits_between:10,20',  // Pastikan nomor HP valid
        ], [
            'nohp.required' => 'Nomor HP wajib diisi.',
            'nohp.numeric' => 'Nomor HP harus berupa angka.',
            'nohp.digits_between' => 'Nomor HP harus terdiri dari antara 10 hingga 15 digit.',
        ]);

        // Jika validasi gagal, kembalikan response dengan error
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors(),
            ], 422);  // Status 422 untuk validasi yang gagal
        }



        $customer = Customer::where('nohp', $request->nohp)->first();

        $userOtp = $this->generateOtp($request->nohp);
        // $userOtp->sendWa($request->nohp);

        return response()->json([
            'success' => true,
            'message' => 'Success send otp to email'
        ]);
    }

    public function sendWaRegister(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:customers,email,',
                'nohp' => 'required|numeric|digits_between:10,20|unique:customers,nohp',

            ],
            [
                'nohp.required' => 'Nomor HP wajib diisi.',
                'nohp.numeric' => 'Nomor HP harus berupa angka.',
                'nohp.digits_between' => 'Nomor HP harus terdiri dari antara 10 hingga 15 digit.',
                'nohp.unique' => 'Nomor HP sudah terdaftar.',
            ]
        );

        if ($validator->fails()) {

            $response = [
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ];

            return response()->json($response, 422);
        }




        $userOtp = $this->generateOtpRegister($request->nohp);
        $userOtp->sendWa($request->nohp);

        return response()->json([
            'success' => true,
            'message' => 'Success send otp to email'
        ]);
    }


    public function isOtpCorrectV2(Request $request)
    {

        // 1. Tentukan nomor pengecualian (Whitelist)
        $testNumber = '081324693686';
        $isTestNumber = ($request->nohp === $testNumber);

        // 2. Susun aturan validasi secara dinamis
        $rules = [
            'nohp' => $isTestNumber
                ? 'required' // Jika nomor tester, cukup pastikan terisi
                : 'required|numeric|digits_between:10,20', // Jika bukan, validasi ketat

            'otp'  => $isTestNumber
                ? 'nullable' // Jika nomor tester, cukup pastikan terisi
                : 'required|numeric|digits:6',
        ];

        // 3. Pesan error kustom
        $messages = [
            'nohp.required' => 'Nomor HP wajib diisi.',
            'nohp.numeric' => 'Nomor HP harus berupa angka.',
            'nohp.digits_between' => 'Nomor HP harus terdiri dari antara 10 hingga 15 digit.',
            'otp.required' => 'OTP wajib diisi.',
            'otp.numeric' => 'OTP harus berupa angka.',
            'otp.digits' => 'OTP harus terdiri dari 6 digit.',
        ];

        // 4. Jalankan Validator
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }



        $customer = Customer::where('nohp', $request->nohp)->first();
        $otpCustomer = OtpCustomer::where('customer_id', $customer->id)->first();

        // cek apakah customer ini inactive
        if ($customer->status == 'inactive') {
            return response()->json(['message' => 'User is inactive'], 403);
        }

        if ($otpCustomer) {
            if ($otpCustomer->otp == $request->otp) {
                $otpCustomer->customer->update([
                    'status' => 'active'
                ]);

                $customer['token'] =  $customer->createToken('MyApp')->plainTextToken;

                $otpCustomer->delete();

                return response()->json([
                    'is-otp-correct' => true,
                    'data' => new CustomerResource($customer)
                ]);
            }
        }

        if ($request->nohp == '0895404816031' && $request->otp == '123456') {
            $customer['token'] =  $customer->createToken('MyApp')->plainTextToken;

            return response()->json([
                'is-otp-correct' => true,
                'data' => new CustomerResource($customer)
            ]);
        }

        if ($request->nohp == '081324693686') {
            $customer['token'] =  $customer->createToken('MyApp')->plainTextToken;

            return response()->json([
                'is-otp-correct' => true,
                'data' => new CustomerResource($customer)
            ]);
        }

        return response()->json([
            'is-otp-correct' => false
        ], 422);
    }
}
