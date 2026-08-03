<?php

namespace App\Http\Controllers;

use App\Models\IndonesiaCity;
use App\Models\IndonesiaDistrict;
use App\Models\IndonesiaProvince;
use App\Models\IndonesiaVillage;

class RegionController extends Controller
{
    public function getProvinces()
    {


        $provinces = IndonesiaProvince::get();

        return response()->json([
            'status' => 'success',
            'data' => $provinces,
        ]);
    }


    public function getCityByProvince($provinceCode)
    {


        $cities = IndonesiaCity::where('province_code', $provinceCode)->get();

        return response()->json([
            'status' => 'success',
            'data' => $cities,
        ]);
    }


    public function getCity()
    {


        $cities = IndonesiaCity::get();

        return response()->json([
            'status' => 'success',
            'data' => $cities,
        ]);
    }

    public function getDistrictByCity($cityCode)
    {

        $districts = IndonesiaDistrict::where('city_code', $cityCode)->get();

        return response()->json([
            'status' => 'success',
            'data' => $districts,
        ]);
    }

    public function getVillageByDistrict($districtCode)
    {

        $villages = IndonesiaVillage::where('district_code', $districtCode)->get();

        return response()->json([
            'status' => 'success',
            'data' => $villages,
        ]);
    }
}
