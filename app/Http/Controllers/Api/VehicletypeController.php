<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleCategory;
use App\Models\Vehicletype;
use Illuminate\Http\Request;

class VehicletypeController extends Controller
{
    public function getVehicletype(Request $request)
    {


        // query filter
        $vehicletypes = Vehicletype::when($request->type, function ($query) use ($request) {
            $query->where('type', $request->type);
        })->get();


        if ($vehicletypes->count()) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $vehicletypes
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'there no vehicle type',
        ], 404);
    }


    public function getVehicletypeV2(Request $request)
    {


        // query filter
        $vehicletypes = Vehicletype::when($request->type, function ($query) use ($request) {
            $query->where('type', $request->type);
        })
            ->when($request->city, function ($query) use ($request) {
                $query->byCity($request->city);
            })
            ->get();

        if ($vehicletypes->count()) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $vehicletypes
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'there no vehicle type',
        ], 404);
    }


    public function getVehicleCategories(Request $request)
    {

        // query filter
        $vehicletypes = VehicleCategory::get();

        if ($vehicletypes->count()) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $vehicletypes
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'there no vehicle category',
        ], 404);
    }
}
