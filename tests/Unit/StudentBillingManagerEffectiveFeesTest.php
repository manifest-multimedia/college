<?php

namespace Tests\Unit;

use App\Livewire\Finance\StudentBillingManager;
use ReflectionMethod;
use Tests\TestCase;

class StudentBillingManagerEffectiveFeesTest extends TestCase
{
    /**
     * Call the protected getEffectiveFeeSelection method for testing.
     */
    protected function getEffectiveFeeSelection(array $availableFees, array $selectedIds): array
    {
        $component = new StudentBillingManager;
        $method = new ReflectionMethod(StudentBillingManager::class, 'getEffectiveFeeSelection');
        $method->setAccessible(true);

        return $method->invoke($component, $availableFees, $selectedIds);
    }

    public function test_effective_selection_includes_mandatory_fees_and_sums_correctly(): void
    {
        $availableFees = [
            ['id' => 1, 'amount' => 100.00, 'is_mandatory' => true],
            ['id' => 2, 'amount' => 130.00, 'is_mandatory' => true],
            ['id' => 3, 'amount' => 1700.00, 'is_mandatory' => true],
            ['id' => 4, 'amount' => 50.00, 'is_mandatory' => false],
        ];
        $selectedIds = []; // Simulates disabled checkboxes not submitted

        $result = $this->getEffectiveFeeSelection($availableFees, $selectedIds);

        $this->assertSame([1, 2, 3], $result['ids']);
        $this->assertSame(1930.00, $result['total']);
    }

    public function test_effective_selection_merges_mandatory_and_user_selected(): void
    {
        $availableFees = [
            ['id' => 1, 'amount' => 100.00, 'is_mandatory' => true],
            ['id' => 2, 'amount' => 50.00, 'is_mandatory' => false],
            ['id' => 3, 'amount' => 25.00, 'is_mandatory' => false],
        ];
        $selectedIds = [2, 3]; // User checked optional fees

        $result = $this->getEffectiveFeeSelection($availableFees, $selectedIds);

        $this->assertSame([1, 2, 3], $result['ids']);
        $this->assertSame(175.00, $result['total']);
    }

    public function test_effective_selection_works_when_no_mandatory_fees(): void
    {
        $availableFees = [
            ['id' => 10, 'amount' => 100.00, 'is_mandatory' => false],
            ['id' => 20, 'amount' => 200.00, 'is_mandatory' => false],
        ];
        $selectedIds = [10, 20];

        $result = $this->getEffectiveFeeSelection($availableFees, $selectedIds);

        $this->assertSame([10, 20], $result['ids']);
        $this->assertSame(300.00, $result['total']);
    }

    public function test_effective_selection_handles_string_ids_from_livewire(): void
    {
        $availableFees = [
            ['id' => 5, 'amount' => 100.00, 'is_mandatory' => true],
            ['id' => 6, 'amount' => 50.00, 'is_mandatory' => false],
        ];
        $selectedIds = ['6']; // String from checkbox value

        $result = $this->getEffectiveFeeSelection($availableFees, $selectedIds);

        $this->assertSame([5, 6], $result['ids']);
        $this->assertSame(150.00, $result['total']);
    }

    public function test_effective_selection_returns_empty_when_no_fees_selected(): void
    {
        $availableFees = [
            ['id' => 1, 'amount' => 50.00, 'is_mandatory' => false],
        ];
        $selectedIds = [];

        $result = $this->getEffectiveFeeSelection($availableFees, $selectedIds);

        $this->assertSame([], $result['ids']);
        $this->assertSame(0.0, $result['total']);
    }
}
