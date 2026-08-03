<?php

namespace App\Http\Controllers;

use App\Models\SettingDepositCustomer;
use App\Models\SettingDepositDriver;
use App\Models\SettingDepositMerchant;

class SettingDepositController extends Controller
{
    public function customersSettingDeposit()
    {
        $settingDeposit = SettingDepositCustomer::first();

        return response()->json([
            'status' => true,
            'message' => 'Success Get setting deposit',
            'data' => $settingDeposit
        ]);
    }

    public function driversSettingDeposit()
    {
        $settingDeposit = SettingDepositDriver::first();

        return response()->json([
            'status' => true,
            'message' => 'Success Get setting deposit',
            'data' => $settingDeposit
        ]);
    }

    public function merchantsSettingDeposit()
    {
        $settingDeposit = SettingDepositMerchant::first();

        return response()->json([
            'status' => true,
            'message' => 'Success Get setting deposit',
            'data' => $settingDeposit
        ]);
    }
}
