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
            return Vehicle::select(['id', 'name'])->get();
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

        $this->clearCache();

        return $vehicle;
    }

    public function updateVehicle(int $id, string $name): ?Vehicle
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update(['name' => $name]);

        $this->clearCache();

        return $vehicle;
    }

    public function deleteVehicle(int $id): void
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        $this->clearCache();
    }

    private function clearCache(): void
    {
        Cache::forget($this->allVehiclesKey);
        Cache::forget($this->vehiclesOrderedKey);
    }

}
