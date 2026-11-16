<?php
namespace App\Models\Hostel;
use Illuminate\Database\Eloquent\Model;
class HostelBed extends Model {
    protected $table = 'hostel_beds';
    public function room() { return $this->belongsTo(HostelRoom::class, 'hostel_room_id'); }
    protected $fillable = ['hostel_room_id', 'bed_code', 'status'];
    protected $casts = [];
}
