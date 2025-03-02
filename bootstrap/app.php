<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withCommands([
        // أضف اسم الـCommand هنا
        \App\Console\Commands\RunScheduledStatuses::class,
    ])
    ->withSchedule(function (Schedule $schedule) {
        // جدولة الـCommand للعمل كل ساعة
        $schedule->command('statuses:run')->everyFifteenMinutes();

        // $schedule->command('attendance:process')->hourly();
        // $schedule->command('attendance:process')->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
