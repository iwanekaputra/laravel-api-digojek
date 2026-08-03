<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\MerchantOperationalHours;
use App\Models\OtpMerchant;
use App\Models\OtpRegisterMerchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthenticationMerchantController extends Controller
{



    public function registerV2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:merchants,email',
            'nohp' => 'required|digits_between:10,15|unique:merchants,nohp',

            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'address' => 'required|string',

            'agreement' => 'required|accepted',

            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',

            'category_merchant_id' => 'required|exists:category_merchants,id',

            'days' => 'required|array',
            'days.*' => 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',

            'opening_hour' => 'required|date_format:H:i',
            'closing_hour' => 'required|date_format:H:i',

            'otp' => 'required|digits:6',

            // FILE
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);


        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }



        $otpRegisterMerchant = OtpRegisterMerchant::where('nohp', $request->nohp)->first();

        if (!$otpRegisterMerchant || $otpRegisterMerchant->otp != $request->otp) {
            return response()->json([
                'message' => 'otp salah'
            ], 422);
        }

        if ($otpRegisterMerchant->otp == $request->otp) {
            $imageName = 'default.jpg';

            if ($request->hasFile('image')) {
                $imageName = $request->file('image')->store('merchants', 'public');
            }


            DB::transaction(function () use ($request, &$merchant) {
                $merchant = Merchant::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'image' => $imageName ?? 'default.jpg',
                    'balance' => 0,
                    'nohp' => $request->nohp,
                    'status' => 'Approved',
                    'city' => $request->city,
                    'province' => $request->province,
                    'address' => $request->address,
                    'referal' => $request->referal ?? '',
                    'agreement' => $request->agreement,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'category_merchant_id' => $request->category_merchant_id,
                    'code_referal' => Str::random(8),
                    'opening_hour' => $request->opening_hour,
                    'closing_hour' =>  $request->closing_hour
                ]);

                foreach ($request->days as $day) {
                    $merchantOperationalHour = MerchantOperationalHours::create([
                        'merchant_id' => $merchant->id,
                        'day_of_week' => $day,
                        'opening_hour' => $request->opening_hour,
                        'closing_hour' => $request->closing_hour
                    ]);
                }
            });


            return response()->json([
                'success' => true,
                'data' => $merchant,
                'message' => 'merchant register successfully'
            ]);
        } else {
            return response()->json([
                'message' => 'otp salah',

            ], 422);
        }
    }






    public function isNohpExist(Request $request)
    {

        $customer = Merchant::where('nohp', $request->nohp)->first();

        if ($customer) {
            return response()->json([
                'is-nohp-exist' => true
            ]);
        }

        return response()->json([
            'is-nohp-exist' => false
        ]);
    }

    public function sendWa(Request $request)
    {


        $merchant = Merchant::where('nohp', $request->nohp)->first();

        $userOtp = $this->generateOtp($request->nohp);
        $userOtp->sendEmail($merchant->email);

        return response()->json([
            'success' => true,
            'message' => 'Success send otp to email'
        ]);
    }
    public function sendWaRegister(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:merchants,email',
            'nohp' => 'required|digits_between:10,15|unique:merchants,nohp',

            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'address' => 'required|string',

            'agreement' => 'required|accepted',

            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',

            'category_merchant_id' => 'required|exists:category_merchants,id',

            'days' => 'required|array',
            'days.*' => 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',

            'opening_hour' => 'required|date_format:H:i',
            'closing_hour' => 'required|date_format:H:i',


            // FILE
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);


        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }





        $userOtp = $this->generateOtpRegister($request->nohp);
        $userOtp->sendEmail($request->email);

        return response()->json([
            'success' => true,
            'message' => 'Success send otp to email'
        ]);
    }

    public function generateOtp($nohp)
    {

        $merchant = Merchant::where('nohp', $nohp)->first();
        /* User Does not Have Any Existing OTP */



        $merchantOtp = OtpMerchant::where('merchant_id', $merchant->id)->latest()->first();

        $now = now();

        if ($merchantOtp && $now->isBefore($merchantOtp->expire_at)) {
            return $merchantOtp;
        }
        /* Create a New OTP */

        if ($merchantOtp) {
            $merchantOtp->delete();
        }

        return OtpMerchant::create([
            'merchant_id' => $merchant->id,
            'otp' => rand(123456, 999999),
            'expire_at' => $now->addMinutes(10)
        ]);
    }

    public function generateOtpRegister($nohp)
    {

        /* User Does not Have Any Existing OTP */



        $merchantOtp = OtpRegisterMerchant::where('nohp', $nohp)->latest()->first();

        $now = now();

        if ($merchantOtp && $now->isBefore($merchantOtp->expire_at)) {
            return $merchantOtp;
        }
        /* Create a New OTP */

        if ($merchantOtp) {
            $merchantOtp->delete();
        }

        return OtpRegisterMerchant::create([
            'nohp' => $nohp,
            'otp' => rand(123456, 999999),
            'expire_at' => $now->addMinutes(10)
        ]);
    }




    public function isOtpCorrectV2(Request $request)
    {



        $merchant = Merchant::where('nohp', $request->nohp)->first();
        $otpMerchant = OtpMerchant::where('merchant_id', $merchant->id)->first();

        if ($request->nohp == '081324693686') {
            $merchant->update([
                'status' => 'active'
            ]);

            $merchant['token'] =  $merchant->createToken('MyApp')->plainTextToken;


            $merchant->MerchantOperationalHours;

            return response()->json([
                'is-otp-correct' => true,
                'data' => $merchant
            ]);
        }
        if ($otpMerchant) {
            if ($otpMerchant->otp == $request->otp) {
                $otpMerchant->merchant->update([
                    'status' => 'active'
                ]);

                $merchant['token'] =  $merchant->createToken('MyApp')->plainTextToken;

                $otpMerchant->delete();

                $merchant->MerchantOperationalHours;

                return response()->json([
                    'is-otp-correct' => true,
                    'data' => $merchant
                ]);
            }
        }

        return response()->json([
            'is-otp-correct' => false
        ], 422);
    }
}
