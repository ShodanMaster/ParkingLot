<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function getAllVehicles(): Collection
    {
        return Cache::remember('dashboard_all_vehicles', now()->addMinutes(10), function () {
            return Vehicle::select(['id', 'name'])->get();
        });
    }

    public function getLocationSummaries(?int $vehicleId = null): Collection
    {
        $cacheKey = $vehicleId ? "dashboard_locations_vehicle_{$vehicleId}" : 'dashboard_locations_all';

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($vehicleId) {
            $query = Location::with(['allocates' => function ($q) {
                $q->select('id', 'location_id', 'out_time');
            }])->select('id', 'name', 'slot', 'vehicle_id');


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
        });
    }
}
