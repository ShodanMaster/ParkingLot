<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class VehicleController extends Controller
{
    public function index(){
        return view('master.vehicle');
    }

    public function getVehicles(Request $request){

        $vehicles = Vehicle::all();

        if($request->ajax()){
            return DataTables::of($vehicles)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editVehicleModal" onclick="editVehicle('.$row->id.', \''.htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8').'\')">
                                Edit
                            </button>

                            <button type="button" class="btn btn-danger btn-sm deleteVehicle" onclick="deleteVehicle('.$row->id.')">Delete</button>

                        ';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request){
        // dd($request->all());

        $request->validate([
            'vehicleName' => 'required|string|max:255|unique:vehicles,name',
        ]);

        try{

            Vehicle::create([
                'name' => $request->vehicleName,
            ]);

            return response()->json([
                'message' => 'Vehicle Created Successfully',
            ], 200);

        }catch (Exception $e) {

            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $message = 'Duplicate entry found. Please ensure the data is unique.';
            } else {
                $message = 'Something Went Wrong. Please try again later. ' . $e->getMessage();
            }

            return response()->json([
                'message' => $message,
            ], 500);
        }
    }

    public function update(Request $request){
        // dd($request->all());
        try{
            $vehicle = Vehicle::find($request->id);

            if($vehicle){
                $vehicle->update(['name' => $request->vehicleName]);
                return response()->json([
                    'message' => 'Vehicle Created Successfully',
                ], 200);
            }

            return response()->json([
                'message' => 'Vehicle Not Found',
            ], 404);

        }catch (Exception $e) {
            return response()->json([
                'message' => 'Something Went Wrong. Please try again later. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request){
        // dd($request->all());
        try{
            $vehicle = Vehicle::findOrFail($request->id);
            $vehicle->delete();

            return response()->json([
                'message' => 'Vehicle Deleted Successfully',
            ], 200);

        }catch (Exception $e) {
            return response()->json([
                'message' => 'Something Went Wrong. Please try again later. ' . $e->getMessage(),
            ], 500);
        }
    }
}
