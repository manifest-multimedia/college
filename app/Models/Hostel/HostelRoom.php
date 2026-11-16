<?php
namespace App\Models\Hostel;
use Illuminate\Database\Eloquent\Model;
class HostelRoom extends Model {
    protected $table = 'hostel_rooms';
    public function beds() { return $this->hasMany(HostelBed::class); }
    public function hostel() { return $this->belongsTo(Hostel::class); }
    protected $fillable = ['hostel_id', 'room_number', 'floor', 'status'];
    protected $casts = [];
}
