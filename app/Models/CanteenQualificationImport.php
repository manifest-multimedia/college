<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanteenQualificationImport extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['imported_at' => 'datetime', 'revoked_at' => 'datetime'];

    public function qualifications()
    {
        return $this->hasMany(CanteenQualification::class, 'import_id');
    }

    public function importedBy()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }
}
