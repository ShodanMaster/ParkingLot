<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\VehicleService;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class VehicleController extends Controller
{
    protected VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function index()
    {
        return view('master.vehicle');
    }

    public function getVehicles(Request $request)
    {
        $vehicles = $this->vehicleService->getAllVehicles();

        if ($request->ajax()) {
            return DataTables::of($vehicles)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '
                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editVehicleModal" onclick="editVehicle(' . $row->id . ', \'' . htmlspecialchars($row->name, ENT_QUOTES, 'UTF-8') . '\')">
                            Edit
                        </button>
                        <button type="button" class="btn btn-danger btn-sm deleteVehicle" onclick="deleteVehicle(' . $row->id . ')">Delete</button>
                    ';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicleName' => 'required|string|max:255|unique:vehicles,name',
        ]);

        try {
            $this->vehicleService->createVehicle($request->vehicleName);

            return response()->json([
                'message' => 'Vehicle Created Successfully',
            ]);
        } catch (Exception $e) {
            $message = str_contains($e->getMessage(), 'Duplicate entry')
                ? 'Duplicate entry found. Please ensure the data is unique.'
                : 'Something Went Wrong. ' . $e->getMessage();

            return response()->json(['message' => $message], 500);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:vehicles,id',
            'vehicleName' => 'required|string|max:255|unique:vehicles,name,' . $request->id,
        ]);

        try {
            $vehicle = $this->vehicleService->updateVehicle($request->id, $request->vehicleName);

            if ($vehicle) {
                return response()->json(['message' => 'Vehicle Updated Successfully']);
            }

            return response()->json(['message' => 'Vehicle Not Found'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something Went Wrong. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $this->vehicleService->deleteVehicle($request->id);

            return response()->json(['message' => 'Vehicle Deleted Successfully']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something Went Wrong. ' . $e->getMessage(),
            ], 500);
        }
    }
}
