<?php
namespace Tests\Feature;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
class HostelBulkAllocationRoutesTest extends TestCase {
 public function test_bulk_and_search_routes_exist(): void {
  foreach (['hostels.bulk.index','hostels.bulk.preview','hostels.bulk.confirm','hostels.students.search'] as $route) $this->assertTrue(Route::has($route));
 }
 public function test_guest_cannot_search_or_bulk_allocate(): void {
  $this->get(route('hostels.students.search',['search'=>'Anna']))->assertRedirect();
  $this->get(route('hostels.bulk.index'))->assertRedirect();
  $this->post(route('hostels.bulk.preview'))->assertRedirect();
  $this->post(route('hostels.bulk.confirm'))->assertRedirect();
 }
}
