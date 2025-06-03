<?php

namespace App\Http\Controllers\Scan;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanInRequest;
use App\Models\Allocate;
use App\Models\Location;
use App\Models\QrCode as ModelsQrCode;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Yajra\DataTables\DataTables;

class ScanInController extends Controller
{
    public function index(){
        $vehicles = Vehicle::orderBy('name')->get();
        return view('scan.scanIn', compact( 'vehicles'));
    }

    public function getAllocates(Request $request){

        if ($request->ajax()) {
            $allocates = Allocate::with('location')->latest()->get();
            return DataTables::of($allocates)
                ->addIndexColumn()
                ->addColumn('location', function ($allocate) {
                    return $allocate->location->name;
                })
                ->addColumn('action', function ($allocate) {
                    $printRoute = route('scan.getprint', $allocate->id);
                    return '<a href="' . $printRoute . '" target="_blank"><button class="btn btn-info btn-sm">Get Print</button></a>';
                })
                ->editColumn('status', function ($allocate) {
                    return $allocate->status;
                })
                ->editColumn('in_time', function ($allocate) {
                    return $allocate->in_time;
                })
                ->editColumn('out_time', function ($allocate) {
                    return !empty($allocate->out_time) ? $allocate->out_time : 'not yet out';
                })
                ->editColumn('qrcode', function ($allocate) {
                    return $allocate->qrcode;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }


    public function store(ScanInRequest $request){

        $validated = $request->validated();

        try {

            $lastAllocate = Allocate::where('vehicle_number', $validated['vehicleNumber'])
                    ->whereNull('out_time')
                    ->latest('created_at')
                    ->first();

            if ($lastAllocate) {
                return response()->json([
                    'status' => 409,
                    'message' => 'This vehicle is already allocated and has not checked out.',
                ]);
            }

            DB::beginTransaction();

            $allocate = Allocate::create([
                'location_id' => $validated['locationId'],
                'vehicle_number' => $validated['vehicleNumber'],
                'qrcode' => Allocate::codeGenerator($validated['locationId']),
            ]);

            $qrCode = QrCode::format('png')->size(200)->generate($allocate->qrcode);

            $fileName = 'qr_code_' . $allocate->qrcode . '.png';
            Storage::disk('public')->put('qr_codes/' . $fileName, $qrCode);
            $qrCodeUrl = Storage::url('qr_codes/' . $fileName);

            ModelsQrCode::create([
                'allocate_id' => $allocate->id,
                'path' => $qrCodeUrl,
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Allocated successfully',
                'print_url' => route('scan.getprint', ['allocate' => $allocate]),
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong! ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSlots(Request $request){

        $request->validate([
            'locationId' => 'required|integer|exists:locations,id',
        ]);

        try {
            $location = Location::findOrFail($request->locationId);
            $totalSlots = $location->slot;

            $occupiedSlots = Allocate::where('location_id', $request->locationId)
                ->whereNull('out_time')
                ->count();

            $availableSlots = $totalSlots - $occupiedSlots;

            return response()->json([
                'status' => 200,
                'message' => 'Slots found',
                'slots' => [
                    'total_slots' => $totalSlots,
                    'occupied_slots' => $occupiedSlots,
                    'available_slots' => $availableSlots,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function allocatedVehicle(ScanInRequest $request){
        $validated = $request->validated();
        try{
            DB::beginTransaction();

            $lastAllocate = Allocate::where('vehicle_number', $validated['vehicleNumber'])
                    ->whereNull('out_time')
                    ->latest('created_at')
                    ->first();

            if ($lastAllocate) {
                $lastAllocate->update([
                    'status' => 'OUT',
                    'out_time' => now()->format('Y-m-d H:i:s'),
                ]);
            }
            $allocate = Allocate::create([
                            'location_id' => $validated['locationId'],
                            'vehicle_number' => $validated['vehicleNumber'],
                            'qrcode' => Allocate::codeGenerator($validated['locationId']),
                        ]);

            $qrCode = QrCode::format('png')->size(200)->generate($allocate->qrcode);

            $fileName = 'qr_code_' . $allocate->qrcode . '.png';
            Storage::disk('public')->put('qr_codes/' . $fileName, $qrCode);
            $qrCodeUrl = Storage::url('qr_codes/' . $fileName);

            ModelsQrCode::create([
                'allocate_id' => $allocate->id,
                'path' => $qrCodeUrl,
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Allocated successfully',
                'print_url' => route('scan.getprint', ['allocate' => $allocate]),
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPrint(Allocate $allocate){

        if (!$allocate->QRCode) {
            $qrCode = QrCode::format('png')->size(200)->generate($allocate->qrcode);

            $fileName = 'qr_code_' . $allocate->qrcode . '.png';
            $path = Storage::disk('public')->put('qr_codes/' . $fileName, $qrCode);
            $qrCodeUrl = Storage::url('qr_codes/' . $fileName);

            ModelsQRCode::create([
                'allocate_id' => $allocate->id,
                'path' => $qrCodeUrl,
            ]);
        } else {
            $filePath = str_replace('/storage', '', $allocate->QRCode->path);

            if (!Storage::disk('public')->exists($filePath)) {
                $qrCode = QrCode::format('png')->size(200)->generate($allocate->qrcode);

                $fileName = 'qr_code_' . $allocate->qrcode . '.png';
                $path = Storage::disk('public')->put('qr_codes/' . $fileName, $qrCode);
                $qrCodeUrl = Storage::url('qr_codes/' . $fileName);

                $allocate->QRCode->update([
                    'path' => $qrCodeUrl,
                ]);
            }
        }

        return view('getPrint', compact('allocate'));
    }

}
