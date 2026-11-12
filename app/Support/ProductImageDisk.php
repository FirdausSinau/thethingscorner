<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class ProductImageDisk
{
    public static function name(): string
    {
        return (string) config('filesystems.product_images_disk', 'public');
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(self::name());
    }
}
