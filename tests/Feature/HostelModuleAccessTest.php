<?php
namespace Tests\Feature;
use Tests\TestCase;
class HostelModuleAccessTest extends TestCase
{
    public function test_guests_cannot_manage_hostels(): void
    {
        $this->get(route('hostels.index'))->assertRedirect();
        $this->post(route('hostels.allocate'), [])->assertRedirect();
    }
    public function test_hostel_routes_are_registered(): void
    {
        foreach (['hostels.index','hostels.settings','hostels.hostel','hostels.room','hostels.bed','hostels.period','hostels.allocate','hostels.transition'] as $name) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($name), $name);
        }
    }
}
