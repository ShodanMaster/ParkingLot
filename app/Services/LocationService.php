<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LocationService
{
    public function getAllWithVehicles(): Collection
    {
        return Cache::remember('locations_with_vehicles', now()->addMinutes(5), function () {
            return Location::with('vehicle')->get();
        });
    }

    public function create(array $data): Location
    {
        $location = Location::create([
            'vehicle_id' => $data['vehicleId'],
            'name' => $data['locationName'],
        ]);

        $this->clearCache($data['vehicleId']);

        return $location;
    }

    public function update(int $id, array $data): Location
    {
        $location = Location::findOrFail($id);
        $location->update([
            'vehicle_id' => $data['vehicleId'],
            'name' => $data['locationName'],
            'slot' => $data['slot'] ?? null,
        ]);

        $this->clearCache($id);


        return $location;
    }

    public function delete(int $id): void
    {
        $location = Location::findOrFail($id);
        $vehicleId = $location->vehicle_id;
        $location->delete();

        $this->clearCache($vehicleId);

    }

    public function getByVehicle(int $vehicleId): Collection
    {
        return Cache::remember("locations_by_vehicle_{$vehicleId}", now()->addMinutes(5), function () use ($vehicleId) {
            return Location::where('vehicle_id', $vehicleId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        });
    }

    private function clearCache($id): void{
        Cache::forget('locations_with_vehicles');
        Cache::forget('locations_by_vehicle_'.$id);
    }
}
