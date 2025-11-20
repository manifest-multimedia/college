<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'email', 'email');
    }

    public function examSessions()
    {
        return $this->hasMany(ExamSession::class, 'id');
    }

    /**
     * The departments that the user belongs to.
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class)->withPivot('is_head')->withTimestamps();
    }

    /**
     * Check if user is head of any department.
     */
    public function departmentHeadOf()
    {
        return $this->belongsToMany(Department::class)
            ->withPivot('is_head')
            ->wherePivot('is_head', true);
    }

    /**
     * Check if user is head of a specific department.
     */
    public function isDepartmentHead(?Department $department = null)
    {
        if (is_null($department)) {
            return $this->departmentHeadOf()->exists();
        }

        return $this->departmentHeadOf()->where('departments.id', $department->id)->exists();
    }

    /**
     * Check if this user was created via AuthCentral.
     * AuthCentral users have random passwords and typically a legacy 'role' field.
     */
    public function isAuthCentralUser(): bool
    {
        // AuthCentral users typically have a role field set and random passwords
        // This is a heuristic - you might want to add a more definitive field if needed
        return ! empty($this->role);
    }

    /**
     * Check if this user was created via regular authentication.
     */
    public function isRegularUser(): bool
    {
        return ! $this->isAuthCentralUser();
    }
}
