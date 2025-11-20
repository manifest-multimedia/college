<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CollegeClass>
 */
class CollegeClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'name' => $name,
            'short_name' => strtoupper(substr($name, 0, 3)),
            'description' => fake()->sentence(),
            'slug' => \Illuminate\Support\Str::slug($name),
            'is_active' => true,
            'is_deleted' => false,
            'created_by' => 'system',
            'course_id' => null,
        ];
    }
}
