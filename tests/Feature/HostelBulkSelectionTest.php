<?php
namespace Tests\Feature;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
class HostelBulkSelectionTest extends TestCase {
 public function test_selection_endpoint_is_registered_and_protected(): void {
  $this->assertTrue(Route::has('hostels.bulk.selection'));
  $this->postJson(route('hostels.bulk.selection'), ['action'=>'clear','student_ids'=>[]])->assertUnauthorized();
 }
 public function test_preview_is_not_public(): void {
  $this->post(route('hostels.bulk.preview'), ['hostel_id'=>1,'period_id'=>1])->assertRedirect();
 }
}
