<?php

namespace Database\Factories;

use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Response>
 */
class ResponseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'respondent_email' => fake()->optional()->safeEmail(),
            'submitted_at' => now(),
        ];
    }
}
