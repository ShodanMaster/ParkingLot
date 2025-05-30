<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
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

class AllocateController extends Controller
{
    public function index(){
        $vehicles = Vehicle::orderBy('name')->get();
        return view('transaction.allocate', compact( 'vehicles'));
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
                    $printRoute = route('allocate.getprint', $allocate->id);
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


    public function store(Request $request){

        $validated = $request->validate([
            'vehicleNumber' => 'required|string',
            'vehicleId' => 'required|integer|exists:vehicles,id',
            'locationId' => 'required|integer|exists:locations,id',
        ]);

        try{

            $totalSlots = Location::findOrFail(($validated['locationId']))->slot;

            $slotsLeft = Allocate::where('location_id', $validated['locationId'])
                                ->whereNull('out_time')
                                ->count();

            if ($slotsLeft >= $totalSlots) {
                return response()->json([
                    'status' => 400,
                    'message' => 'No available slots at this location'
                ], 400);
            }

            DB::beginTransaction();

            $allocate = Allocate::create([
                'location_id' => $validated['locationId'],
                'vehicle_number' => $validated['vehicleNumber'],
                'qrcode' => Allocate::codeGenerator($validated['locationId']),
            ]);
            // dd($allocate);
            $qrCode = QrCode::format('png')->size(200)->generate($allocate->qrcode);

            $fileName = 'qr_code_' . $allocate->qrcode . '.png';
            $path = Storage::disk('public')->put('qr_codes/' . $fileName, $qrCode);
            $qrCodeUrl = Storage::url('qr_codes/' . $fileName);

            ModelsQrCode::create([
                'allocate_id' => $allocate->id,
                'path' => $qrCodeUrl,
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Allocated successfully',
                'print_url' => route('allocate.getprint', ['allocate' => $allocate]),
            ]);

        }catch(Exception $e){
            DB::rollback();
            return response()->json([
                'status' => 500,
                'message' => 'Something Went Wrong!: '.$e->getMessage()
            ], 500);
        }
    }

    public function getSlots(Request $request){
        // dd($request->all());

        try{
            $totalSlots = Location::findOrFail(($request->locationId))->slot;

            $slotsLeft = Allocate::where('location_id', $request->locationId)
                                ->whereNull('out_time')
                                ->count();

            return response()->json([
                'status' => 200,
                'message' => 'Slots Found',
                'slots' => [
                    'total_slots' => $totalSlots,
                    'slots_left' => $slotsLeft,
                ]
            ]);
        }catch(Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'Something Went Wrong!: '.$e->getMessage()
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
