<?php

namespace App\Services;

use App\Models\Shop;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ShopCoverImageService
{
    public function store(Shop $shop, UploadedFile $file): string
    {
        $image = (new ImageManager(new Driver))
            ->read($file->getRealPath())
            ->cover(1600, 900)
            ->toWebp(82);
        $path = 'shops/'.$shop->id.'/cover-'.Str::uuid().'.webp';

        Storage::disk('public')->put($path, $image->toString());

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
