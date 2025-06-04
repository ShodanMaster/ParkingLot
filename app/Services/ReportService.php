<?php

namespace App\Services;

use App\Models\Allocate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ReportService
{
    public function getAllocations(array $filters): Collection
    {
        $cacheKey = $this->buildCacheKey('allocations', $filters);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters) {
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
        });
    }

    public function getSummary(array $filters): array
    {
        $cacheKey = $this->buildCacheKey('summary', $filters);

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters) {
            $allocations = $this->getAllocations($filters);

            $inCount = $allocations->whereNull('out_time')->count();
            $outCount = $allocations->whereNotNull('out_time')->count();

            return [
                'total' => $allocations->count(),
                'in_count' => $inCount,
                'out_count' => $outCount,
            ];
        });
    }

    public function formatForTable(Collection $allocations): array
    {
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

    protected function buildCacheKey(string $prefix, array $filters): string
    {
        ksort($filters);
        return $prefix . '_' . md5(json_encode($filters));
    }
}
