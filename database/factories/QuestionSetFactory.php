<?php

namespace Database\Factories;

use App\Models\QuestionSet;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\QuestionSet>
 */
class QuestionSetFactory extends Factory
{
    protected $model = QuestionSet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'course_id' => Subject::factory(),
            'difficulty_level' => $this->faker->randomElement(['easy', 'medium', 'hard']),
            'created_by' => User::factory(),
        ];
    }
}

