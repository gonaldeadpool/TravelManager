<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class LocalStoragePaths
{
    public static function locandine(): string
    {
        return self::value('storage.locandine', storage_path('app/public/locandine'));
    }

    public static function documenti(): string
    {
        return self::value('storage.documenti', storage_path('app/private/documenti'));
    }

    public static function documentiPratiche(): string
    {
        return self::value('storage.documenti_pratiche', storage_path('app/private/documenti-pratiche'));
    }

    public static function disk(string $path): FilesystemAdapter
    {
        return Storage::build([
            'driver' => 'local',
            'root' => $path,
            'throw' => false,
        ]);
    }

    public static function ensureDirectories(): void
    {
        foreach ([self::locandine(), self::documenti(), self::documentiPratiche()] as $path) {
            if (! is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    private static function value(string $key, string $default): string
    {
        return (string) (AppSetting::where('key', $key)->value('value') ?: $default);
    }
}
