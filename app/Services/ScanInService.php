<?php

namespace App\Services;

use App\Models\Allocate;
use App\Models\Location;
use App\Models\QrCode as ModelsQrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ScanInService
{
    protected QrCodeService $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function getAllAllocatesWithLocation()
    {
        return Cache::remember('cache:allocates:with-location', now()->addMinutes(3), function () {
            return Allocate::with(['location:id,name,slot'])
                ->select('id', 'location_id', 'vehicle_number', 'status', 'in_time', 'out_time', 'qrcode', 'created_at')
                ->latest()
                ->get();
        });
    }

    public function allocateWithQr(array $data): Allocate
    {
        return DB::transaction(function () use ($data) {
            $allocate = Allocate::create([
                'location_id' => $data['locationId'],
                'vehicle_number' => $data['vehicleNumber'],
                'status' => 'IN',
                'qrcode' => Allocate::codeGenerator($data['locationId']),
            ]);

            $this->generateAndSaveQrCodeForAllocate($allocate);

            $this->clearCache($data['locationId']);

            return $allocate;
        });
    }

    public function allocateVehicleWithCheckOut(array $data): Allocate
    {
        return DB::transaction(function () use ($data) {
            $lastAllocate = Allocate::where('vehicle_number', $data['vehicleNumber'])
                ->select('id', 'location_id', 'created_at')
                ->whereNull('out_time')
                ->latest('created_at')
                ->first();

            if ($lastAllocate) {
                if ($lastAllocate->created_at->gt(now()->subSeconds(10))) {
                    throw new \Exception('Vehicle was just scanned recently. Please wait a few seconds.');
                }

                $lastAllocate->update([
                    'status' => 'OUT',
                    'out_time' => now(),
                ]);

                Cache::forget('cache:slot-status:location:' . $lastAllocate->location_id);
            }

            $allocate = Allocate::create([
                'location_id' => $data['locationId'],
                'vehicle_number' => $data['vehicleNumber'],
                'qrcode' => Allocate::codeGenerator($data['locationId']),
            ]);

            $this->generateAndSaveQrCodeForAllocate($allocate);

            $this->clearCache($data['locationId']);

            return $allocate;
        });
    }

    public function generateAndSaveQrCodeForAllocate(Allocate $allocate): string
    {
        $qrCodeUrl = $this->qrCodeService->generateAndStoreQrCode($allocate->qrcode);

        $existing = ModelsQrCode::where('allocate_id', $allocate->id)
            ->select('id', 'path')
            ->first();

        if (!$existing || $existing->path !== $qrCodeUrl) {
            ModelsQrCode::updateOrCreate(
                ['allocate_id' => $allocate->id],
                ['path' => $qrCodeUrl]
            );
        }

        return $qrCodeUrl;
    }

    public function getSlotStatus(int $locationId): array
    {
        return Cache::remember("cache:slot-status:location:{$locationId}", now()->addMinutes(2), function () use ($locationId) {
            $location = Location::select('id', 'slot')->findOrFail($locationId);

            $totalSlots = $location->slot;
            $occupiedSlots = Allocate::where('location_id', $locationId)
                ->whereNull('out_time')
                ->count();

            $availableSlots = $totalSlots - $occupiedSlots;

            return compact('totalSlots', 'occupiedSlots', 'availableSlots');
        });
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

    private function clearCache($id): void
    {
        Cache::forget('cache:allocates:with-location');
        Cache::forget("cache:slot-status:location:{$id}");
    }
}
