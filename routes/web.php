<?php

use App\Http\Controllers\IndexController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\VehicleController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Scan\ScanInController;
use App\Http\Controllers\Scan\ScanOutController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'index'])->name('login');

Route::post('/login-in', [LoginController::class, 'logingIn'])->name('logingin');

Route::get('/', [IndexController::class, 'index']);

Route::group(['middleware' => 'auth'], function () {

    Route::get('/dashboard', [IndexController::class, 'dashboard'])->name('dashboard');

    Route::group(['middleware' => 'admin'], function () {

        Route::prefix('master')->name('master.')->group(function () {

            Route::prefix('vehicle')->name('vehicle.')->group(function () {
                Route::get('/', [VehicleController::class, 'index'])->name('index');
                Route::get('/getVehicles', [VehicleController::class, 'getVehicles'])->name('getvehicles');
                Route::post('/store', [VehicleController::class, 'store'])->name('store');
                Route::put('/update', [VehicleController::class, 'update'])->name('update');
                Route::delete('/delete', [VehicleController::class, 'destroy'])->name('delete');
            });

            Route::prefix('location')->name('location.')->group(function () {
                Route::get('/', [LocationController::class, 'index'])->name('index');
                Route::get('/getLocations', [LocationController::class, 'getLocations'])->name('getlocations');
                Route::post('/store', [LocationController::class, 'store'])->name('store');
                Route::put('/update', [LocationController::class, 'update'])->name('update');
                Route::delete('/delete', [LocationController::class, 'destroy'])->name('delete');
            });

        });

    });

    Route::group(['middleware' => 'admin_worker'], function () {

        Route::prefix('scan')->name('scan.')->group(function () {

            Route::get('/scan-in', [ScanInController::class, 'index'])->name('scanin');
            Route::get('get-allocates', [ScanInController::class, 'getAllocates'])->name('getallocates');
            Route::post('/scaning-in', [ScanInController::class, 'store'])->name('scanningin');

            Route::post('fetch-locations', [LocationController::class, 'fetchLocations'])->name('fetchlocations');
            Route::post('get-slots', [ScanInController::class, 'getSlots'])->name('getslots');
            Route::post('allocated-vehicle', [ScanInController::class, 'allocatedVehicle'])->name('allocatedvehcile');
            Route::get('/print/{allocate}', [ScanInController::class, 'getPrint'])->name('getprint');

            Route::get('/scan-out', [ScanOutController::class, 'index'])->name('scanout');
            Route::post('scanning-out', [ScanOutController::class, 'scanOut'])->name('scanningout');

        });

        Route::prefix('report')->name('report.')->group(function () {

            Route::get('', [ReportController::class, 'index'])->name('report');
            Route::post('report-view', [ReportController::class, 'store'])->name('reportview');
            Route::post('get-report', [ReportController::class, 'getReports'])->name('getreports');
        });

    });

    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});
