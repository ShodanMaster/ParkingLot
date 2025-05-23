<?php

use App\Http\Controllers\IndexController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Master\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'index'])->name('login');

Route::post('/login-in', [LoginController::class, 'logingIn'])->name('logingin');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/dashboard', [IndexController::class, 'index'])->name('dashboard');

    Route::prefix('master')->name('master.')->group(function () {
        Route::get('/vehicle', [VehicleController::class, 'index'])->name('vehicle');
        Route::get('/vehicle/create', [VehicleController::class, 'create'])->name('vehicle.create');
        Route::post('/vehicle/store', [VehicleController::class, 'store'])->name('vehicle.store');
        Route::get('/vehicle/edit/{id}', [VehicleController::class, 'edit'])->name('vehicle.edit');
        Route::post('/vehicle/update/{id}', [VehicleController::class, 'update'])->name('vehicle.update');
        Route::get('/vehicle/delete/{id}', [VehicleController::class, 'destroy'])->name('vehicle.delete');
    });


    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});
