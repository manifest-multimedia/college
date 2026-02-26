<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Year;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'course_code' => strtoupper($this->faker->bothify('CS###')),
            'semester_id' => Semester::factory(),
            'year_id' => Year::factory(),
            'college_class_id' => CollegeClass::factory(),
            'credit_hours' => 3.0,
        ];
    }
}
