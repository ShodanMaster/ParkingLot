<?php

namespace App\Http\Controllers\Scan;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanInRequest;
use App\Models\Allocate;
use App\Services\ScanInService;
use App\Services\VehicleService;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ScanInController extends Controller
{
    protected ScanInService $scanInService;
    protected VehicleService $vehicleService;

    public function __construct(ScanInService $scanInService, VehicleService $vehicleService)
    {
        $this->scanInService = $scanInService;
        $this->vehicleService = $vehicleService;
    }

    public function index()
    {
        $vehicles = $this->vehicleService->getAllVehicles();
        return view('scan.scanIn', compact('vehicles'));
    }

    public function getAllocates(Request $request)
    {
        if ($request->ajax()) {
            $allocates = $this->scanInService->getAllAllocatesWithLocation();
            return DataTables::of($allocates)
                ->addIndexColumn()
                ->addColumn('location', fn($allocate) => $allocate->location->name)
                ->addColumn('action', function ($allocate) {
                    $printRoute = route('scan.getprint', $allocate->id);
                    return '<a href="' . $printRoute . '" target="_blank"><button class="btn btn-info btn-sm">Get Print</button></a>';
                })
                ->editColumn('status', fn($allocate) => $allocate->status)
                ->editColumn('in_time', fn($allocate) => $allocate->in_time)
                ->editColumn('out_time', fn($allocate) => $allocate->out_time ?? 'not yet out')
                ->editColumn('qrcode', fn($allocate) => $allocate->qrcode)
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function store(ScanInRequest $request)
    {
        $validated = $request->validated();

        if ($this->scanInService->isVehicleAlreadyAllocated($validated['vehicleNumber'])) {
            return response()->json([
                'status' => 409,
                'message' => 'This vehicle is already allocated and has not checked out.',
            ]);
        }

        try {
            $allocate = $this->scanInService->allocateWithQr($validated);

            return response()->json([
                'status' => 200,
                'message' => 'Allocated successfully',
                'print_url' => route('scan.getprint', ['allocate' => $allocate]),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong! ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSlots(Request $request)
    {
        $request->validate(['locationId' => 'required|integer|exists:locations,id']);

        try {
            $slots = $this->scanInService->getSlotStatus($request->locationId);

            return response()->json([
                'status' => 200,
                'message' => 'Slots found',
                'slots' => [
                    'total_slots' => $slots['totalSlots'],
                    'occupied_slots' => $slots['occupiedSlots'],
                    'available_slots' => $slots['availableSlots'],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function allocatedVehicle(ScanInRequest $request)
    {
        $validated = $request->validated();

        try {
            $allocate = $this->scanInService->allocateVehicleWithCheckOut($validated);

            return response()->json([
                'message' => 'Allocated successfully',
                'print_url' => route('scan.getprint', ['allocate' => $allocate]),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getPrint(Allocate $allocate)
    {
        try {
            $qrCodeUrl = $this->scanInService->getOrCreateQrCodeForAllocate($allocate);
        } catch (Exception $e) {
            return redirect()->back()->withErrors('Failed to generate QR code');
        }

        return view('getPrint', compact('allocate'));
    }
}
