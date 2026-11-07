<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'building_id' => Building::factory(),
            'code' => 'R-'.strtoupper(fake()->unique()->bothify('###')),
            'name' => 'Ruang '.fake()->randomElement(['Sidang', 'Ujian', 'Seminar', 'Diskusi']).' '.fake()->numberBetween(1, 20),
            'floor' => fake()->numberBetween(1, 4),
            'type' => fake()->randomElement(array_keys(Room::types())),
            'capacity' => fake()->randomElement([10, 15, 20, 25, 30, 40, 60]),
            'status' => 'tersedia',
            'notes' => null,
        ];
    }

    public function maintenance(): static
    {
        return $this->state(fn () => ['status' => 'perbaikan']);
    }
}
