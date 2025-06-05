<?php

namespace App\Services;

use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class VehicleService
{
    protected string $allVehiclesKey = 'vehicles_all';
    protected string $vehiclesOrderedKey = 'vehicles_ordered';

    /**
     * Get all vehicles without specific order.
     */
    public function getAllVehicles(): Collection
    {
        return Cache::remember($this->allVehiclesKey, now()->addMinutes(10), function () {
            return Vehicle::select(['id', 'name'])->get();
        });
    }

    /**
     * Get all vehicles ordered by name.
     */
    public function getAllOrderedByName(): Collection
    {
        return Cache::remember($this->vehiclesOrderedKey, now()->addMinutes(10), function () {
            return Vehicle::select(['id', 'name'])->orderBy('name')->get();
        });
    }

    /**
     * Create a new vehicle and clear cache.
     */
    public function createVehicle(string $name): Vehicle
    {
        $vehicle = Vehicle::create(['name' => $name]);
        $this->clearCache();

        return $vehicle;
    }

    /**
     * Update an existing vehicle by ID and clear cache.
     */
    public function updateVehicle(int $id, string $name): Vehicle
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update(['name' => $name]);

        $this->clearCache();

        return $vehicle;
    }

    /**
     * Delete a vehicle by ID and clear cache.
     */
    public function deleteVehicle(int $id): void
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->delete();

        $this->clearCache();
    }

    /**
     * Clear relevant vehicle caches.
     */
    private function clearCache(): void
    {
        Cache::forget($this->allVehiclesKey);
        Cache::forget($this->vehiclesOrderedKey);
    }
}
