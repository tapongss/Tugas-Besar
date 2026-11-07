<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\DefenseSession;
use App\Models\Facility;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_page_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['code' => 'LAP-01', 'name' => 'Ruang Laporan']);
        DefenseSession::factory()->create([
            'room_id' => $room->id,
            'date' => today(),
            'status' => 'selesai',
        ]);

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Utilisasi ruang')
            ->assertSee('Ruang Laporan');
    }

    public function test_report_can_be_filtered_by_month(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('reports.index', ['month' => '2026-01']))
            ->assertOk()
            ->assertSee('Januari 2026');
    }

    public function test_print_report_can_be_rendered(): void
    {
        $user = User::factory()->create();
        $building = Building::factory()->create();
        $room = Room::factory()->create(['building_id' => $building->id]);
        $facility = Facility::factory()->create();
        $room->facilities()->attach($facility->id, ['quantity' => 3, 'condition' => 'baik', 'note' => null]);

        $this->actingAs($user)
            ->get(route('reports.print'))
            ->assertOk()
            ->assertSee('Laporan Ruang & Fasilitas Sidang', false)
            ->assertSee($user->name);
    }

    public function test_rooms_can_be_exported_as_csv(): void
    {
        $user = User::factory()->create();
        Room::factory()->create(['code' => 'CSV-01']);

        $response = $this->actingAs($user)->get(route('reports.rooms.csv'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('Kode Ruang', $response->streamedContent());
    }

    public function test_schedules_can_be_exported_as_csv(): void
    {
        $user = User::factory()->create();
        DefenseSession::factory()->create(['title' => 'Sidang Ekspor CSV']);

        $response = $this->actingAs($user)->get(route('reports.schedules.csv'));

        $response->assertOk();
        $this->assertStringContainsString('Sidang Ekspor CSV', $response->streamedContent());
    }
}
