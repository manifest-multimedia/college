<?php
namespace App\Models\Hostel;
use Illuminate\Database\Eloquent\Model;
class HostelApplication extends Model {
    protected $table = 'hostel_applications';
    public function student() { return $this->belongsTo(\App\Models\Student::class); }
    public function period() { return $this->belongsTo(HostelAllocationPeriod::class, 'hostel_allocation_period_id'); }
    public function preferredHostel() { return $this->belongsTo(Hostel::class, 'preferred_hostel_id'); }
    protected $fillable = ['student_id', 'hostel_allocation_period_id', 'preferred_hostel_id', 'status', 'notes', 'reviewed_by', 'reviewed_at', 'waitlisted_at'];
    protected $casts = ['reviewed_at'=>'datetime', 'waitlisted_at'=>'datetime'];
}
