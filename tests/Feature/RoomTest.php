<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\DefenseSession;
use App\Models\Facility;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    private Building $building;

    private Facility $projector;

    private Facility $computer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create();
        $this->building = Building::factory()->create();
        $this->projector = Facility::factory()->create(['name' => 'Proyektor Uji']);
        $this->computer = Facility::factory()->create(['name' => 'PC Uji']);
    }

    public function test_room_with_facilities_can_be_created(): void
    {
        $response = $this->actingAs($this->operator)->post(route('rooms.store'), [
            'building_id' => $this->building->id,
            'code' => 'x-101',
            'name' => 'Ruang Sidang Uji',
            'floor' => 1,
            'type' => 'ruang_sidang',
            'capacity' => 30,
            'status' => 'tersedia',
            'notes' => 'Ruang untuk pengujian.',
            'facilities' => [
                ['facility_id' => $this->projector->id, 'quantity' => 1, 'condition' => 'baik', 'note' => ''],
                ['facility_id' => $this->computer->id, 'quantity' => 2, 'condition' => 'rusak_ringan', 'note' => 'Perlu servis'],
            ],
        ]);

        $room = Room::where('code', 'X-101')->firstOrFail();

        $response->assertRedirect(route('rooms.show', $room));
        $this->assertSame(30, $room->capacity);
        $this->assertCount(2, $room->facilities);

        $this->assertDatabaseHas('facility_room', [
            'room_id' => $room->id,
            'facility_id' => $this->computer->id,
            'quantity' => 2,
            'condition' => 'rusak_ringan',
        ]);
    }

    public function test_room_code_must_be_unique(): void
    {
        Room::factory()->create(['code' => 'R-UNIK']);

        $this->actingAs($this->operator)
            ->from(route('rooms.create'))
            ->post(route('rooms.store'), [
                'building_id' => $this->building->id,
                'code' => 'R-UNIK',
                'name' => 'Ruang Duplikat',
                'floor' => 1,
                'type' => 'ruang_ujian',
                'capacity' => 20,
                'status' => 'tersedia',
            ])
            ->assertSessionHasErrors('code');
    }

    public function test_room_facilities_can_be_synchronized_on_update(): void
    {
        $room = Room::factory()->create(['building_id' => $this->building->id]);
        $room->facilities()->attach($this->projector->id, [
            'quantity' => 1,
            'condition' => 'baik',
            'note' => null,
        ]);

        $this->actingAs($this->operator)
            ->put(route('rooms.update', $room), [
                'building_id' => $this->building->id,
                'code' => $room->code,
                'name' => $room->name,
                'floor' => $room->floor,
                'type' => $room->type,
                'capacity' => 25,
                'status' => 'perbaikan',
                'facilities' => [
                    ['facility_id' => $this->computer->id, 'quantity' => 4, 'condition' => 'baik', 'note' => null],
                ],
            ])
            ->assertRedirect(route('rooms.show', $room));

        $room->refresh();

        $this->assertSame(25, $room->capacity);
        $this->assertSame('perbaikan', $room->status);
        $this->assertCount(1, $room->facilities);
        $this->assertSame($this->computer->id, $room->facilities->first()->id);
        $this->assertSame(4, $room->facilities->first()->pivot->quantity);
    }

    public function test_room_filter_by_facility_returns_matching_rooms(): void
    {
        $matching = Room::factory()->create(['building_id' => $this->building->id, 'name' => 'Ruang Proyektor']);
        $matching->facilities()->attach($this->projector->id, ['quantity' => 1, 'condition' => 'baik', 'note' => null]);

        $other = Room::factory()->create(['building_id' => $this->building->id, 'name' => 'Ruang Tanpa Proyektor']);

        $this->actingAs($this->operator)
            ->get(route('rooms.index', ['facility_id' => $this->projector->id]))
            ->assertOk()
            ->assertSee($matching->name)
            ->assertDontSee($other->name);
    }

    public function test_room_with_schedule_history_cannot_be_deleted(): void
    {
        $room = Room::factory()->create(['building_id' => $this->building->id]);
        DefenseSession::factory()->create(['room_id' => $room->id]);

        $this->actingAs($this->operator)
            ->from(route('rooms.show', $room))
            ->delete(route('rooms.destroy', $room))
            ->assertRedirect(route('rooms.show', $room))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }
}
