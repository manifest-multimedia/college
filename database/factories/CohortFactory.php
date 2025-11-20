<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cohort>
 */
class CohortFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true).' Cohort';

        return [
            'name' => $name,
            'description' => fake()->sentence(),
            'slug' => \Illuminate\Support\Str::slug($name),
            'is_active' => true,
            'is_deleted' => false,
            'created_by' => 'system',
            'academic_year' => fake()->year().'/'.(fake()->year() + 1),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
        ];
    }
}
