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

class AllocateController extends Controller
{
    public function index(){
        $vehicles = Vehicle::orderBy('name')->get();
        $allocates = Allocate::limit(5)->latest()->get();
        return view('transaction.allocate', compact( 'vehicles', 'allocates'));
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

}
