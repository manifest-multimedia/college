<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentIdentification extends Model
{
    public const TYPES = [
        'ghana_card' => 'Ghana Card',
        'passport' => 'Passport',
        'nhis' => 'NHIS Card',
        'voter_id' => 'Voter ID',
        'drivers_licence' => "Driver's Licence",
        'other' => 'Other Government ID',
    ];

    protected $fillable = [
        'student_id',
        'identification_type',
        'identifier',
        'identifier_hash',
        'issuing_country',
        'issuing_authority',
        'expires_on',
        'verification_status',
        'verified_at',
        'verified_by',
        'image_path',
        'image_mime_type',
        'image_size',
    ];

    protected function casts(): array
    {
        return [
            'identifier' => 'encrypted',
            'expires_on' => 'date',
            'verified_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getMaskedIdentifierAttribute(): string
    {
        $identifier = (string) $this->identifier;
        $visible = min(4, strlen($identifier));

        return str_repeat('•', max(0, strlen($identifier) - $visible)).substr($identifier, -$visible);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->identification_type] ?? 'Other Government ID';
    }
}
