<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamDevice extends Model
{
    use HasFactory;

    protected $fillable = ['public_id', 'label', 'device_type', 'status', 'token_hash', 'device_metadata', 'location', 'registered_by', 'last_seen_at', 'revoked_at', 'revoked_by', 'revocation_reason'];

    protected $casts = ['device_metadata' => 'array', 'last_seen_at' => 'datetime', 'revoked_at' => 'datetime'];

    public function registeredBy() { return $this->belongsTo(User::class, 'registered_by'); }
    public function revokedBy() { return $this->belongsTo(User::class, 'revoked_by'); }

    public function isActive(): bool { return $this->status === 'active' && $this->revoked_at === null; }
}
