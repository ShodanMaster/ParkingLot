<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Collection;

class LocationService
{
    public function getAllWithVehicles(): Collection
    {
        return Location::with('vehicle')->get();
    }

    public function create(array $data): Location
    {
        return Location::create([
            'vehicle_id' => $data['vehicleId'],
            'name' => $data['locationName'],
        ]);
    }

    public function update(int $id, array $data): Location
    {
        $location = Location::findOrFail($id);
        $location->update([
            'vehicle_id' => $data['vehicleId'],
            'name' => $data['locationName'],
            'slot' => $data['slot'] ?? null,
        ]);
        return $location;
    }

    public function delete(int $id): void
    {
        Location::findOrFail($id)->delete();
    }

    public function getByVehicle(int $vehicleId): Collection
    {
        return Location::where('vehicle_id', $vehicleId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }
}
