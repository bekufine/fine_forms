<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Response;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Answer>
 */
class AnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'response_id' => Response::factory(),
            'question_id' => Question::factory(),
            'value' => fake()->sentence(),
        ];
    }
}
