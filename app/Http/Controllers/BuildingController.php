<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuildingRequest;
use App\Models\ActivityLog;
use App\Models\Building;
use App\Models\DefenseSession;
use App\Models\Facility;
use App\Models\Room;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuildingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'status']);

        $buildings = Building::query()
            ->withCount('rooms')
            ->withSum('rooms as capacity_total', 'capacity')
            ->filter($filters)
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        return view('buildings.index', [
            'buildings' => $buildings,
            'filters' => $filters,
            'summary' => [
                'total' => Building::count(),
                'active' => Building::where('is_active', true)->count(),
                'rooms' => Room::count(),
                'capacity' => (int) Room::sum('capacity'),
                'facilities' => Facility::count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('buildings.create', [
            'building' => new Building(['floors' => 1, 'is_active' => true]),
        ]);
    }

    public function store(BuildingRequest $request): RedirectResponse
    {
        $building = Building::create($request->validated());

        ActivityLogger::log('building.created', $building, "Menambahkan gedung {$building->code} — {$building->name}.");

        return redirect()->route('buildings.show', $building)
            ->with('success', "Gedung {$building->name} berhasil ditambahkan.");
    }

    public function show(Building $building): View
    {
        $building->load([
            'rooms' => fn ($query) => $query
                ->withCount('facilities')
                ->withCount(['defenseSessions as upcoming_count' => fn ($query) => $query->upcoming()])
                ->orderBy('floor')
                ->orderBy('code'),
        ]);

        return view('buildings.show', [
            'building' => $building,
            'activities' => ActivityLog::with('user')
                ->where('subject_type', $building->getMorphClass())
                ->where('subject_id', $building->id)
                ->latest()
                ->limit(6)
                ->get(),
            'upcomingSessions' => DefenseSession::with(['room'])
                ->whereHas('room', fn ($query) => $query->where('building_id', $building->id))
                ->upcoming()
                ->limit(5)
                ->get(),
        ]);
    }

    public function edit(Building $building): View
    {
        return view('buildings.edit', compact('building'));
    }

    public function update(BuildingRequest $request, Building $building): RedirectResponse
    {
        $building->update($request->validated());

        ActivityLogger::log('building.updated', $building, "Memperbarui data gedung {$building->code} — {$building->name}.");

        return redirect()->route('buildings.show', $building)
            ->with('success', 'Data gedung berhasil diperbarui.');
    }

    public function destroy(Building $building): RedirectResponse
    {
        if ($building->rooms()->exists()) {
            return back()->with('error', 'Gedung tidak dapat dihapus karena masih memiliki ruang terdaftar.');
        }

        $label = "{$building->code} — {$building->name}";
        $building->delete();

        ActivityLogger::log('building.deleted', null, "Menghapus gedung {$label}.");

        return redirect()->route('buildings.index')
            ->with('success', "Gedung {$label} berhasil dihapus.");
    }
}
