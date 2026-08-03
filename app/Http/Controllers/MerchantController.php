<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\MerchantOperationalHours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MerchantController extends Controller
{
    public function updateMerchant(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:merchants,email,' . auth()->user()->id,

            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',

            'days' => 'required|array',
            'days.*' => 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',

            'opening_hour' => 'required|date_format:H:i',
            'closing_hour' => 'required|date_format:H:i|after:opening_hour',

            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $merchant = auth()->user();

        DB::transaction(function () use ($request, $merchant) {

            $imageName = $merchant->image;

            if ($request->hasFile('image') && $request->file('image')->isValid()) {

                // 🔥 HAPUS IMAGE LAMA
                if ($merchant->image && $merchant->image !== 'default.jpg') {
                    Storage::disk('public')->delete($merchant->image);
                }

                // 🔥 SIMPAN IMAGE BARU
                $imageName = $request->file('image')->store('merchants', 'public');
            }

            // ===== UPDATE MAIN =====
            $merchant->update([
                'name' => $request->name,
                'email' => $request->email,
                'image' => $imageName,
                'opening_hour' => $request->opening_hour,
                'closing_hour' => $request->closing_hour,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'address' => $request->address,
            ]);

            // ===== SYNC OPERATIONAL DAYS =====
            MerchantOperationalHours::where('merchant_id', $merchant->id)->delete();

            foreach (array_unique($request->days) as $day) {
                MerchantOperationalHours::create([
                    'merchant_id' => $merchant->id,
                    'day_of_week' => $day,
                    'opening_hour' => $request->opening_hour,
                    'closing_hour' => $request->closing_hour
                ]);
            }
        });

        $merchant->load('merchantOperationalHours');

        return response()->json([
            'status' => true,
            'message' => 'success update merchant',
            'data' => $merchant
        ]);
    }

    public function updateDeviceToken(Request $request)
    {
        $merchant = auth()->user();

        $merchant->update([
            'device_token' => $request->device_token
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => $merchant
        ]);
    }


    public function showMerchant(Merchant $merchant)
    {
        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => $merchant
        ]);
    }
}
