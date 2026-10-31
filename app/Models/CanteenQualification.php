<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanteenQualification extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['qualified' => 'boolean', 'source_record' => 'array'];

    public function import(): BelongsTo
    {
        return $this->belongsTo(CanteenQualificationImport::class, 'import_id');
    }
}
