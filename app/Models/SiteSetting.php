<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Ambil nilai pengaturan berdasarkan kunci.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();
        return $setting && !is_null($setting->value) && $setting->value !== '' ? $setting->value : $default;
    }

    /**
     * Simpan atau perbarui nilai pengaturan.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}

