<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetHistory extends Model
{
    use HasFactory;

    const UPDATED_AT = null; // We only need created_at

    protected $fillable = [
        'asset_id',
        'action',
        'old_value',
        'new_value',
        'user_id',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    /**
     * Get the asset that this history belongs to.
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an action for an asset.
     */
    public static function logAction(int $assetId, string $action, $oldValue = null, $newValue = null, ?int $userId = null)
    {
        return static::create([
            'asset_id' => $assetId,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'user_id' => $userId ?: auth()->id(),
        ]);
    }
}
