<?php

namespace App\Services;

use App\Models\Allocate;
use Illuminate\Support\Facades\Cache;

class ScanOutService
{
    /**
     * Handles scan out process.
     *
     * @param string $code
     * @return array
     */
    public function handle(string $code): array
    {
        $allocate = Allocate::select('id', 'location_id', 'out_time')
            ->where('qrcode', $code)
            ->first();

        if (! $allocate) {
            return [
                'status' => 404,
                'message' => 'Code Not Found',
            ];
        }

        if ($allocate->out_time !== null) {
            return [
                'status' => 400,
                'message' => 'Code Already Scanned',
            ];
        }

        $allocate->update([
            'status' => 'OUT',
            'out_time' => now(),
        ]);
        
        Cache::forget('allocates_with_location');
        Cache::forget('slot_status_' . $allocate->location_id);

        return [
            'status' => 200,
            'message' => 'Scanned Out',
        ];
    }
}
