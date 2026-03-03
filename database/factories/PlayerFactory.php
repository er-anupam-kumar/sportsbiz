<?php

namespace Database\Factories;

use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'base_price' => fake()->numberBetween(50000, 200000),
            'age' => fake()->numberBetween(18, 38),
            'country' => fake()->country(),
            'previous_team' => fake()->company(),
            'stats' => [
                'matches' => fake()->numberBetween(10, 150),
                'rating' => fake()->randomFloat(2, 1, 10),
            ],
            'status' => 'available',
        ];
    }
}
