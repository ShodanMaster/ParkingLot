<?php

namespace App\Services;

use App\Models\Vehicle;

class VehicleService
{
    public function getAllVehicles()
    {
        return Vehicle::all();
    }

    public function getAllOrderedByName()
    {
        return Vehicle::orderBy('name')->get();
    }

    public function createVehicle(string $name): Vehicle
    {
        return Vehicle::create(['name' => $name]);
    }

    public function updateVehicle(int $id, string $name): ?Vehicle
    {
        $vehicle = Vehicle::find($id);
        if ($vehicle) {
            $vehicle->update(['name' => $name]);
        }
        return $vehicle;
    }

    public function deleteVehicle(int $id): void
    {
        Vehicle::findOrFail($id)->delete();
    }
}
