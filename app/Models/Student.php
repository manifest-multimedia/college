<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class Student extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'other_name',
        'gender',
        'date_of_birth',
        'nationality',
        'country_of_residence',
        'home_region',
        'home_town',
        'religion',
        'mobile_number',
        'email',
        'gps_address',
        'postal_address',
        'residential_address',
        'marital_status',
        'college_class_id',
        'cohort_id',
        'academic_year_id',
        'status',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [];

    protected function dateOfBirth(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?Carbon {
                if ($value === null || $value === '') {
                    return null;
                }

                foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'Y/m/d', 'd.m.Y'] as $format) {
                    try {
                        $date = Carbon::createFromFormat($format, $value);
                        if ($date instanceof Carbon && $date->format($format) === $value) {
                            return $date;
                        }
                    } catch (\Exception) {
                        // Try next format
                    }
                }

                try {
                    return Carbon::parse($value);
                } catch (\Exception) {
                    return null;
                }
            },
        );
    }

    // Get attribute name
    public function getNameAttribute()
    {
        $firstName = $this->first_name ?? 'N/A';
        $otherName = $this->other_name ?? '';
        $lastName = $this->last_name ?? 'N/A';

        return trim("$firstName $otherName $lastName");
    }

    /**
     * Get the full name of the student
     */
    public function getFullNameAttribute()
    {
        $firstName = $this->first_name ?? '';
        $otherName = $this->other_name ?? '';
        $lastName = $this->last_name ?? '';

        // Filter out empty values and join with space
        return trim(implode(' ', array_filter([$firstName, $otherName, $lastName])));
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function collegeClass()
    {
        return $this->belongsTo(CollegeClass::class);
    }

    public function cohort()
    {
        return $this->belongsTo(Cohort::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function contacts()
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    public function isEligibleForExam($exam = null)
    {
        // 1. Check for manual override or explicit clearance in ExamClearance records
        if ($exam) {
            $clearance = $this->examClearances()
                ->where(function ($query) use ($exam) {
                    // Check by polymorphic relationship
                    $query->where(function ($q) use ($exam) {
                        $q->where('clearable_type', get_class($exam))
                          ->where('clearable_id', $exam->id);
                    });

                    // Backward compatibility: check by academic year, semester, and exam type
                    // This handles cases where the polymorphic relationship might not be set up but legacy fields are
                    if (isset($exam->academic_year_id) && isset($exam->semester_id) && isset($exam->type_id)) {
                        $query->orWhere(function ($q) use ($exam) {
                            $q->where('academic_year_id', $exam->academic_year_id)
                              ->where('semester_id', $exam->semester_id)
                              ->where('exam_type_id', $exam->type_id);
                        });
                    }
                })
                ->where(function ($query) {
                    $query->where('is_cleared', true)
                          ->orWhere('is_manual_override', true);
                })
                ->first();

            if ($clearance) {
                return true;
            }
        }

        // 2. Fallback to general fee-based eligibility
        // Check FeeCollections if student_id (registration number) is present and if is_eligble is true
        $feeCollection = FeeCollection::where('student_id', $this->student_id)->first();

        if (! $feeCollection || ! $feeCollection->is_eligble) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the user associated with this student
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createUser()
    {
        // Only create a user if a valid email exists and there is no existing user with the same email
        if ($this->email && ! User::where('email', $this->email)->exists()) {
            $user = User::create([
                'name' => ($this->first_name ?? 'N/A').' '.($this->other_name ?? 'N/A').' '.($this->last_name ?? 'N/A'),
                'email' => $this->email,
                'password' => Hash::make('password'),
            ]);

            // Assign Student role to newly created user
            $studentRole = \Spatie\Permission\Models\Role::where('name', 'Student')->first();
            if ($studentRole) {
                $user->assignRole($studentRole);
                Log::info("Assigned Student role to user {$user->email}");
            } else {
                Log::warning('Student role not found in system');
            }

            // Link the user to this student
            $this->user_id = $user->id;
            $this->save();

            return $user;
        }

        return null;
    }

    public function examSessions()
    {
        return $this->hasManyThrough(
            ExamSession::class, // Related model
            User::class,        // Intermediate model
            'email',            // Foreign key on the intermediate model (users.email)
            'student_id',       // Foreign key on the related model (exam_sessions.student_id)
            'email',            // Local key on the parent model (students.email)
            'id'                // Local key on the intermediate model (users.id)
        );
    }

    /**
     * Get fee bills associated with this student
     */
    public function feeBills()
    {
        return $this->hasMany(StudentFeeBill::class);
    }

    /**
     * Get fee payments made by this student
     */
    public function feePayments()
    {
        return $this->hasMany(FeePayment::class);
    }

    /**
     * Get exam clearances for this student
     */
    public function examClearances()
    {
        return $this->hasMany(ExamClearance::class);
    }

    /**
     * Get course registrations for this student
     */
    public function courseRegistrations()
    {
        return $this->hasMany(CourseRegistration::class);
    }

    /**
     * Get exam entry tickets for this student
     */
    public function examEntryTickets()
    {
        return $this->hasMany(ExamEntryTicket::class);
    }

    /**
     * Get offline exam scores for this student
     */
    public function offlineExamScores()
    {
        return $this->hasMany(OfflineExamScore::class);
    }

    /**
     * Get current fee bill for student in specified academic year and semester
     *
     * @param  int  $academicYearId
     * @param  int  $semesterId
     * @return StudentFeeBill|null
     */
    public function getCurrentFeeBill($academicYearId, $semesterId)
    {
        return $this->feeBills()
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->latest()
            ->first();
    }

    /**
     * Check if student is eligible for course registration (at least 60% fee payment)
     *
     * @param  int  $academicYearId
     * @param  int  $semesterId
     * @return bool
     */
    public function isEligibleForCourseRegistration($academicYearId, $semesterId)
    {
        $feeBill = $this->getCurrentFeeBill($academicYearId, $semesterId);

        if (! $feeBill) {
            return false;
        }

        return $feeBill->payment_percentage >= 60.0;
    }

    /**
     * Check if student is eligible for exam clearance based on exam type
     *
     * @param  int  $academicYearId
     * @param  int  $semesterId
     * @param  int  $examTypeId
     * @return bool
     */
    public function isEligibleForExamClearance($academicYearId, $semesterId, $examTypeId)
    {
        $feeBill = $this->getCurrentFeeBill($academicYearId, $semesterId);

        if (! $feeBill) {
            return false;
        }

        // Get the exam type and its required payment threshold
        $examType = ExamType::find($examTypeId);

        if (! $examType) {
            return false;
        }

        return $feeBill->payment_percentage >= $examType->payment_threshold;
    }

    /**
     * Check if student has active exam clearance for an exam type
     *
     * @param  int  $academicYearId
     * @param  int  $semesterId
     * @param  int  $examTypeId
     * @return ExamClearance|null
     */
    public function getActiveExamClearance($academicYearId, $semesterId, $examTypeId)
    {
        return $this->examClearances()
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('exam_type_id', $examTypeId)
            ->where('is_cleared', true)
            ->first();
    }
}
