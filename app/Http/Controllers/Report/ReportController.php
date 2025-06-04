<?php

namespace App\Http\Controllers\Report;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function index(){
        $locations = Location::all();
        return view('report.report', compact('locations'));
    }

    public function store(Request $request){
        try {
            $filters = $request->only([
                'from_date', 'to_date', 'qrcode', 'vehicle_number',
                'location', 'status', 'inTimeFrom', 'inTimeTo', 'outTimeFrom', 'outTimeTo'
            ]);

            $serviceData = $this->reportService->getAllocations($filters);
            $data = $this->reportService->formatForTable($serviceData);

            if ($request->action == 1) {
                return view('report.reportview', compact('filters'));
            } elseif ($request->action == 2) {
                return Excel::download(new ReportExport($data), 'parking_report.xlsx');
            } elseif ($request->action == 3) {
                // dd($data);
                return $this->reportPdf($data);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Something Went Wrong! ' . $e->getMessage()
            ]);
        }
    }


    public function getReports(Request $request){
        // dd($request->all());
        $filters = $request->all();
        $data = $this->reportService->formatForTable(
            $this->reportService->getAllocations($filters)
        );
        // dd($data);
        return  DataTables::of($data)
                    ->addIndexColumn()
                    ->make(true);
    }

    protected function reportPdf($data){

        $pdf = Pdf::loadView('report.reportpdf', ['data' => $data]);
        return $pdf->download('reportpdf.pdf');
    }
}
