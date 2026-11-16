<?php
namespace App\Models\Hostel;
use Illuminate\Database\Eloquent\Model;
class HostelSetting extends Model {
    protected $table = 'hostel_settings';
    protected $fillable = ['enabled', 'allocation_method', 'allocation_unit', 'period_type', 'enforce_gender', 'require_check_in', 'allow_applications', 'require_payment', 'fee_type_id', 'minimum_payment_percent', 'auto_waitlist'];
    protected $casts = ['enabled' => 'boolean', 'enforce_gender' => 'boolean', 'require_check_in' => 'boolean', 'allow_applications' => 'boolean', 'require_payment', 'fee_type_id', 'minimum_payment_percent', 'auto_waitlist' => 'boolean'];
}
