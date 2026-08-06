<?php

use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\api\DriverController;
use App\Http\Controllers\Api\DriverOrderController;
use App\Http\Controllers\api\VehicletypeController;
use App\Http\Controllers\AuthenticationDriverController;
use App\Http\Controllers\AuthenticationMerchantController;
use App\Http\Controllers\CategoryMerchantController;
use App\Http\Controllers\CategoryProductController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\MootaController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\SettingDepositController;
use App\Http\Controllers\SharedController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\OrderChatController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\PaymentMethod\PaymentMethodController;
use App\Http\Controllers\Api\RideController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\DepositDriverController;
use App\Http\Middleware\LogApiRequest;
use App\Services\WhatsAppGatewayService;

Route::post('/test-send-wa', function (Request $request, WhatsAppGatewayService $waService) {
    $request->validate([
        'phone' => 'required|string',
    ]);

    $otpDummy = (string) rand(100000, 999999);

    try {
        $result = $waService->sendOtp($request->phone, $otpDummy);

        return response()->json([
            'success' => true,
            'message' => 'OTP berhasil dikirim ke WhatsApp',
            'otp_sent' => $otpDummy,
            'gateway_response' => $result,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/location/search', [LocationController::class, 'search']);
Route::post('test-fcm', [SharedController::class, 'testFcm']);



Route::middleware(LogApiRequest::class)->group(function () {

    Route::get('/user', function (Request $request) {
        $user = $request->user();

        // Konversi pin ke integer jika nilainya ada
        if (!is_null($user->pin)) {
            $user->pin = (int) $user->pin;
        }

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    })->middleware('auth:sanctum');

    Route::get('/merchant', function (Request $request) {

        $request->user()->MerchantOperationalHours;
        return $request->user();
    })->middleware('auth:sanctum');

    Route::get('/driver', function (Request $request) {

        return $request->user();
    })->middleware('auth:sanctum');



    Route::controller(AuthenticationController::class)->group(function () {
        Route::post('customer/register-v2', 'registerV2');
        Route::post('customer/is-nohp-exist', 'isNohpExist');
        Route::post('customer/send-otp-wa', 'sendWa');
        Route::post('customer/send-otp-wa-register', 'sendWaRegister');
        Route::post('customer/is-otp-correct-v2', 'isOtpCorrectV2');
    });

    Route::controller(AuthenticationDriverController::class)->group(function () {

        Route::post('driver/register-v2', 'registerV2');
        // Route::post('driver/is-email-exist', 'isEmailExist');
        Route::post('driver/is-nohp-exist', 'isNohpExist');
        Route::post('driver/send-otp-wa', 'sendWa');
        Route::post('driver/send-otp-wa-register', 'sendWaRegister');
        Route::post('driver/is-otp-correct-v2', 'isOtpCorrectV2');
    });

    Route::controller(AuthenticationMerchantController::class)->group(function () {
        Route::post('merchant/register-v2', 'registerV2');
        // Route::post('driver/is-email-exist', 'isEmailExist');
        Route::post('merchant/is-nohp-exist', 'isNohpExist');
        Route::post('merchant/send-otp-wa', 'sendWa');
        Route::post('merchant/send-otp-wa-register', 'sendWaRegister');
        Route::post('merchant/is-otp-correct-v2', 'isOtpCorrectV2');
    });










    Route::get('driver/vehicletype', [VehicletypeController::class, 'getVehicletype']);
    Route::get('driver/vehicletype-v2', [VehicletypeController::class, 'getVehicletypeV2']);
    Route::get('driver/vehicle-categories', [VehicletypeController::class, 'getVehicleCategories']);


    Route::get('merchant/category-merchant', [CategoryMerchantController::class, 'index']);
    Route::get('merchant/category-product', [CategoryProductController::class, 'index']);

    Route::middleware(['auth:sanctum', 'active'])->group(function () {


        Route::prefix('orders/chats')->group(function () {
            Route::get('/{order_id}', [OrderChatController::class, 'index']); // Ambil riwayat chat
            Route::post('/', [OrderChatController::class, 'store']);          // Kirim pesan baru
        });
        // new
        Route::post('/ride/preview', [RideController::class, 'preview']);
        Route::post('/ride/preview-ship-porter', [RideController::class, 'previewShipPorter']);

        Route::post('/order/create', [ApiOrderController::class, 'create']);
        Route::post('/order/create-ship-porter', [ApiOrderController::class, 'createShipPorter']);


        // perlu koreksi
        Route::get('/customer/track-order', [CustomerOrderController::class, 'trackOrder']);


        Route::get('/customer/order-detail/{id}', [CustomerOrderController::class, 'orderDetail']);
        Route::post('/customer/cancel-order', [CustomerOrderController::class, 'cancelOrder']);
        Route::post('/customer/rate-order', [CustomerOrderController::class, 'rateOrder']);
        Route::get('/customer/order-history', [CustomerOrderController::class, 'orderHistory']);
        Route::post('/customer/route', [RouteController::class, 'getRoute']);
        // customer

        // Route::get('customer/price-by-city', [SharedController::class, 'priceByCity']);
        Route::get('customers/sliders', [SharedController::class, 'getSliders']);
        // Route::get('customer/price-by-vehicle', [SharedController::class, 'priceByVehicle']);
        // Route::get('customers', [CustomerController::class, 'customers']);
        Route::post('customers/deposit', [DepositController::class, 'customersDeposit']);
        Route::post('customers/deposit/{deposit_customer}/update', [DepositController::class, 'updateDepositCustomer']);
        Route::get('customers/deposit', [DepositController::class, 'getDepositCustomers']);
        Route::get('customers/merchants', [SharedController::class, 'listMerchants']);
        Route::get('customers/carts', [SharedController::class, 'carts']);
        Route::post('customers/carts', [SharedController::class, 'storeCarts']);
        Route::get('customers/carts/destroy/{cart}', [SharedController::class, 'destroyCarts']);
        Route::get('customers/setting-deposit', [SettingDepositController::class, 'customersSettingDeposit']);
        Route::get('customers/transaction', [TransactionController::class, 'customersGetTransaction']);

        Route::post('/v2/customers/deposit', [DepositController::class, 'createDeposit']);
        Route::get('/v2/customers/deposit', [DepositController::class, 'index']);
        Route::get('/v2/customers/deposit/{depositCustomer}', [DepositController::class, 'show']);






        Route::get('customers/get-user-by-device-token', [CustomerController::class, 'getUserByDeviceToken']);





        Route::post('customers/is-pin-exists', [PinController::class, 'isPinExists']);
        Route::post('customers/verify-pin', [PinController::class, 'verifyPin']);
    });

    Route::middleware(['auth:sanctum', 'driver_active'])->group(function () {
        Route::prefix('driver/orders/chats')->group(function () {
            Route::get('/{order_id}', [OrderChatController::class, 'index']); // Ambil riwayat chat
            Route::post('/', [OrderChatController::class, 'store']);          // Kirim pesan baru
        });
        Route::post('/v2/drivers/deposit', [DepositDriverController::class, 'createDeposit']);
        Route::get('/v2/drivers/deposit', [DepositDriverController::class, 'index']);
        Route::get('/v2/drivers/deposit/{depositDriver}', [DepositDriverController::class, 'show']);


        // new
        Route::get('/driver/orders/incoming', [DriverOrderController::class, 'incoming']);
        Route::post('/driver/accept-order', [DriverOrderController::class, 'acceptOrder']);
        Route::get('/driver/current-order', [DriverOrderController::class, 'currentOrder']);
        Route::post('/driver/update-location', [DriverOrderController::class, 'updateLocation']);
        Route::post('/driver/update-order-status', [DriverOrderController::class, 'updateOrderStatus']);
        Route::post('/driver/cancel-order', [DriverOrderController::class, 'cancelOrder']);
        Route::get('/driver/order-history', [DriverOrderController::class, 'orderHistory']);
        Route::get('/driver/order-detail/{id}', [DriverOrderController::class, 'orderDetail']);



        Route::get('drivers', [DriverController::class, 'drivers']);



        Route::post('drivers/update-device-token', [DriverController::class, 'updateDeviceToken']);



        Route::get('driver/setting-deposit', [SettingDepositController::class, 'driversSettingDeposit']);
        Route::get('drivers/transaction', [TransactionController::class, 'driversGetTransaction']);
        Route::get('drivers/get-transaction-purchases-by-driver-id', [SharedController::class, 'getTransactionPurchasesByDriverId']);
    });


    Route::middleware(['auth:sanctum', 'merchant_active'])->group(function () {
        Route::get('merchant/earning-merchant', [OrderController::class, 'earningMerchant']);

        Route::post('merchant/update', [MerchantController::class, 'updateMerchant']);
        Route::post('merchant/update-device-token', [MerchantController::class, 'updateDeviceToken']);




        Route::get('merchant/get-transaction-purchases-by-merchant-id', [SharedController::class, 'getTransactionPurchasesByMerchantId']);


        Route::get('merchant/transaction', [TransactionController::class, 'merchantGetTransaction']);


        Route::post('merchants/update-status-order-pijat', [OrderController::class, 'updateStatusOrderPijat']);
        Route::post('merchants/orders/finish-order-pijat', [OrderController::class, 'finishOrderPijat']);
    });



    // callback service
    Route::post('moota/callback', [MootaController::class, 'transaction']);
    Route::get('moota/get-list-bank', [MootaController::class, 'getListBank']);






    Route::get('province', [RegionController::class, 'getProvinces']);
    Route::get('city', [RegionController::class, 'getCity']);
    Route::get('district/{cityCode}', [RegionController::class, 'getDistrictByCity']);

    Route::get('city/{provinceCode}', [RegionController::class, 'getCityByProvince']);
    Route::get('village/{districtCode}', [RegionController::class, 'getVillageByDistrict']);



    Route::get('/customers/payment-methods', [PaymentMethodController::class, 'index']);
});
