<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Get a setting value by key.
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, string $value, ?string $description = null)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );

        return $setting;
    }

    /**
     * Get the asset tag prefix.
     */
    public static function getAssetTagPrefix()
    {
        return static::getValue('asset_tag_prefix', 'COL-');
    }
}