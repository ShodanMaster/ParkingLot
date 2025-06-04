<?php

namespace App\Services;

use App\Models\Allocate;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Get all allocations with optional filters.
     *
     * @param array $filters
     *     Possible keys: qrcode, vehicle_number, location_id, status,
     *     from_date, to_date, inTimeFrom, inTimeTo, outTimeFrom, outTimeTo
     * @return Collection
     */
    public function getAllocations(array $filters): Collection
    {
        $query = Allocate::query();

        if (!empty($filters['qrcode'])) {
            $query->where('qrcode', $filters['qrcode']);
        }

        if (!empty($filters['vehicle_number'])) {
            $query->where('vehicle_number', $filters['vehicle_number']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (!empty($filters['inTimeFrom'])) {
            $query->whereTime('in_time', '>=', $filters['inTimeFrom']);
        }

        if (!empty($filters['inTimeTo'])) {
            $query->whereTime('in_time', '<=', $filters['inTimeTo']);
        }

        if (!empty($filters['outTimeFrom'])) {
            $query->whereTime('out_time', '>=', $filters['outTimeFrom']);
        }

        if (!empty($filters['outTimeTo'])) {
            $query->whereTime('out_time', '<=', $filters['outTimeTo']);
        }

        return $query->with('location')->get();
    }

    /**
     * Get summarized report like total IN/OUT count and optionally revenue.
     *
     * @param array $filters
     * @return array
     */
    public function getSummary(array $filters): array
    {
        $allocations = $this->getAllocations($filters);

        $inCount = $allocations->whereNull('out_time')->count();
        $outCount = $allocations->whereNotNull('out_time')->count();

        // Optional: calculate fees/revenue in future
        // $revenue = $allocations->sum('fee');

        return [
            'total' => $allocations->count(),
            'in_count' => $inCount,
            'out_count' => $outCount,
            // 'revenue' => $revenue,
        ];
    }

    /**
     * Format allocations for frontend DataTable.
     *
     * @param Collection $allocations
     * @return Collection
     */
    public function formatForTable(Collection $allocations): array{
        
        return $allocations->map(function ($r, $index) {
            return [
                'DT_RowIndex'    => $index + 1,
                'vehicle_number' => $r->vehicle_number,
                'qrcode'         => $r->qrcode,
                'location'       => $r->location->name ?? 'N/A',
                'in_time'        => $r->in_time,
                'out_time'       => $r->out_time ?? 'Not yet out',
                'status'         => $r->status,
            ];
        })->toArray();
    }

}
