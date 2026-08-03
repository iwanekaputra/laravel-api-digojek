<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        Carbon::setToStringFormat('Y-m-d H:i:s');
        date_default_timezone_set('Asia/Jakarta');

        Gate::define('viewLogViewer', function ($user) {
            if ($user->getRoleNames()[0] == 'Owner') {
                return true;
            }

            return false;
        });
    }
}
