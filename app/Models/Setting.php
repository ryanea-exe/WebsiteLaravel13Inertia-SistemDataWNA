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

        return 'Sistem Data WNA';
    }

    /**
     * Get the configured instansi name.
     */
    public static function getInstansiName(): string
    {
        return static::get('instansi_name') ?: 'Kantor Imigrasi';
    }

    /**
     * Get the configured application description.
     */
    public static function getAppDescription(): string
    {
        return static::get('app_description') ?: 'Layanan Keimigrasian';
    }

    /**
     * Get the icon streaming URL if an icon path is configured.
     */
    public static function getAppIconUrl(): ?string
    {
        $iconPath = static::get('app_icon');

        return $iconPath ? route('settings.icon') : null;
    }
}
