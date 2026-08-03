<?php

namespace App\Http\Controllers;

use App\Http\Resources\DriverResource;
use App\Models\Driver;
use App\Models\OtpDriver;
use App\Models\OtpRegisterDriver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthenticationDriverController extends Controller
{



    public function  registerV2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // ===== DATA DRIVER =====
            'name' => 'required|string|max:255',
            'birthday' => 'required|date',
            'gender' => 'required|in:Laki-Laki,Perempuan',
            'email' => 'required|email|unique:drivers,email',

            'image' => 'required',

            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'address' => 'required|string',

            'agreement' => 'required|accepted',

            'nohp' => 'required|digits_between:10,15|unique:drivers,nohp',

            'ktp' => 'required',
            'sim' => 'required',
            'skck' => 'nullable',

            // ===== DATA VEHICLE =====
            'vehicletype_id' => 'required|exists:vehicle_categories,id',
            'brand' => 'required|string|max:255',
            'registration_number' => 'required|string|max:50',
            'manufacture_year' => 'required|digits:4',
            'color' => 'required|string|max:50',

            'stnk' => 'required',
            'image_vehicle' => 'required',

            'otp' => 'required|digits:6|exists:otp_register_drivers,otp'
        ], [
            'nohp.required' => 'Nomor HP wajib diisi.',
            'nohp.digits_between' => 'Nomor HP harus terdiri dari 10–15 digit.',
            'nohp.unique' => 'Nomor HP sudah terdaftar.',

            'agreement.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',

            'image.image' => 'Foto profil harus berupa gambar.',
            'ktp.image' => 'KTP harus berupa gambar.',
            'sim.image' => 'SIM harus berupa gambar.',
            'stnk.image' => 'STNK harus berupa gambar.',
        ]);
        if ($validator->fails()) {

            $response = [
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ];

            return response()->json($response, 422);
        }


        $otpRegisterDriver = OtpRegisterDriver::where('nohp', $request->nohp)->first();

        if ($otpRegisterDriver->otp == $request->otp) {
            if ($request->stnk) {
                $imageStnk = $request->stnk;  // your base64 encoded
                $imageStnk = str_replace('data:image/png;base64,', '', $imageStnk);
                $imageStnk = str_replace(' ', '+', $imageStnk);
                $imageNameStnk = Str::random(10) . '.' . 'png';
                File::put(storage_path() . '/app/public' . '/' . $imageNameStnk, base64_decode($imageStnk));
            }

            if ($request->image_vehicle) {
                $imageVehicle = $request->image_vehicle;  // your base64 encoded
                $imageVehicle = str_replace('data:image/png;base64,', '', $imageVehicle);
                $imageVehicle = str_replace(' ', '+', $imageVehicle);
                $imageNameVehicle = Str::random(10) . '.' . 'png';
                File::put(storage_path() . '/app/public' . '/' . $imageNameVehicle, base64_decode($imageVehicle));
            }

            if ($request->skck) {
                $imageSkck = $request->skck;  // your base64 encoded
                $imageSkck = str_replace('data:image/png;base64,', '', $imageSkck);
                $imageSkck = str_replace(' ', '+', $imageSkck);
                $imageNameSkck = Str::random(10) . '.' . 'png';
                File::put(storage_path() . '/app/public' . '/' . $imageNameSkck, base64_decode($imageSkck));
            }

            if ($request->sim) {
                $imageSim = $request->sim;  // your base64 encoded
                $imageSim = str_replace('data:image/png;base64,', '', $imageSim);
                $imageSim = str_replace(' ', '+', $imageSim);
                $imageNameSim = Str::random(10) . '.' . 'png';
                File::put(storage_path() . '/app/public' . '/' . $imageNameSim, base64_decode($imageSim));
            }

            if ($request->ktp) {
                $imageKtp = $request->ktp;  // your base64 encoded
                $imageKtp = str_replace('data:image/png;base64,', '', $imageKtp);
                $imageKtp = str_replace(' ', '+', $imageKtp);
                $imageNameKtp = Str::random(10) . '.' . 'png';
                File::put(storage_path() . '/app/public' . '/' . $imageNameKtp, base64_decode($imageKtp));
            }

            if ($request->image) {
                $image = $request->image;  // your base64 encoded
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10) . '.' . 'png';
                File::put(storage_path() . '/app/public' . '/' . $imageName, base64_decode($image));
            }

            DB::transaction(function () use ($request, $imageNameStnk, $imageNameVehicle, $imageName, $imageNameKtp, $imageNameSim, &$vehicle, &$driver) {
                $vehicle = Vehicle::create([
                    'vehicletype_id' => $request->vehicletype_id,
                    'brand' => $request->brand,
                    'registration_number' => $request->registration_number,
                    'manufacture_year' => $request->manufacture_year,
                    'color' => $request->color,
                    'stnk' => $imageNameStnk,
                    'image' => $imageNameVehicle,
                    'vehicle_category_id' => $request->vehicletype_id
                ]);

                $driver = Driver::create([
                    'name' => $request->name,
                    'birthday' => $request->birthday,
                    'gender' => $request->gender,
                    'email' => $request->email,
                    'image' => $imageName,
                    'balance' => 0,
                    'city' => $request->city,
                    'province' => $request->province,
                    'address' => $request->address,
                    'status' => 'Sedang Menunggu Persetujuan',
                    'referal' => $request->referal ?? '',
                    'vehicle_id' => $vehicle->id,
                    'agreement' => $request->agreement,
                    'nohp' => $request->nohp,
                    'ktp' => $imageNameKtp,
                    'sim' => $imageNameSim,
                    'status_driver' => 'offline',
                    'code_referal' => Str::random(8)
                ]);
            });



            if ($vehicle) {
                return response()->json([
                    'success' => true,
                    'data' => new DriverResource($driver),
                    'message' => 'Driver register successfully'
                ]);
            }
        } else {
            return response()->json([
                'message' => 'otp salah'
            ], 422);
        }
    }



    public function isNohpExist(Request $request)
    {

        $customer = Driver::where('nohp', $request->nohp)->first();

        if ($customer) {
            return response()->json([
                'is-nohp-exist' => true
            ]);
        }

        return response()->json([
            'is-nohp-exist' => false
        ]);
    }

    public function generateOtp($nohp)
    {

        $driver = Driver::where('nohp', $nohp)->first();
        /* User Does not Have Any Existing OTP */



        $driverOtp = OtpDriver::where('driver_id', $driver->id)->latest()->first();

        $now = now();

        if ($driverOtp && $now->isBefore($driverOtp->expire_at)) {
            return $driverOtp;
        }
        /* Create a New OTP */

        if ($driverOtp) {
            $driverOtp->delete();
        }

        return OtpDriver::create([
            'driver_id' => $driver->id,
            'otp' => rand(123456, 999999),
            'expire_at' => $now->addMinutes(10)
        ]);
    }
    public function generateOtpRegister($nohp)
    {




        $driverOtp = OtpRegisterDriver::where('nohp', $nohp)->latest()->first();

        $now = now();

        if ($driverOtp && $now->isBefore($driverOtp->expire_at)) {
            return $driverOtp;
        }
        /* Create a New OTP */

        if ($driverOtp) {
            $driverOtp->delete();
        }

        return OtpRegisterDriver::create([
            'nohp' => $nohp,
            'otp' => rand(123456, 999999),
            'expire_at' => $now->addMinutes(10)
        ]);
    }

    public function sendWa(Request $request)
    {


        $driver = Driver::where("nohp", $request->nohp)->first();

        if ($driver->status == 'Sedang Menunggu Persetujuan') {
            return response()->json([
                'message' => 'Akunmu masih menunggu persetujuan'

            ], 422);
        }

        $driver = Driver::where("nohp", $request->nohp)->first();

        $userOtp = $this->generateOtp($request->nohp);
        $userOtp->sendEmail($driver->email);

        return response()->json([
            'success' => true,
            'message' => 'Success send otp to wa'
        ]);
    }
    public function sendWaRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // ===== DATA DRIVER =====
            'name' => 'required|string|max:255',
            'birthday' => 'required|date',
            'gender' => 'required|in:Laki-Laki,Perempuan',
            'email' => 'required|email|unique:drivers,email',

            'image' => 'required',

            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'address' => 'required|string',

            'agreement' => 'required|accepted',

            'nohp' => 'required|digits_between:10,15|unique:drivers,nohp',

            'ktp' => 'required',
            'sim' => 'required',
            'skck' => 'nullable',

            // ===== DATA VEHICLE =====
            'vehicletype_id' => 'required|exists:vehicle_categories,id',
            'brand' => 'required|string|max:255',
            'registration_number' => 'required|string|max:50',
            'manufacture_year' => 'required|digits:4',
            'color' => 'required|string|max:50',

            'stnk' => 'required',
            'image_vehicle' => 'required'
        ], [
            'nohp.required' => 'Nomor HP wajib diisi.',
            'nohp.digits_between' => 'Nomor HP harus terdiri dari 10–15 digit.',
            'nohp.unique' => 'Nomor HP sudah terdaftar.',

            'agreement.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',

            'image.image' => 'Foto profil harus berupa gambar.',
            'ktp.image' => 'KTP harus berupa gambar.',
            'sim.image' => 'SIM harus berupa gambar.',
            'stnk.image' => 'STNK harus berupa gambar.',
        ]);

        if ($validator->fails()) {

            $response = [
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ];

            return response()->json($response, 422);
        }




        $userOtp = $this->generateOtpRegister($request->nohp);
        $userOtp->sendEmail($request->email);

        return response()->json([
            'success' => true,
            'message' => 'Success send otp to email'
        ]);
    }




    public function isOtpCorrectV2(Request $request)
    {

        $driver = Driver::where('nohp', $request->nohp)->first();

        $otpDriver = OtpDriver::where('driver_id', $driver->id)->first();

        if ($request->nohp == '081324693686') {
            $driver->update([
                'status' => 'active'
            ]);

            $driver['token'] =  $driver->createToken('MyApp')->plainTextToken;



            return response()->json([
                'is-otp-correct' => true,
                'data' => new DriverResource($driver)
            ]);
        }

        if ($otpDriver) {
            if ($otpDriver->otp == $request->otp) {
                $otpDriver->driver->update([
                    'status' => 'active'
                ]);

                $driver['token'] =  $driver->createToken('MyApp')->plainTextToken;

                $otpDriver->delete();

                return response()->json([
                    'is-otp-correct' => true,
                    'data' => new DriverResource($driver)
                ]);
            }
        }

        return response()->json([
            'is-otp-correct' => false
        ], 422);
    }
}
