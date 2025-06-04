<?php

namespace App\Http\Controllers\Scan;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanOutRequest;
use App\Models\Allocate;
use App\Services\ScanOutService;
use Exception;
use Illuminate\Http\Request;

class ScanOutController extends Controller
{
    public function __construct(protected ScanOutService $scanOutService) {}

    public function index(){
        return view('scan.scanout');
    }

    public function store(ScanOutRequest $request){

        try {
            $response = $this->scanOutService->handle($request->input('code'));

            return response()->json([
                'status' => $response['status'],
                'message' => $response['message'],
            ], $response['status']);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something Went Wrong! ' . $e->getMessage()
            ], 500);
        }
    }
}
