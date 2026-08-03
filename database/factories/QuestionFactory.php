<?php

namespace Database\Factories;

use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'type' => 'text',
            'title' => fake()->sentence(6),
            'is_required' => fake()->boolean(),
            'order' => 0,
            'options' => null,
            'is_archived' => false,
        ];
    }

    public function radio(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'radio',
            'options' => ['Option A', 'Option B', 'Option C'],
        ]);
    }

    public function checkbox(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'checkbox',
            'options' => ['Option A', 'Option B', 'Option C'],
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
        ]);
    }
}
