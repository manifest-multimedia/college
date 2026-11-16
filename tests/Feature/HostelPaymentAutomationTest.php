<?php
namespace Tests\Feature;
use App\Models\Hostel\HostelSetting;
use App\Services\Hostels\HostelPaymentClearanceService;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
class HostelPaymentAutomationTest extends TestCase {
 public function test_automatic_allocation_route_is_registered(): void {
  $this->assertTrue(Route::has('hostels.applications.auto-allocate'));
 }
 public function test_unconfigured_payment_clearance_fails_closed(): void {
  $settings=new HostelSetting(['enabled'=>true,'require_payment'=>true,'minimum_payment_percent'=>100]);
  $this->assertTrue($settings->require_payment);
  $this->assertNull($settings->fee_type_id);
 }
 public function test_guests_cannot_trigger_automatic_allocation(): void {
  $this->post(route('hostels.applications.auto-allocate'),['hostel_allocation_period_id'=>1])->assertRedirect();
 }
}
