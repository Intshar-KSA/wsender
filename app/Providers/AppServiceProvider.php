<?php

namespace App\Providers;

use App\Events\DeviceCreated;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use App\Listeners\ActivateFreePlanForDevice;

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

        // Filament::serving(function () {
        //     // التأكد من توثيق المستخدم
        //     Route::middleware(['verified'])->group(function () {
        //         // باقي تعريفات Filament
        //     });
        // });
// Event::listen(
//     DeviceCreated::class,
//     ActivateFreePlanForDevice::class
// );
    }
}
