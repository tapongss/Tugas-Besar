<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Facility;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private function tokenFor(User $user, string $token = 'token-uji'): string
    {
        $user->forceFill(['api_token' => hash('sha256', $token)])->save();

        return $token;
    }

    public function test_public_endpoints_are_accessible_without_token(): void
    {
        $building = Building::factory()->create();
        Room::factory()->create(['building_id' => $building->id, 'code' => 'API-01']);

        $this->getJson('/api/v1')->assertOk()->assertJsonStructure(['name', 'version', 'resources']);
        $this->getJson('/api/v1/buildings')->assertOk()->assertJsonStructure(['data']);
        $this->getJson('/api/v1/rooms')->assertOk()->assertJsonStructure(['data']);
        $this->getJson('/api/v1/facilities')->assertOk()->assertJsonStructure(['data']);
        $this->getJson('/api/v1/schedules')->assertOk()->assertJsonStructure(['data']);
    }

    public function test_room_list_can_be_filtered_and_paginated(): void
    {
        $building = Building::factory()->create();
        $matching = Room::factory()->create(['building_id' => $building->id, 'code' => 'API-A', 'status' => 'tersedia']);
        Room::factory()->maintenance()->create(['building_id' => $building->id, 'code' => 'API-B']);

        $response = $this->getJson('/api/v1/rooms?status=tersedia&per_page=5');

        $response->assertOk()
            ->assertJsonPath('data.0.code', $matching->code)
            ->assertJsonCount(1, 'data');
    }

    public function test_write_endpoint_requires_token(): void
    {
        $this->postJson('/api/v1/buildings', [
            'code' => 'API-X',
            'name' => 'Gedung API',
            'floors' => 2,
        ])->assertUnauthorized();
    }

    public function test_invalid_token_is_rejected(): void
    {
        $this->withToken('token-palsu')
            ->postJson('/api/v1/buildings', [
                'code' => 'API-X',
                'name' => 'Gedung API',
                'floors' => 2,
            ])
            ->assertUnauthorized();
    }

    public function test_viewer_token_cannot_write(): void
    {
        $viewer = User::factory()->viewer()->create();
        $token = $this->tokenFor($viewer);

        $this->withToken($token)
            ->postJson('/api/v1/buildings', [
                'code' => 'API-X',
                'name' => 'Gedung API',
                'floors' => 2,
            ])
            ->assertForbidden();
    }

    public function test_admin_token_can_create_building(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $this->tokenFor($admin);

        $this->withToken($token)
            ->postJson('/api/v1/buildings', [
                'code' => 'api-z',
                'name' => 'Gedung API',
                'address' => 'Jl. API',
                'floors' => 3,
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'API-Z');

        $this->assertDatabaseHas('buildings', ['code' => 'API-Z', 'name' => 'Gedung API']);
    }

    public function test_validation_errors_are_returned_as_json(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $this->tokenFor($admin);

        $this->withToken($token)
            ->postJson('/api/v1/buildings', ['code' => '', 'floors' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'name', 'floors']);
    }

    public function test_admin_token_can_create_room_with_facilities(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $this->tokenFor($admin);
        $building = Building::factory()->create();
        $facility = Facility::factory()->create();

        $this->withToken($token)
            ->postJson('/api/v1/rooms', [
                'building_id' => $building->id,
                'code' => 'api-room',
                'name' => 'Ruang API',
                'floor' => 1,
                'type' => 'ruang_sidang',
                'capacity' => 25,
                'status' => 'tersedia',
                'facilities' => [
                    ['facility_id' => $facility->id, 'quantity' => 2, 'condition' => 'baik'],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'API-ROOM');

        $this->assertDatabaseHas('facility_room', [
            'facility_id' => $facility->id,
            'quantity' => 2,
        ]);
    }

    public function test_operator_token_can_create_but_cannot_delete(): void
    {
        $operator = User::factory()->create();
        $token = $this->tokenFor($operator);

        $response = $this->withToken($token)
            ->postJson('/api/v1/buildings', [
                'code' => 'API-OP',
                'name' => 'Gedung Operator',
                'floors' => 2,
            ])
            ->assertCreated();

        $buildingId = $response->json('data.id');

        $this->withToken($token)
            ->deleteJson("/api/v1/buildings/{$buildingId}")
            ->assertForbidden();

        $this->assertDatabaseHas('buildings', ['id' => $buildingId]);
    }

    public function test_admin_token_can_delete_building(): void
    {
        $admin = User::factory()->admin()->create();
        $token = $this->tokenFor($admin);
        $building = Building::factory()->create();

        $this->withToken($token)
            ->deleteJson("/api/v1/buildings/{$building->id}")
            ->assertOk();

        $this->assertDatabaseMissing('buildings', ['id' => $building->id]);
    }

    public function test_inactive_user_token_is_rejected(): void
    {
        $user = User::factory()->inactive()->create();
        $token = $this->tokenFor($user);

        $this->withToken($token)
            ->postJson('/api/v1/buildings', [
                'code' => 'API-X',
                'name' => 'Gedung API',
                'floors' => 2,
            ])
            ->assertUnauthorized();
    }
}
