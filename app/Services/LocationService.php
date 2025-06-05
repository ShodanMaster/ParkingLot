<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LocationService
{
    /**
     * Get all locations with their associated vehicles.
     */
    public function getAllWithVehicles(): Collection
    {
        return Cache::remember('locations_with_vehicles', now()->addMinutes(5), function () {
            return Location::with(['vehicle:id,name'])
                ->select('id', 'name', 'vehicle_id', 'slot')
                ->get();
        });
    }

    /**
     * Create a new location.
     */
    public function create(array $data): Location
    {
        $location = Location::create([
            'vehicle_id' => $data['vehicleId'],
            'name' => $data['locationName'],
        ]);

        $this->clearCache($data['vehicleId']);

        return $location;
    }

    /**
     * Update a location by ID.
     */
    public function update(int $locationId, array $data): Location
    {
        $location = Location::findOrFail($locationId);

        $location->update([
            'vehicle_id' => $data['vehicleId'],
            'name' => $data['locationName'],
            'slot' => $data['slot'] ?? null,
        ]);

        $this->clearCache($location->vehicle_id);

        return $location;
    }

    /**
     * Delete a location by ID.
     */
    public function delete(int $locationId): void
    {
        $location = Location::findOrFail($locationId);
        $vehicleId = $location->vehicle_id;
        $location->delete();

        $this->clearCache($vehicleId);
    }

    /**
     * Get locations for a specific vehicle.
     */
    public function getByVehicle(int $vehicleId): Collection
    {
        return Cache::remember("locations_by_vehicle_{$vehicleId}", now()->addMinutes(5), function () use ($vehicleId) {
            return Location::where('vehicle_id', $vehicleId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Clear location-related cache.
     */
    private function clearCache(int $vehicleId): void
    {
        Cache::forget('locations_with_vehicles');
        Cache::forget("locations_by_vehicle_{$vehicleId}");
    }
}
