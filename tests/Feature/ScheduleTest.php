<?php

namespace Tests\Feature;

use App\Models\DefenseSession;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    private Room $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create();
        $this->room = Room::factory()->create();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'room_id' => $this->room->id,
            'title' => 'Sidang Skripsi — Pengujian Sistem',
            'student_name' => 'Mahasiswa Uji',
            'student_nim' => '2110101999',
            'type' => 'skripsi',
            'date' => today()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'status' => 'dijadwalkan',
            'supervisor_name' => 'Dr. Pembimbing',
            'examiner_name' => 'Dr. Penguji',
            'notes' => null,
        ], $overrides);
    }

    public function test_schedule_can_be_created(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('schedules.store'), $this->payload());

        $session = DefenseSession::where('student_nim', '2110101999')->firstOrFail();

        $response->assertRedirect(route('schedules.show', $session));

        $this->assertDatabaseHas('defense_sessions', [
            'room_id' => $this->room->id,
            'student_nim' => '2110101999',
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'created_by' => $this->operator->id,
        ]);
    }

    public function test_overlapping_schedule_is_rejected(): void
    {
        DefenseSession::factory()->create([
            'room_id' => $this->room->id,
            'date' => today()->addDay(),
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        $this->actingAs($this->operator)
            ->from(route('schedules.create'))
            ->post(route('schedules.store'), $this->payload(['start_time' => '10:00', 'end_time' => '12:00']))
            ->assertRedirect(route('schedules.create'))
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('defense_sessions', 1);
    }

    public function test_adjacent_schedule_is_allowed(): void
    {
        DefenseSession::factory()->create([
            'room_id' => $this->room->id,
            'date' => today()->addDay(),
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        $this->actingAs($this->operator)
            ->post(route('schedules.store'), $this->payload(['start_time' => '11:00', 'end_time' => '13:00']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('defense_sessions', 2);
    }

    public function test_schedule_in_room_under_repair_is_rejected(): void
    {
        $room = Room::factory()->maintenance()->create();

        $this->actingAs($this->operator)
            ->from(route('schedules.create'))
            ->post(route('schedules.store'), $this->payload(['room_id' => $room->id]))
            ->assertSessionHasErrors('room_id');
    }

    public function test_schedule_can_be_updated_without_conflicting_with_itself(): void
    {
        $session = DefenseSession::factory()->create([
            'room_id' => $this->room->id,
            'date' => today()->addDay(),
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        $this->actingAs($this->operator)
            ->put(route('schedules.update', $session), $this->payload([
                'title' => 'Judul Diperbarui',
                'start_time' => '09:30',
                'end_time' => '11:30',
            ]))
            ->assertRedirect(route('schedules.show', $session))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('defense_sessions', [
            'id' => $session->id,
            'title' => 'Judul Diperbarui',
            'start_time' => '09:30:00',
        ]);
    }

    public function test_schedule_status_can_be_updated(): void
    {
        $session = DefenseSession::factory()->create([
            'room_id' => $this->room->id,
            'status' => 'dijadwalkan',
        ]);

        $this->actingAs($this->operator)
            ->patch(route('schedules.status', $session), ['status' => 'selesai'])
            ->assertRedirect();

        $this->assertSame('selesai', $session->fresh()->status);
    }

    public function test_cancelled_schedule_does_not_block_room(): void
    {
        DefenseSession::factory()->create([
            'room_id' => $this->room->id,
            'date' => today()->addDay(),
            'start_time' => '09:00',
            'end_time' => '11:00',
            'status' => 'batal',
        ]);

        $this->actingAs($this->operator)
            ->post(route('schedules.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('defense_sessions', 2);
    }
}
