<?php

namespace App\Services;

use App\Models\Allocate;
use App\Models\QrCode as ModelsQrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ScanInService
{
    /**
     * Handle the vehicle scan-in process.
     *
     * @param array $data
     * @return \App\Models\Allocate
     */
    public function allocateWithQr(array $data): Allocate
    {
        return DB::transaction(function () use ($data) {
            $allocate = Allocate::create([
                'location_id' => $data['locationId'],
                'vehicle_number' => $data['vehicleNumber'],
                'qrcode' => Allocate::codeGenerator($data['locationId']),
            ]);

            $qrCodeImage = QrCode::format('png')->size(200)->generate($allocate->qrcode);
            $fileName = 'qr_code_' . $allocate->qrcode . '.png';

            Storage::disk('public')->put('qr_codes/' . $fileName, $qrCodeImage);
            $qrCodeUrl = Storage::url('qr_codes/' . $fileName);

            ModelsQrCode::create([
                'allocate_id' => $allocate->id,
                'path' => $qrCodeUrl,
            ]);

            return $allocate;
        });
    }

    public function isVehicleAlreadyAllocated(string $vehicleNumber): bool
    {
        return Allocate::where('vehicle_number', $vehicleNumber)
            ->whereNull('out_time')
            ->exists();
    }
}
