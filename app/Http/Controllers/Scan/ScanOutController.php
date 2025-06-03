<?php

namespace App\Http\Controllers\Scan;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanOutRequest;
use App\Models\Allocate;
use Exception;
use Illuminate\Http\Request;

class ScanOutController extends Controller
{
    public function index(){
        return view('scan.scanout');
    }

    public function scanOut(ScanOutRequest $request){

        $validated = $request->validate([
            'code' => 'required|string'
        ]);

        try{
            $allocate = Allocate::where('qrcode', $validated['code'])->first();

            if($allocate && $allocate->out_time != null){
                return response()->json([
                    'status' => 400,
                    'message' => 'Code Already Scanned'
                ], 400);
            }
            elseif($allocate){
                $allocate->update([
                    'status' => 'OUT',
                    'out_time' => now()->format('Y-m-d H:i:s'),
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => 'Scanned Out'
                ], 200);
            }else{
                return response()->json([
                    'status' => 404,
                    'message' => 'Code Not Found'
                ], 404);
            }
        }catch(Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'Something Went Wrong!: '.$e->getMessage()
            ], 500);
        }
    }
}
