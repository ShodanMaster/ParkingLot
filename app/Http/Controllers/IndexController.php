<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Exception;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index()
    {
        $vehicles = $this->dashboardService->getAllVehicles();
        return view('index', compact('vehicles'));
    }

    public function dashboard()
    {
        $vehicles = $this->dashboardService->getAllVehicles();
        return view('dashboard', compact('vehicles'));
    }

    public function locations(Request $request)
    {
        try {
            $vehicleId = $request->vehicleId;
            $data = $this->dashboardService->getLocationSummaries($vehicleId);

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
