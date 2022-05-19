<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $ipv6 = $this->faker->ipv6();
        $ipv6 = str_replace(':', '', $ipv6);
        $key = substr($ipv6, 0, 10);

        return [
            'key' => $key,
            'players' => [],
            'played_cards' => [],
            'request_cards' => []
        ];
    }
}
