<?php
namespace App\Models\Hostel;
use Illuminate\Database\Eloquent\Model;
class HostelAllocationEvent extends Model {
    protected $table = 'hostel_allocation_events';
    protected $fillable = ['hostel_allocation_id', 'actor_id', 'event', 'notes', 'metadata'];
    protected $casts = ['metadata' => 'array'];
}
