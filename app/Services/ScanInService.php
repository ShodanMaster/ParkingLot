<?php

namespace App\Services;

use App\Models\Allocate;
use App\Models\Location;
use App\Models\QrCode as ModelsQrCode;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ScanInService
{
    protected QrCodeService $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function getAllVehiclesOrderedByName()
    {
        return Vehicle::orderBy('name')->get();
    }

    public function getAllAllocatesWithLocation()
    {
        return Allocate::with('location')->latest()->get();
    }

    public function allocateWithQr(array $data): Allocate
    {
        return DB::transaction(function () use ($data) {
            $allocate = Allocate::create([
                'location_id' => $data['locationId'],
                'vehicle_number' => $data['vehicleNumber'],
                'qrcode' => Allocate::codeGenerator($data['locationId']),
            ]);

            $this->generateAndSaveQrCodeForAllocate($allocate);

            return $allocate;
        });
    }

    public function allocateVehicleWithCheckOut(array $data): Allocate
    {
        return DB::transaction(function () use ($data) {
            $lastAllocate = Allocate::where('vehicle_number', $data['vehicleNumber'])
                ->whereNull('out_time')
                ->latest('created_at')
                ->first();

            if ($lastAllocate) {
                $lastAllocate->update([
                    'status' => 'OUT',
                    'out_time' => now(),
                ]);
            }

            $allocate = Allocate::create([
                'location_id' => $data['locationId'],
                'vehicle_number' => $data['vehicleNumber'],
                'qrcode' => Allocate::codeGenerator($data['locationId']),
            ]);

            $this->generateAndSaveQrCodeForAllocate($allocate);

            return $allocate;
        });
    }

    public function generateAndSaveQrCodeForAllocate(Allocate $allocate): string
    {
        $qrCodeUrl = $this->qrCodeService->generateAndStoreQrCode($allocate->qrcode);

        ModelsQrCode::updateOrCreate(
            ['allocate_id' => $allocate->id],
            ['path' => $qrCodeUrl]
        );

        return $qrCodeUrl;
    }

    public function getSlotStatus(int $locationId): array
    {
        $location = Location::findOrFail($locationId);

        $totalSlots = $location->slot;
        $occupiedSlots = Allocate::where('location_id', $locationId)
            ->whereNull('out_time')
            ->count();

        $availableSlots = $totalSlots - $occupiedSlots;

        return compact('totalSlots', 'occupiedSlots', 'availableSlots');
    }

    public function isVehicleAlreadyAllocated(string $vehicleNumber): bool
    {
        return Allocate::where('vehicle_number', $vehicleNumber)
            ->whereNull('out_time')
            ->exists();
    }

    public function getOrCreateQrCodeForAllocate(Allocate $allocate): string
    {
        if (!$allocate->qrCode || !Storage::disk('public')->exists(str_replace('/storage', '', $allocate->qrCode->path))) {
            return $this->generateAndSaveQrCodeForAllocate($allocate);
        }

        return $allocate->qrCode->path;
    }
}
