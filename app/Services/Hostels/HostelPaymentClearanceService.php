<?php
namespace App\Services\Hostels;
use App\Models\Hostel\HostelSetting;
use App\Models\StudentFeeBill;
use Illuminate\Validation\ValidationException;
class HostelPaymentClearanceService {
 public function isCleared(int $studentId,int $periodId): bool {
  $settings=HostelSetting::first();
  if(!$settings?->require_payment) return true;
  if(!$settings->fee_type_id) return false;
  $period=\App\Models\Hostel\HostelAllocationPeriod::findOrFail($periodId);
  // Never treat unrelated or reversed student payments as hostel clearance.
  $bills=StudentFeeBill::query()->active()->where('student_id',$studentId)->where('fee_type_id',$settings->fee_type_id)
   ->when($period->academic_year_id,fn($q,$id)=>$q->where('academic_year_id',$id))
   ->when($period->semester_id,fn($q,$id)=>$q->where('semester_id',$id))->get();
  if($bills->isEmpty()) return false;
  $total=$bills->sum(fn($bill)=>(float)$bill->total_amount);
  $paid=$bills->sum(fn($bill)=>(float)$bill->amount_paid);
  return $total>0 && $paid+0.00001 >= $total*((int)$settings->minimum_payment_percent/100);
 }
 public function assertCleared(int $studentId,int $periodId): void {
  if(!$this->isCleared($studentId,$periodId)) throw ValidationException::withMessages(['payment'=>'Accommodation fee clearance is pending or insufficient.']);
 }
}
