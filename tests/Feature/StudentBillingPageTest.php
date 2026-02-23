<?php

namespace Tests\Feature;

use App\Livewire\Finance\StudentBillingManager;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentBillingPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'Finance Manager']);
    }

    protected function createFinanceUser(): User
    {
        $user = User::factory()->create([
            'role' => 'Finance Manager',
        ]);
        $user->assignRole('Finance Manager');

        return $user;
    }

    public function test_billing_page_loads_for_authorized_user(): void
    {
        $this->actingAs($this->createFinanceUser());

        $response = $this->get(route('finance.billing'));

        $response->assertOk();
        $response->assertSee('Student Billing Management', false);
        $response->assertSee('Batch Bills', false);
        $response->assertSee('New Bill', false);
    }

    public function test_billing_page_livewire_component_renders(): void
    {
        $this->actingAs($this->createFinanceUser());

        Livewire::test(StudentBillingManager::class)
            ->assertOk()
            ->assertSee('Student Billing Management', false)
            ->assertSee('Generate Batch Bills', false);
    }

    public function test_batch_bills_modal_can_be_opened(): void
    {
        $this->actingAs($this->createFinanceUser());

        Livewire::test(StudentBillingManager::class)
            ->call('openBatchBillsModal')
            ->assertSet('showBatchBillsModal', true)
            ->assertSee('Generate Batch Bills', false)
            ->assertSee('Program', false)
            ->assertSee('Batch (Cohort)', false);
    }

    public function test_new_bill_modal_can_be_opened(): void
    {
        $this->actingAs($this->createFinanceUser());

        Livewire::test(StudentBillingManager::class)
            ->call('openNewBillModal')
            ->assertSet('showNewBillModal', true)
            ->assertSee('Select Student', false);
    }
}
