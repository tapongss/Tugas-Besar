<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoomController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        $sort = in_array($request->input('sort'), ['code', 'name', 'capacity', 'floor', 'created_at'], true)
            ? $request->input('sort')
            : 'code';

        $rooms = Room::query()
            ->with(['building', 'facilities'])
            ->withCount(['defenseSessions' => fn ($query) => $query->upcoming()])
            ->filter($request->only(['q', 'building_id', 'type', 'status', 'facility_id', 'min_capacity']))
            ->orderBy($sort, $request->input('direction') === 'desc' ? 'desc' : 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return RoomResource::collection($rooms);
    }

    public function show(Room $room): RoomResource
    {
        $room->load(['building', 'facilities'])->loadCount([
            'defenseSessions' => fn ($query) => $query->upcoming(),
        ]);

        return new RoomResource($room);
    }

    public function store(RoomRequest $request): JsonResponse
    {
        $data = $request->validated();
        $facilities = $data['facilities'] ?? [];
        unset($data['facilities']);

        $room = Room::create($data);
        $this->syncFacilities($room, $facilities);

        ActivityLogger::log('room.created', $room, "Menambahkan ruang {$room->code} melalui API.");

        return (new RoomResource($room->load(['building', 'facilities'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(RoomRequest $request, Room $room): RoomResource
    {
        $data = $request->validated();
        $facilities = $data['facilities'] ?? [];
        unset($data['facilities']);

        $room->update($data);
        $this->syncFacilities($room, $facilities);

        ActivityLogger::log('room.updated', $room, "Memperbarui ruang {$room->code} melalui API.");

        return new RoomResource($room->load(['building', 'facilities']));
    }

    public function destroy(Room $room): JsonResponse
    {
        if ($room->defenseSessions()->exists()) {
            return response()->json([
                'message' => 'Ruang tidak dapat dihapus karena masih memiliki riwayat jadwal sidang.',
            ], 409);
        }

        $label = "{$room->code} — {$room->name}";
        $room->delete();
        ActivityLogger::log('room.deleted', null, "Menghapus ruang {$label} melalui API.");

        return response()->json(['message' => 'Ruang berhasil dihapus.']);
    }

    /**
     * @param  array<int, array{facility_id: int, quantity: int, condition: string, note?: string|null}>  $facilities
     */
    private function syncFacilities(Room $room, array $facilities): void
    {
        $payload = collect($facilities)
            ->keyBy('facility_id')
            ->map(fn (array $item) => [
                'quantity' => $item['quantity'],
                'condition' => $item['condition'],
                'note' => $item['note'] ?? null,
            ])
            ->all();

        $room->facilities()->sync($payload);
    }
}
