<?php
namespace App\Models\Hostel;
use Illuminate\Database\Eloquent\Model;
class HostelAllocationPeriod extends Model {
    protected $table = 'hostel_allocation_periods';
    protected $fillable = ['name', 'academic_year_id', 'semester_id', 'starts_on', 'ends_on', 'status'];
    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date'];
}
