<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Allocate;
use App\Models\Location;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Composer;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{
    public function index(){
        $locations = Location::all();
        return view('report.report', compact('locations'));
    }

    public function store(Request $request){
        // dd($request->all());
        try{
            $fromDate = $request->from_date;
            $toDate = $request->to_date;
            $qrcode = $request->qrcode;
            $vehicleNumber = $request->vehicle_number;
            $location = $request->location;
            $status = $request->status;
            $inTimeFrom = $request->inTimeFrom;
            $inTimeTo = $request->inTimeTo;
            $outTimeFrom = $request->outTimeFrom;
            $outTimeTo = $request->outTimeTo;



            if($request->action == 1){
                return view('report.reportview', compact('fromDate', 'toDate', 'qrcode', 'vehicleNumber', 'inTimeFrom', 'location', 'status','inTimeTo','outTimeFrom', 'outTimeTo'));
            }
        } catch(Exception $e){
            return response()->json([
                'status' => 500,
                'message' => 'Something Went Wrong! '. $e->getMessage()
            ]);
        }
    }

    public function getReports(Request $request){
        $data = $this->allocateData($request);

        return  DataTables::of($data)
                    ->addIndexColumn()
                    ->make(true);
    }

    protected function allocateData($filter){

        $query = Allocate::query();

        if ($filter->filled('qrcode')) {
            $query->where('qrcode', $filter->input('qrcode'));
        }

        if ($filter->filled('vehicleNumber')) {
            $query->where('vehicle_number', $filter->input('vehicleNumber'));
        }

        if ($filter->filled('location')) {
            $query->where('location_id', $filter->input('location'));
        }

        if ($filter->filled('status')) {
            $query->where('status', $filter->input('status'));
        }

        if ($filter->filled('fromDate')) {
            $query->whereDate('created_at', '>=', $filter->input('fromDate'));
        }

        if ($filter->filled('toDate')) {
            $query->whereDate('created_at', '<=', $filter->input('toDate'));
        }

        if ($filter->filled('inTimeFrom')) {
            $query->whereTime('in_time', '>=', $filter->input('inTimeFrom'));
        }

        if ($filter->filled('inTimeTo')) {
            $query->whereTime('in_time', '<=', $filter->input('inTimeTo'));
        }

        if ($filter->filled('outTimeFrom')) {
            $query->whereTime('out_time', '>=', $filter->input('outTimeFrom'));
        }

        if ($filter->filled('outTimeTo')) {
            $query->whereTime('out_time', '<=', $filter->input('outTimeTo'));
        }

        $result = $query->with('location')->get();

        return $result->map(function ($r, $index) {
            return [
                'DT_RowIndex'     => $index + 1,
                'vehicle_number'  => $r->vehicle_number,
                'qrcode'         => $r->qrcode,
                'location'        => $r->location->name,
                'in_time'         => $r->in_time,
                'out_time'        => $r->out_time ?? 'not yet out' ,
                'status'          => $r->status,
            ];
        });

    }

}
