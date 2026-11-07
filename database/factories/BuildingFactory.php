<?php

namespace Database\Factories;

use App\Models\Building;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Building>
 */
class BuildingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'GD-'.strtoupper(fake()->unique()->bothify('??')),
            'name' => 'Gedung '.fake()->lastName(),
            'address' => fake()->address(),
            'floors' => fake()->numberBetween(1, 6),
            'notes' => null,
            'is_active' => true,
        ];
    }
}
