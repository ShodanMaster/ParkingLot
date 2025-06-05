<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AdminWorkerMiddleware;
use App\Models\QrCode;
use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
                'admin' => AdminMiddleware::class,
                'admin_worker' => AdminWorkerMiddleware::class,
            ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->call(function () {
            $qrCodes = QrCode::where('created_at', '<', Carbon::yesterday())->get();

            foreach ($qrCodes as $qrCode) {
                $filePath = str_replace('/storage', '', $qrCode->path);

                Storage::disk('public')->delete($filePath);
                $qrCode->delete();
            }

        })->daily();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
