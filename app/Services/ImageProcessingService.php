<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageProcessingService
{
    public function optimize($file, $folder = 'photos'): string
    {
        $fileName = $folder . '/' . Str::uuid() . '.jpg';

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            $image->scaleDown(width: 1200);
            Storage::disk('public')->put($fileName, $image->encodeByMediaType('image/jpeg', 70));
        } catch (\Throwable $e) {
            Log::error('Image Processing Failed: ' . $e->getMessage());
            // Fallback: simpan file asli jika terjadi error pada Intervention Image
            Storage::disk('public')->put($fileName, file_get_contents($file->getRealPath()));
        }

        return $fileName;
    }
}
