<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::doesntHave('card')->inRandomOrder()->first()->id,
            'card_number' => $this->faker->creditCardNumber(),
            'expiration' => now()->addMonths(rand(12, 36)),
            'cvv' => $this->faker->numberBetween(100,999),
        ];
    }
}
