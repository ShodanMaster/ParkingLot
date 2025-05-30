<?php

use App\Http\Controllers\IndexController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\VehicleController;
use App\Http\Controllers\Transaction\AllocateController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'index'])->name('login');

Route::post('/login-in', [LoginController::class, 'logingIn'])->name('logingin');

Route::group(['middleware' => 'auth'], function () {

    Route::get('/dashboard', [IndexController::class, 'index'])->name('dashboard');

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

    Route::prefix('allocate')->name('allocate.')->group(function(){
        Route::get('/', [AllocateController::class, 'index'])->name('index');
        Route::post('fetch-locations', [LocationController::class, 'fetchLocations'])->name('fetchlocations');
    });

    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});
