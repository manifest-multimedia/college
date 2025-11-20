<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Temporary storage for original data during updates.
     * This property is not saved to the database.
     */
    public $_original_data;

    protected $fillable = [
        'asset_tag',
        'name',
        'description',
        'category_id',
        'department_id',
        'office_id',
        'location',
        'purchase_date',
        'purchase_price',
        'current_value',
        'state',
        'assigned_to_type',
        'assigned_to_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
    ];

    /**
     * Get the model's attributes.
     * Exclude _original_data from being treated as a database attribute.
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();
        unset($attributes['_original_data']);

        return $attributes;
    }

    /**
     * Convert the model's attributes to an array.
     * Exclude _original_data from array conversion.
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();
        unset($attributes['_original_data']);

        return $attributes;
    }

    protected static function boot()
    {
        parent::boot();

        // Auto-generate asset tag on creating
        static::creating(function ($asset) {
            if (! $asset->asset_tag) {
                $asset->asset_tag = static::generateAssetTag();
            }

            // Set created_by
            if (! $asset->created_by && auth()->check()) {
                $asset->created_by = auth()->id();
            }

            // Log creation
            static::created(function ($asset) {
                AssetHistory::logAction(
                    $asset->id,
                    'created',
                    null,
                    $asset->toArray(),
                    $asset->created_by
                );
            });
        });

        // Log updates
        static::updating(function ($asset) {
            if (! $asset->updated_by && auth()->check()) {
                $asset->updated_by = auth()->id();
            }

            // Store original data for history (not saved to database)
            $asset->_original_data = $asset->getOriginal();
        });

        static::updated(function ($asset) {
            if (isset($asset->_original_data)) {
                $changes = $asset->getChanges();
                // Remove _original_data from changes if it somehow got included
                unset($changes['_original_data']);

                if (! empty($changes)) {
                    AssetHistory::logAction(
                        $asset->id,
                        'updated',
                        $asset->_original_data,
                        $changes,
                        $asset->updated_by
                    );
                }

                // Clean up temporary data
                unset($asset->_original_data);
            }
        });

        // Log soft deletion
        static::deleting(function ($asset) {
            AssetHistory::logAction(
                $asset->id,
                'deleted',
                $asset->toArray(),
                null,
                auth()->id()
            );
        });
    }

    /**
     * Generate a unique asset tag.
     */
    public static function generateAssetTag()
    {
        $prefix = AssetSetting::getAssetTagPrefix();

        // Get the highest existing asset number
        $lastAsset = static::withTrashed()
            ->where('asset_tag', 'like', $prefix.'%')
            ->orderByRaw('CAST(SUBSTRING(asset_tag, '.(strlen($prefix) + 1).') AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastAsset) {
            $lastNumber = (int) str_replace($prefix, '', $lastAsset->asset_tag);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the category that this asset belongs to.
     */
    public function category()
    {
        return $this->belongsTo(AssetCategory::class);
    }

    /**
     * Get the department that this asset belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the office that this asset belongs to.
     */
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Get the polymorphic relation for what this asset is assigned to.
     */
    public function assignedTo()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who created this asset.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this asset.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the history of this asset.
     */
    public function histories()
    {
        return $this->hasMany(AssetHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Scope to filter by state.
     */
    public function scopeWithState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeWithCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to search assets.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('asset_tag', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%");
        });
    }

    /**
     * Get the state badge color.
     */
    public function getStateBadgeColorAttribute()
    {
        return match ($this->state) {
            'new' => 'success',
            'in_use' => 'primary',
            'damaged' => 'warning',
            'repaired' => 'info',
            'disposed' => 'secondary',
            'lost' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get the formatted purchase price.
     */
    public function getFormattedPurchasePriceAttribute()
    {
        return $this->purchase_price ? number_format($this->purchase_price, 2) : null;
    }

    /**
     * Get the formatted current value.
     */
    public function getFormattedCurrentValueAttribute()
    {
        return $this->current_value ? number_format($this->current_value, 2) : null;
    }
}
