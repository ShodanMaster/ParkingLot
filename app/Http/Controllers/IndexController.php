<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index(){
        $vehicles = Vehicle::all();
        return view('index', compact('vehicles'));
    }
    public function dashboard()
    {
        $vehicles = Vehicle::all();
        return view('dashboard', compact('vehicles'));
    }

    public function locations(Request $request){

        try {
            $vehicleId = $request->vehicleId;

            $query = Location::with('allocates');

            if ($vehicleId) {
                $query->where('vehicle_id', $vehicleId);
            }

            $locations = $query->get();

            $data = $locations->map(function ($location) {
                $allocated = $location->allocates->filter(function ($allocate) {
                    return is_null($allocate->out_time);
                })->count();

                return [
                    'id' => $location->id,
                    'name' => $location->name,
                    'allocated' => $allocated,
                    'available' => $location->slot - $allocated,
                    'totalSlot' => $location->slot
                ];
            });

            return response()->json([
                'status' => 200,
                'message' => 'Locations Found',
                'data' => $data
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
