<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Services\LocationService;
use App\Services\VehicleService;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LocationController extends Controller
{
    protected LocationService $locationService;
    protected VehicleService $vehicleService;

    public function __construct(LocationService $locationService, VehicleService $vehicleService)
    {
        $this->locationService = $locationService;
        $this->vehicleService = $vehicleService;
    }

    public function index()
    {
        $vehicles = $this->vehicleService->getAllVehicles();
        return view('master.location', compact('vehicles'));
    }

    public function getLocations(Request $request)
    {
        $locations = $this->locationService->getAllWithVehicles();

        $data = $locations->map(function ($l) {
            return [
                'id' => $l->id,
                'vehicle' => $l->vehicle->name ?? '',
                'location' => $l->name,
                'slot' => $l->slot,
            ];
        });

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editLocationModal" onclick="editLocation('
                        . $row['id'] . ', '.$row['slot'].',
                        \'' . htmlspecialchars($row['vehicle'], ENT_QUOTES, 'UTF-8') . '\',
                        \'' . htmlspecialchars($row['location'], ENT_QUOTES, 'UTF-8') . '\')">Edit</button>

                        <button type="button" class="btn btn-danger btn-sm deleteLocation" onclick="deleteLocation(' . $row['id'] . ')">Delete</button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicleId' => 'required|exists:vehicles,id',
            'locationName' => 'required|string|max:255|unique:locations,name',
        ]);

        try {
            $this->locationService->create($request->only(['vehicleId', 'locationName']));

            return response()->json(['message' => 'Location Created Successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error creating location: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:locations,id',
            'vehicleId' => 'required|exists:vehicles,id',
            'locationName' => 'required|string|max:255|unique:locations,name,' . $request->id,
            'slot' => 'nullable|integer|max:100',
        ]);

        try {
            $this->locationService->update($request->id, $request->only(['vehicleId', 'locationName', 'slot']));

            return response()->json(['message' => 'Location Updated Successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error updating location: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:locations,id',
        ]);

        try {
            $this->locationService->delete($request->id);

            return response()->json(['message' => 'Location Deleted Successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error deleting location: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fetchLocations(Request $request)
    {
        try {
            $locations = $this->locationService->getByVehicle($request->vehicleId);

            return response()->json([
                'status' => 200,
                'message' => 'Locations Found',
                'locations' => $locations,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something Went Wrong ' . $e->getMessage(),
            ]);
        }
    }
}
