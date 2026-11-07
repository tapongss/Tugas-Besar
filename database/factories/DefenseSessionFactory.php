<?php

namespace Database\Factories;

use App\Models\DefenseSession;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DefenseSession>
 */
class DefenseSessionFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->numberBetween(8, 15);

        return [
            'room_id' => Room::factory(),
            'created_by' => User::factory(),
            'title' => fake()->randomElement(['Sidang Skripsi', 'Seminar Proposal', 'Seminar Hasil']),
            'student_name' => fake()->name(),
            'student_nim' => fake()->numerify('##########'),
            'type' => fake()->randomElement(array_keys(DefenseSession::types())),
            'date' => today()->addDays(fake()->numberBetween(0, 14)),
            'start_time' => sprintf('%02d:00:00', $start),
            'end_time' => sprintf('%02d:00:00', $start + 2),
            'status' => DefenseSession::STATUS_SCHEDULED,
            'supervisor_name' => fake()->name(),
            'examiner_name' => fake()->name(),
            'notes' => null,
        ];
    }
}
