<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
    ];

    /**
     * Get the assets belonging to this category.
     */
    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(AssetCategory::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(AssetCategory::class, 'parent_id');
    }

    /**
     * Get all descendants of this category.
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Check if this category has any children.
     */
    public function hasChildren()
    {
        return $this->children()->exists();
    }

    /**
     * Get the full category path (parent > child > grandchild).
     */
    public function getFullPathAttribute()
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }
}
