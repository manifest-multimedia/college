<?php
namespace App\Models\Hostel;
use Illuminate\Database\Eloquent\Model;
class HostelWarden extends Model {
 protected $fillable=['hostel_id','user_id','active'];
 protected $casts=['active'=>'boolean'];
 public function hostel() { return $this->belongsTo(Hostel::class); }
 public function user() { return $this->belongsTo(\App\Models\User::class); }
}
