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
        $name = $this->faker->words(3, true);

        return [
            'name' => ucfirst($name),
            'short_name' => strtoupper(substr($name, 0, 3)),
            'description' => 'Test program',
            'is_active' => true,
            'is_deleted' => false,
            'created_by' => 'factory',
            'slug' => \Illuminate\Support\Str::slug($name),
        ];
    }
}
