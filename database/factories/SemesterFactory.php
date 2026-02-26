<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Semester>
 */
class SemesterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->randomElement(['First Semester', 'Second Semester']);

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'academic_year_id' => AcademicYear::factory(),
            'start_date' => now(),
            'end_date' => now()->addMonths(4),
            'is_current' => false,
            'description' => 'Test semester',
        ];
    }
}

