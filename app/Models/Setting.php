<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'key',
    'value',
])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * Retrieve a setting value by key.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::query()->where('key', $key)->first();

        return $setting !== null && $setting->value !== null ? $setting->value : $default;
    }

    /**
     * Set or update a setting value by key.
     */
    public static function set(string $key, ?string $value): self
    {
        return static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get the configured application name.
     */
    public static function getAppName(): string
    {
        $configured = static::get('app_name');
        if ($configured !== null && trim($configured) !== '') {
            return $configured;
        }

        $configName = config('app.name');
        if (is_string($configName) && $configName !== '' && $configName !== 'Laravel') {
            return $configName;
        }

        return 'Sistem Data WNA Kanim Ponorogo';
    }

    /**
     * Get the configured instansi name.
     */
    public static function getInstansiName(): string
    {
        return static::get('instansi_name') ?: 'Kantor Imigrasi Kelas II Non TPI Ponorogo';
    }

    /**
     * Get the configured application description.
     */
    public static function getAppDescription(): string
    {
        return static::get('app_description') ?: 'Sistem Informasi Data Warga Negara Asing';
    }

    /**
     * Get the configured primary logo slot (1, 2, or 3).
     */
    public static function getPrimaryLogoSlot(): int
    {
        $slot = (int) static::get('primary_logo', '1');

        return in_array($slot, [1, 2, 3], true) ? $slot : 1;
    }

    /**
     * Get the relative storage path for a specific logo slot (1, 2, or 3).
     */
    public static function getLogoPath(int $slot): ?string
    {
        if ($slot === 1) {
            $path = static::get('app_logo_1');

            return ($path !== null && trim($path) !== '') ? $path : static::get('app_icon');
        }

        return static::get('app_logo_'.$slot);
    }

    /**
     * Get the storage path of the selected primary logo, with graceful fallback.
     */
    public static function getPrimaryLogoPath(): ?string
    {
        $primarySlot = static::getPrimaryLogoSlot();
        $path = static::getLogoPath($primarySlot);

        if ($path !== null && trim($path) !== '') {
            return $path;
        }

        for ($slot = 1; $slot <= 3; $slot++) {
            $candidate = static::getLogoPath($slot);
            if ($candidate !== null && trim($candidate) !== '') {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Get the streaming URL for the primary logo.
     */
    public static function getPrimaryLogoUrl(): ?string
    {
        $primarySlot = static::getPrimaryLogoSlot();
        $path = static::getPrimaryLogoPath();

        if ($path === null || trim($path) === '') {
            return null;
        }

        $version = substr(md5($path), 0, 8);

        return route('settings.icon', ['slot' => $primarySlot, 'v' => $version]);
    }

    /**
     * Get the streaming URL for a specific logo slot.
     */
    public static function getLogoUrl(int $slot): ?string
    {
        $path = static::getLogoPath($slot);

        if ($path === null || trim($path) === '') {
            return null;
        }

        $version = substr(md5($path), 0, 8);

        return route('settings.icon', ['slot' => $slot, 'v' => $version]);
    }

    /**
     * Get the icon streaming URL if an icon path is configured (alias for primary logo).
     */
    public static function getAppIconUrl(): ?string
    {
        return static::getPrimaryLogoUrl();
    }

    /**
     * Get list of all currently uploaded logos in order of slots.
     *
     * @return array<int, array{slot: int, path: string, url: string, is_primary: bool}>
     */
    public static function getAvailableLogos(): array
    {
        $primarySlot = static::getPrimaryLogoSlot();
        $logos = [];

        for ($slot = 1; $slot <= 3; $slot++) {
            $path = static::getLogoPath($slot);
            if ($path !== null && trim($path) !== '') {
                $logos[] = [
                    'slot' => $slot,
                    'path' => $path,
                    'url' => static::getLogoUrl($slot),
                    'is_primary' => ($slot === $primarySlot),
                ];
            }
        }

        return $logos;
    }
}
