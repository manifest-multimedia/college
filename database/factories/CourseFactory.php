<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'course_code' => fake()->regexify('[A-Z]{2,4}[0-9]{3,4}'),
            'description' => fake()->sentence(),
            'slug' => fake()->slug(),
            'is_deleted' => false,
            'created_by' => User::factory(),
        ];
    }
}
