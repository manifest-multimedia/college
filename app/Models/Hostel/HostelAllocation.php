<?php
namespace App\Models\Hostel;
use Illuminate\Database\Eloquent\Model;
class HostelAllocation extends Model {
    protected $table = 'hostel_allocations';
    public function student() { return $this->belongsTo(\App\Models\Student::class); }
    public function bed() { return $this->belongsTo(HostelBed::class, 'hostel_bed_id'); }
    public function period() { return $this->belongsTo(HostelAllocationPeriod::class, 'hostel_allocation_period_id'); }
    protected $fillable = ['student_id', 'hostel_bed_id', 'hostel_allocation_period_id', 'status', 'checked_in_at', 'checked_out_at', 'allocated_by', 'notes', 'transferred_from_id'];
    protected $casts = ['checked_in_at' => 'datetime', 'checked_out_at' => 'datetime'];
}
