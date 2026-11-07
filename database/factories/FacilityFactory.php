<?php

namespace Database\Factories;

use App\Models\Facility;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Facility>
 */
class FacilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'FS-'.strtoupper(fake()->unique()->bothify('###')),
            'name' => fake()->randomElement(['Proyektor', 'PC', 'AC', 'Sound System', 'Papan Tulis']).' '.fake()->bothify('##'),
            'category' => fake()->randomElement(array_keys(Facility::categories())),
            'unit' => 'unit',
            'description' => null,
            'is_active' => true,
        ];
    }
}
