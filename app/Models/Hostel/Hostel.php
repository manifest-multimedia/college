<?php
namespace App\Models\Hostel;
use Illuminate\Database\Eloquent\Model;
class Hostel extends Model {
    protected $table = 'hostels';
    public function rooms() { return $this->hasMany(HostelRoom::class); }
    protected $fillable = ['name', 'code', 'location', 'gender_policy', 'status', 'description'];
    protected $casts = [];
}
