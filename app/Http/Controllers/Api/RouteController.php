<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RouteController extends Controller
{
    public function getRoute(Request $request)
    {
        $originLat = $request->origin_lat;
        $originLng = $request->origin_lng;
        $destLat   = $request->destination_lat;
        $destLng   = $request->destination_lng;

        // 🔒 ambil dari .env
        $apiKey = env('GOOGLE_MAP_KEY');

        $url = "https://maps.googleapis.com/maps/api/directions/json";

        $response = Http::get($url, [
            'origin'      => "$originLat,$originLng",
            'destination' => "$destLat,$destLng",
            'mode'        => 'driving',
            'key'         => $apiKey,
        ]);

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch route'
            ], 500);
        }

        $data = $response->json();

        if (empty($data['routes'])) {
            return response()->json([
                'success' => false,
                'message' => 'No route found'
            ], 404);
        }

        $polyline = $data['routes'][0]['overview_polyline']['points'];

        return response()->json([
            'success'  => true,
            'polyline' => $polyline,
        ]);
    }
}
