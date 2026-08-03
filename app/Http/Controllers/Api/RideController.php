<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CityVehiclePrice;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RideController extends Controller
{
    public function preview(Request $request)
    {
        $request->validate([
            'pickup_lat' => 'required',
            'pickup_lng' => 'required',
            'destination_lat' => 'required',
            'destination_lng' => 'required',
            'city' => 'required'
        ]);

        $customer = Customer::find(auth()->user()->id);


        /*
        ==========================
        1. HITUNG JARAK GOOGLE API
        ==========================
        */

        $google = Http::get(
            'https://maps.googleapis.com/maps/api/distancematrix/json',
            [
                'origins' =>
                $request->pickup_lat . ',' . $request->pickup_lng,

                'destinations' =>
                $request->destination_lat . ',' . $request->destination_lng,

                'key' => env('GOOGLE_MAP_KEY')
            ]
        );

        $json = $google->json();


        $element = $json['rows'][0]['elements'][0];

        $distanceMeter = $element['distance']['value'];
        $durationText = $element['duration']['text'];

        $km = ceil($distanceMeter / 1000);

        if ($km < 1) {
            $km = 1;
        }

        /*
        ==========================
        2. AMBIL HARGA KOTA
        ==========================
        */

        $prices = CityVehiclePrice::with('vehicle')
            ->where('city', $request->city)
            ->where('is_active', 1)
            ->whereHas('vehicle', function ($query) use ($request) {
                $query->where('slug', $request->type == 'mobil' ? "sedan" : "motor");
            })
            ->get();

        $services = [];

        foreach ($prices as $item) {

            $billKm = max($km, $item->minimum_km);

            $total = $billKm * $item->price_per_km;

            if ($total < $item->minimum_price) {
                $total = $item->minimum_price;
            }

            $services[] = [
                'vehicle_category_id' => $item->vehicle->id,
                'vehicle_name' => $item->vehicle->name,
                'vehicle_slug' => $item->vehicle->slug,
                'price' => $total,
                'price_per_km' => $item->price_per_km,
                'passenger' => 1,
                'link_image' => 'https://api.digojek.com/storage/3zbW89gxpQ.png'
            ];
        }

        return response()->json([
            'success' => true,
            'distance_km' => $km,
            'duration_text' => $durationText,
            'services' => $services
        ]);
    }


    public function previewShipPorter(Request $request)
    {
        // 1. Validasi Input Form Sesuai Struktur order_ship_cargo Baru
        $request->validate([
            'pickup_lat'           => 'required',
            'pickup_lng'           => 'required',
            'city'                 => 'required',
            'weight_kg'            => 'required|numeric|min:1',
            'service_type'         => 'required|in:turun_ke_kapal,naik_ke_kapal',
            'origin_location'      => 'required|string|max:150',
            'destination_location' => 'required|string|max:150',
            'ship_name'            => 'required|string|max:100',
            'length_cm'            => 'nullable|numeric',
            'width_cm'             => 'nullable|numeric',
            'height_cm'            => 'nullable|numeric',
        ]);

        $customer = Customer::find(auth()->user()->id);

        /*
    ==========================================================
    2. ESTIMASI JARAK GOOGLE API (OPSIONAL)
    ==========================================================
    Karena rute utama buruh berada di dalam area pelabuhan/kapal,
    Google Maps tidak bisa menghitung jarak internal tersebut.
    Namun, jika front-end mengirimkan koordinat tujuan (misal letak pelabuhan),
    kita tetap kalkulasikan jaraknya sebagai manifes data. Jika tidak, kita default ke 1 KM.
    */
        $distanceMeter = 0;
        $durationText = "Instant";
        $km = 1;

        if ($request->has('destination_lat') && $request->has('destination_lng')) {
            $google = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
                'origins'      => $request->pickup_lat . ',' . $request->pickup_lng,
                'destinations' => $request->destination_lat . ',' . $request->destination_lng,
                'key'          => env('GOOGLE_MAP_KEY')
            ]);

            $json = $google->json();

            if (isset($json['rows'][0]['elements'][0]['distance'])) {
                $element = $json['rows'][0]['elements'][0];
                $distanceMeter = $element['distance']['value'];
                $durationText = $element['duration']['text'];
                $km = ceil($distanceMeter / 1000);
                $km = $km < 1 ? 1 : $km;
            }
        }

        /*
    ==========================================================
    3. AMBIL HARGA LAYANAN BERDASARKAN KOTA & BERAT BARANG
    ==========================================================
    Mencari tarif berdasarkan kota tempat pelabuhan berada dan tipe kendaraan 'ship_porter'.
    */
        $prices = CityVehiclePrice::with('vehicle')
            ->where('city', $request->city)
            ->whereHas('vehicle', function ($query) {
                $query->where('slug', 'ship_porter');
            })
            ->where('is_active', 1)
            ->get();

        $services = [];
        $actualWeight = $request->weight_kg;

        // Ambil nilai dimensi dari request, default ke 0 jika tidak diisi kustomer
        $length = $request->input('length_cm', 0);
        $width  = $request->input('width_cm', 0);
        $height = $request->input('height_cm', 0);

        // Hitung berat volume (Konstanta standar laut = 4000)
        $volumetricWeight = 0;
        if ($length > 0 && $width > 0 && $height > 0) {
            $volumetricWeight = ($length * $width * $height) / 4000;
        }

        // Tentukan berat final yang digunakan (mencari angka tertinggi antara berat aktual vs berat volume)
        $finalWeight = max($actualWeight, $volumetricWeight);

        foreach ($prices as $item) {

            // Membandingkan berat final dengan batas minimum berat di master harga (jika ada kebijakan minimum kg)
            $billWeight = max($finalWeight, $item->minimum_km); // kolom minimum_km diasumsikan sebagai minimum_kg

            // Hitung total tarif: Berat Final (KG) x Harga per KG
            $total = ($billWeight * $item->price_per_km) + $item->service_fee; // kolom price_per_km diasumsikan sebagai price_per_kg

            // Cek batasan harga minimum (flat rate terendah)
            if ($total < $item->minimum_price) {
                $total = $item->minimum_price;
            }

            // Membungkus data ke dalam format array standar yang dikenali aplikasi front-end
            $services[] = [
                'vehicle_category_id'  => $item->vehicle->id,
                'vehicle_name'         => $item->vehicle->name,
                'vehicle_slug'         => $item->vehicle->slug,
                'price'                => $total,
                'price_per_kg'         => $item->price_per_km,
                'weight_kg'            => (float)$actualWeight,
                'volumetric_weight_kg' => round($volumetricWeight, 2), // Kita kirim data berat volumenya untuk transparansi ke user
                'billed_weight_kg'     => (float)$billWeight,     // Total berat yang akhirnya dijadikan tagihan
                'service_type'         => $request->service_type,
                'origin_location'      => $request->origin_location,
                'destination_location' => $request->destination_location,
                'ship_name'            => $request->ship_name,
                'link_image'           => 'https://api.digojek.com/storage/3zbW89gxpQ.png',
                'service_fee'          => $item->service_fee, // Kita tambahkan informasi service fee jika ada, agar front-end bisa menghitung total biaya yang harus dibayar user (price + service_fee)
            ];
        }

        return response()->json([
            'success'       => true,
            'distance_km'   => $km,
            'duration_text' => $durationText,
            'services'      => $services
        ]);
    }
}
