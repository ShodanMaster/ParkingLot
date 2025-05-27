<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class LocationController extends Controller
{
    public function index(){
        $vehicles = Vehicle::all();
        return view('master.location', compact('vehicles'));
    }

    public function getLocations(Request $request){
        $locations = Location::with('vehicle')->get();

        $data = $locations->map(function ($l) {
            return [
                'id' => $l->id,
                'vehicle' => $l->vehicle->name,
                'location' => $l->name,
            ];
        });

        if ($request->ajax()) {
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editLocationModal" onclick="editLocation('
                            . $row['id'] . ', \''
                            . htmlspecialchars($row['vehicle'], ENT_QUOTES, 'UTF-8') . '\', \''
                            . htmlspecialchars($row['location'], ENT_QUOTES, 'UTF-8') . '\')">Edit</button>

                        <button type="button" class="btn btn-danger btn-sm deleteLocation" onclick="deleteLocation(' . $row['id'] . ')">
                            Delete
                        </button>
                    ';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request){
        $request->validate([
            'vehicleId' => 'required|exists:vehicles,id',
            'locationName' => 'required|string|max:255|unique:locations,name',
        ]);

        try {
            Location::create([
                'vehicle_id' => $request->vehicleId,
                'name' => $request->locationName,
            ]);

            return response()->json([
                'message' => 'Location Created Successfully',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error creating location: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request){
        $request->validate([
            'id' => 'required|exists:locations,id',
            'vehicleId' => 'required|exists:vehicles,id',
            'locationName' => 'required|string|max:255|unique:locations,name,'.$request->locationId,
        ]);

        try {

            $location = Location::findOrFail($request->id);
            $location->update([
                'vehicle_id' => $request->vehicleId,
                'name' => $request->locationName,
            ]);

            return response()->json([
                'message' => 'Location Updated Successfully',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error updating location: '.$e->getMessage(),
            ], 500);
        }
    }
    public function destroy(Request $request){
        $request->validate([
            'id' => 'required|exists:locations,id',
        ]);

        try {
            $location = Location::findOrFail($request->id);
            $location->delete();

            return response()->json([
                'message' => 'Location Deleted Successfully',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error deleting location: '.$e->getMessage(),
            ], 500);
        }
    }
}
