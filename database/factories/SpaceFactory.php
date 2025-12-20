<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Space>
 */
class SpaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' Room',
            'capacity' => $this->faker->numberBetween(10, 300),
            'description' => $this->faker->sentence(),
            'active' => true,
            'available_from' => '08:00',
            'available_to' => '18:00',
        ];
    }
}
