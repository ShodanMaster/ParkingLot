<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Create a new class instance.
     */
    public function generateAndStoreQrCode(string $codeText): string{
        $fileName = 'qr_code_' . $codeText . '.png';
        $filePath = 'qr_codes/' . $fileName;

        if (!Storage::disk('public')->exists($filePath)) {
            $qrCodeImage = QrCode::format('png')->size(200)->generate($codeText);
            Storage::disk('public')->put($filePath, $qrCodeImage);
        }

        return Storage::url($filePath);
    }
}
