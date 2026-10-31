<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanteenQualificationImport extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = ['imported_at' => 'datetime', 'revoked_at' => 'datetime'];
}
