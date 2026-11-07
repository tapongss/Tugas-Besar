<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomRequest;
use App\Models\ActivityLog;
use App\Models\Building;
use App\Models\DefenseSession;
use App\Models\Facility;
use App\Models\Room;
use App\Support\ActivityLogger;
use App\Support\ModuleStats;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'building_id', 'type', 'status', 'facility_id', 'min_capacity']);

        $sort = in_array($request->input('sort'), ['code', 'name', 'capacity', 'floor', 'created_at'], true)
            ? $request->input('sort')
            : 'code';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $rooms = Room::query()
            ->with(['building', 'facilities'])
            ->withCount(['defenseSessions as upcoming_count' => fn ($query) => $query->upcoming()])
            ->filter($filters)
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('rooms.index', [
            'rooms' => $rooms,
            'filters' => $filters,
            'sort' => $sort,
            'direction' => $direction,
            'buildings' => Building::orderBy('name')->get(),
            'facilities' => Facility::where('is_active', true)->orderBy('name')->get(),
            'summary' => [
                'total' => Room::count(),
                'available' => Room::where('status', 'tersedia')->count(),
                'repair' => Room::where('status', 'perbaikan')->count(),
                'capacity' => (int) Room::sum('capacity'),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('rooms.create', [
            'room' => new Room([
                'building_id' => $request->integer('building_id') ?: null,
                'floor' => 1,
                'capacity' => 20,
                'status' => 'tersedia',
                'type' => 'ruang_sidang',
            ]),
            'buildings' => Building::where('is_active', true)->orderBy('name')->get(),
            'facilities' => Facility::where('is_active', true)->orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function store(RoomRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $facilities = $data['facilities'] ?? [];
        unset($data['facilities']);

        $room = Room::create($data);
        $this->syncFacilities($room, $facilities);

        ActivityLogger::log('room.created', $room, "Menambahkan ruang {$room->code} — {$room->name}.", [
            'building' => $room->building?->name,
            'facilities' => count($facilities),
        ]);

        return redirect()->route('rooms.show', $room)
            ->with('success', "Ruang {$room->name} berhasil ditambahkan.");
    }

    public function show(Room $room): View
    {
        $room->load(['building', 'facilities']);

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $monthlySessions = $room->defenseSessions()
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->where('status', '!=', DefenseSession::STATUS_CANCELLED)
            ->get();

        $monthlyMinutes = (int) $monthlySessions->sum(fn (DefenseSession $session) => $session->duration);
        $officeMinutes = ModuleStats::officeMinutesInMonth($monthStart);

        return view('rooms.show', [
            'room' => $room,
            'conditions' => $room->facilityConditions(),
            'upcomingSessions' => $room->upcomingSessions()->with('creator')->limit(6)->get(),
            'historySessions' => $room->defenseSessions()
                ->with('creator')
                ->orderByDesc('date')
                ->orderByDesc('start_time')
                ->limit(8)
                ->get(),
            'todaySessions' => $room->defenseSessions()
                ->whereDate('date', today())
                ->where('status', '!=', DefenseSession::STATUS_CANCELLED)
                ->orderBy('start_time')
                ->get(),
            'monthlySessions' => $monthlySessions->count(),
            'monthlyMinutes' => $monthlyMinutes,
            'utilization' => $officeMinutes > 0 ? min(100, round($monthlyMinutes / $officeMinutes * 100, 1)) : 0,
            'activities' => ActivityLog::with('user')
                ->where('subject_type', $room->getMorphClass())
                ->where('subject_id', $room->id)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function edit(Room $room): View
    {
        $room->load('facilities');

        return view('rooms.edit', [
            'room' => $room,
            'buildings' => Building::orderBy('name')->get(),
            'facilities' => Facility::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function update(RoomRequest $request, Room $room): RedirectResponse
    {
        $data = $request->validated();
        $facilities = $data['facilities'] ?? [];
        unset($data['facilities']);

        $room->update($data);
        $this->syncFacilities($room, $facilities);

        ActivityLogger::log('room.updated', $room, "Memperbarui ruang {$room->code} — {$room->name}.");

        return redirect()->route('rooms.show', $room)
            ->with('success', 'Data ruang berhasil diperbarui.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        if ($room->defenseSessions()->exists()) {
            return back()->with('error', 'Ruang tidak dapat dihapus karena masih memiliki riwayat jadwal sidang. Nonaktifkan ruang jika sudah tidak digunakan.');
        }

        $label = "{$room->code} — {$room->name}";
        $room->delete();

        ActivityLogger::log('room.deleted', null, "Menghapus ruang {$label}.");

        return redirect()->route('rooms.index')
            ->with('success', "Ruang {$label} berhasil dihapus.");
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
