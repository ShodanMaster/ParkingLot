<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Generate and store QR code image for given text.
     *
     * @param string $codeText
     * @return string  Public URL of stored QR code image.
     */
    public function generateAndStoreQrCode(string $codeText): string
    {
        $safeCodeText = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $codeText); // sanitize filename
        $fileName = 'qr_code_' . $safeCodeText . '.png';
        $filePath = 'qr_codes/' . $fileName;

        if (!Storage::disk('public')->exists($filePath)) {
            $qrCodeImage = QrCode::format('png')
                ->size(200)
                ->margin(1)
                ->generate($codeText);

            Storage::disk('public')->put($filePath, $qrCodeImage);
        }

        return Storage::url($filePath);
    }
}
