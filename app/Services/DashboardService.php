<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

class DashboardService
{
    public function getAllVehicles(): Collection
    {
        return Vehicle::all();
    }

    public function getLocationSummaries(?int $vehicleId = null): Collection
    {
        $query = Location::with('allocates');

        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        $locations = $query->get();

        return $locations->map(function ($location) {
            $allocated = $location->allocates->whereNull('out_time')->count();

            return [
                'id' => $location->id,
                'name' => $location->name,
                'allocated' => $allocated,
                'available' => $location->slot - $allocated,
                'totalSlot' => $location->slot,
            ];
        });
    }
}
