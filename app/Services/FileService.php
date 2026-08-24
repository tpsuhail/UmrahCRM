<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Base64 uploads (tickets, host IDs, company logos). Drive is gone; files now
 * live on the `public` disk and are served straight from the app's own URL,
 * which also means logos render inside generated PDFs without a round trip.
 */
class FileService
{
    private const DIR = 'crm';

    public function saveTicket(?string $dataUrl, ?string $filename): string
    {
        return $this->store($dataUrl, $filename, 'ticket');
    }

    public function saveLogo(?string $dataUrl, ?string $filename): string
    {
        return $this->store($dataUrl, $filename, 'logo');
    }

    private function store(?string $dataUrl, ?string $filename, string $fallback): string
    {
        if (! $dataUrl) {
            return '';
        }

        try {
            $data = $dataUrl;
            $extension = '';
            if (preg_match('#^data:([^;]+);base64,(.*)$#s', $dataUrl, $m)) {
                $extension = $this->extensionFor($m[1]);
                $data = $m[2];
            }
            $bytes = base64_decode($data, true);
            if ($bytes === false || $bytes === '') {
                return '';
            }
            if (strlen($bytes) > (int) config('crm.max_upload_bytes')) {
                return '';
            }

            $safe = $this->safeName($filename ?: $fallback);
            $extension = $extension ?: (pathinfo($safe, PATHINFO_EXTENSION) ?: 'bin');
            $path = self::DIR.'/'.Str::uuid().'-'.Str::slug(pathinfo($safe, PATHINFO_FILENAME)).'.'.$extension;

            Storage::disk('public')->put($path, $bytes);

            return Storage::disk('public')->url($path);
        } catch (Throwable) {
            return '';
        }
    }

    private function safeName(string $name): string
    {
        $clean = preg_replace('/[^\w\x{0600}-\x{06FF}.\- ]/u', '', $name) ?? '';

        return Str::limit(trim($clean), 80, '') ?: 'file';
    }

    private function extensionFor(string $mime): string
    {
        return match ($mime) {
            'image/png' => 'png',
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            default => '',
        };
    }
}
