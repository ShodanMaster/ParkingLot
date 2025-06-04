<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Support\Facades\Cache;

class VehicleService
{
    protected string $allVehiclesKey = 'vehicles_all';
    protected string $vehiclesOrderedKey = 'vehicles_ordered';

    public function getAllVehicles()
    {
        return Cache::remember($this->allVehiclesKey, now()->addMinutes(10), function () {
            return Vehicle::all();
        });
    }

    public function getAllOrderedByName()
    {
        return Cache::remember($this->vehiclesOrderedKey, now()->addMinutes(10), function () {
            return Vehicle::orderBy('name')->get();
        });
    }

    public function createVehicle(string $name): Vehicle
    {
        $vehicle =  Vehicle::create(['name' => $name]);

        Cache::forget($this->allVehiclesKey);
        Cache::forget($this->vehiclesOrderedKey);

        return $vehicle;
    }

    public function updateVehicle(int $id, string $name): ?Vehicle
    {
        $vehicle = Vehicle::find($id);
        if ($vehicle) {
            $vehicle->update(['name' => $name]);

            Cache::forget($this->allVehiclesKey);
            Cache::forget($this->vehiclesOrderedKey);
        }

        return $vehicle;
    }

    public function deleteVehicle(int $id): void
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        Cache::forget($this->allVehiclesKey);
        Cache::forget($this->vehiclesOrderedKey);
    }
}
