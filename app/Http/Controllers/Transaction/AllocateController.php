<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Allocate;
use App\Models\Location;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\Request;

class AllocateController extends Controller
{
    public function index(){
        $vehicles = Vehicle::orderBy('name')->get();
        $allocates = Allocate::limit(5)->latest()->get();
        return view('transaction.allocate', compact( 'vehicles', 'allocates'));
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
