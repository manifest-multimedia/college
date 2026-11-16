<?php
namespace Tests\Feature;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
class HostelWorkflowsRoutesTest extends TestCase {
 public function test_new_workflow_routes_exist(): void {
  foreach(['student.hostel.applications','student.hostel.apply','hostels.applications','hostels.applications.decide','hostels.operations','hostels.reports.export','hostels.wardens.assign','hostels.wardens.revoke','hostels.allocations.transfer','warden.hostels.index','warden.hostels.transition'] as $name) $this->assertTrue(Route::has($name),$name);
 }
 public function test_guests_cannot_view_student_or_warden_records(): void {
  $this->get(route('student.hostel.applications'))->assertRedirect();
  $this->get(route('warden.hostels.index'))->assertRedirect();
  $this->get(route('hostels.applications'))->assertRedirect();
 }
}
