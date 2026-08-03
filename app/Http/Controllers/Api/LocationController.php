<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2|max:100',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        $query = $request->q;

        $response = Http::get(
            'https://maps.googleapis.com/maps/api/place/autocomplete/json',
            [
                'input' => $query,
                'key' => env('GOOGLE_MAP_KEY'),
                'components' => 'country:id',
                'locationbias' => 'circle:50000@' . $validated['latitude'] . ',' . $validated['longitude'],

                // 'radius' => 50000, // 50 km
                // 'strictbounds' => true
            ]
        );


        return response()->json(
            $response->json()['predictions']
        );
    }
}
