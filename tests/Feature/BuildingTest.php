<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildingTest extends TestCase
{
    use RefreshDatabase;

    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create();
    }

    public function test_building_index_and_detail_can_be_rendered(): void
    {
        $building = Building::factory()->create();

        $this->actingAs($this->operator)
            ->get(route('buildings.index'))
            ->assertOk()
            ->assertSee($building->name);

        $this->actingAs($this->operator)
            ->get(route('buildings.show', $building))
            ->assertOk()
            ->assertSee($building->code);
    }

    public function test_building_can_be_created(): void
    {
        $response = $this->actingAs($this->operator)->post(route('buildings.store'), [
            'code' => 'gd-d',
            'name' => 'Gedung Inovasi',
            'address' => 'Jl. Kampus Baru No. 9',
            'floors' => 5,
            'notes' => 'Gedung baru untuk program studi baru.',
            'is_active' => '1',
        ]);

        $building = Building::where('code', 'GD-D')->firstOrFail();

        $response->assertRedirect(route('buildings.show', $building));
        $this->assertDatabaseHas('buildings', [
            'code' => 'GD-D',
            'name' => 'Gedung Inovasi',
            'floors' => 5,
            'is_active' => true,
        ]);
    }

    public function test_building_code_must_be_unique(): void
    {
        Building::factory()->create(['code' => 'GD-X']);

        $this->actingAs($this->operator)
            ->from(route('buildings.create'))
            ->post(route('buildings.store'), [
                'code' => 'gd-x',
                'name' => 'Gedung Duplikat',
                'floors' => 2,
            ])
            ->assertRedirect(route('buildings.create'))
            ->assertSessionHasErrors('code');
    }

    public function test_building_can_be_updated(): void
    {
        $building = Building::factory()->create(['name' => 'Gedung Lama']);

        $this->actingAs($this->operator)
            ->put(route('buildings.update', $building), [
                'code' => $building->code,
                'name' => 'Gedung Baru',
                'floors' => 4,
                'is_active' => '0',
            ])
            ->assertRedirect(route('buildings.show', $building));

        $this->assertDatabaseHas('buildings', [
            'id' => $building->id,
            'name' => 'Gedung Baru',
            'is_active' => false,
        ]);
    }

    public function test_building_with_rooms_cannot_be_deleted(): void
    {
        $building = Building::factory()->create();
        Room::factory()->create(['building_id' => $building->id]);

        $this->actingAs($this->operator)
            ->from(route('buildings.show', $building))
            ->delete(route('buildings.destroy', $building))
            ->assertRedirect(route('buildings.show', $building))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('buildings', ['id' => $building->id]);
    }

    public function test_empty_building_can_be_deleted(): void
    {
        $building = Building::factory()->create();

        $this->actingAs($this->operator)
            ->delete(route('buildings.destroy', $building))
            ->assertRedirect(route('buildings.index'));

        $this->assertDatabaseMissing('buildings', ['id' => $building->id]);
    }
}
