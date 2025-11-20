<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExamEntryTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_clearance_id',
        'student_id',
        'exam_type_id', // Kept for backward compatibility
        'ticketable_type', // New field for polymorphic relationship
        'ticketable_id',   // New field for polymorphic relationship
        'qr_code',
        'ticket_number',
        'is_verified',
        'verified_at',
        'verified_by',
        'verification_location',
        'verification_ip',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            // Generate a unique QR code if not provided
            if (empty($ticket->qr_code)) {
                $ticket->qr_code = 'QR-'.Str::uuid()->toString();
            }

            // Generate a unique ticket number if not provided
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = 'TKT-'.Str::upper(Str::random(8));
            }
        });
    }

    /**
     * Get the related exam (online or offline)
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function ticketable()
    {
        return $this->morphTo();
    }

    /**
     * Get the exam clearance associated with this entry ticket
     */
    public function examClearance()
    {
        return $this->belongsTo(ExamClearance::class);
    }

    /**
     * Get the student associated with this entry ticket
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the exam type associated with this entry ticket
     *
     * @deprecated Use ticketable relationship instead
     */
    public function examType()
    {
        return $this->belongsTo(ExamType::class, 'exam_type_id');
    }

    /**
     * Get the user who verified this ticket
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if ticket is valid and active
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->is_verified) {
            return false; // Already used
        }

        if ($this->expires_at && now()->isAfter($this->expires_at)) {
            return false; // Expired
        }

        return true;
    }

    /**
     * Determine if the ticket is for an online exam.
     *
     * @return bool
     */
    public function isOnlineExam()
    {
        return $this->ticketable_type === 'App\\Models\\Exam';
    }

    /**
     * Determine if the ticket is for an offline exam.
     *
     * @return bool
     */
    public function isOfflineExam()
    {
        return $this->ticketable_type === 'App\\Models\\OfflineExam';
    }
}
