<?php

namespace App\Services;

use App\Models\Allocate;
use Illuminate\Support\Carbon;

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
        $allocate = Allocate::where('qrcode', $code)->first();

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
            'out_time' => Carbon::now(),
        ]);

        return [
            'status' => 200,
            'message' => 'Scanned Out',
        ];
    }
}
