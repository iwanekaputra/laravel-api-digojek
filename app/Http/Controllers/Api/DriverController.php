<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriverResource;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class DriverController extends Controller
{

    public function updateDeviceToken(Request $request)
    {
        $driver = auth()->user();

        $driver->update([
            'device_token' => $request->device_token
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => $driver
        ]);
    }

    public function searchDriver(Request $request)
    {

        // query filter
        $drivers = Driver::when($request->city != null, function ($query) use ($request) {
            $query->where('city', $request->city);
        })
            ->when($request->status_driver != null, function ($query) use ($request) {
                $query->where('status_driver', $request->status_driver);
            })
            ->when($request->is_delivering != null, function ($query) use ($request) {
                $query->where('is_delivering', $request->is_delivering);
            })
            ->when($request->is_mober != null, function ($query) use ($request) {
                $query->where('is_mober', $request->is_mober);
            })
            ->when($request->vehicletype != null, function ($query) use ($request) {
                $query->whereHas('vehicle.vehicletype', function ($query) use ($request) {
                    $query->where('type', $request->vehicletype);
                });
            })
            ->get();


        if ($drivers->count()) {
            return response()->json([
                'status' => true,
                'message' => 'success get driver by city',
                'data' => $drivers,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'there no driver',
        ], 404);
    }

    public function updateStatusDriver(Request $request)
    {
        $driver = Driver::find($request->driver_id);
        if ($driver) {
            $driver->update([
                'status_driver' => $request->status_driver,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude
            ]);

            return response()->json([
                'status' => true,
                'message' => 'berhasil update status driver',
                'data' => $driver
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal update',

        ], 404);
    }

    public function updateLocationDriver(Request $request)
    {
        $driver = Driver::find($request->driver_id);

        $driver->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'success',
            'data' => $driver
        ]);
    }

    public function updateStatusMober(Request $request)
    {
        $driver = Driver::find($request->driver_id);
        if ($driver) {
            $driver->update([
                'is_mober' => $request->is_mober,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'berhasil update status driver',
                'data' => $driver
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal update',

        ], 404);
    }

    public function drivers(Request $request)
    {
        $driver = Driver::find($request->id);

        if ($driver) {
            return response()->json([
                'status' => true,
                'message' => 'get driver',
                'data' => new DriverResource($driver)
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'there no drivers'
        ], 404);
    }

    public function updateDriver(Request $request)
    {
        /*
            name
            email
            image
        */
        $driver = Driver::find($request->driver_id);

        if ($driver) {

            if ($request->image) {
                $image = $request->image;  // your base64 encoded
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName = Str::random(10) . '.' . 'png';
                File::put(storage_path() . '/app/public' . '/' . $imageName, base64_decode($image));
            }

            $driver->update([
                'name' => $request->name ?? $driver->name,
                'image' => $request->image ? $imageName : $driver->image,
                'email' => $request->email,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'success update driver',
                'data' => new DriverResource($driver)
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'failed update driver',
        ], 501);
    }
}
