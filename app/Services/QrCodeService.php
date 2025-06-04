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
        $qrCodeImage = QrCode::format('png')->size(200)->generate($codeText);
        $fileName = 'qr_code_' . $codeText . '.png';

        Storage::disk('public')->put('qr_codes/' . $fileName, $qrCodeImage);
        return Storage::url('qr_codes/' . $fileName);
    }
}
