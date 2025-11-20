<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = fake()->year();
        $name = $year.'/'.($year + 1);

        return [
            'name' => $name,
            'year' => $year,
            'slug' => \Illuminate\Support\Str::slug($name),
            'start_date' => $year.'-09-01',
            'end_date' => ($year + 1).'-08-31',
            'is_current' => false,
            'is_deleted' => false,
            'created_by' => 'system',
        ];
    }
}
