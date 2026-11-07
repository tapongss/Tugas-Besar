<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacilityRequest;
use App\Models\ActivityLog;
use App\Models\Facility;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FacilityController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'category', 'status']);

        $facilities = Facility::query()
            ->withCount('rooms')
            ->filter($filters)
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $units = DB::table('facility_room')
            ->select('facility_id', DB::raw('sum(quantity) as units'))
            ->groupBy('facility_id')
            ->pluck('units', 'facility_id');

        $facilities->getCollection()->each(
            fn (Facility $facility) => $facility->total_units = (int) ($units[$facility->id] ?? 0)
        );

        $categoryCounts = Facility::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('facilities.index', [
            'facilities' => $facilities,
            'filters' => $filters,
            'categoryCounts' => $categoryCounts,
            'summary' => [
                'total' => Facility::count(),
                'active' => Facility::where('is_active', true)->count(),
                'units' => (int) DB::table('facility_room')->sum('quantity'),
                'damaged' => (int) DB::table('facility_room')->whereIn('condition', ['rusak_ringan', 'rusak_berat'])->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('facilities.create', [
            'facility' => new Facility(['category' => 'display', 'unit' => 'unit', 'is_active' => true]),
        ]);
    }

    public function store(FacilityRequest $request): RedirectResponse
    {
        $facility = Facility::create($request->validated());

        ActivityLogger::log('facility.created', $facility, "Menambahkan fasilitas {$facility->name}.");

        return redirect()->route('facilities.index')
            ->with('success', "Fasilitas {$facility->name} berhasil ditambahkan.");
    }

    public function show(Facility $facility): View
    {
        $facility->load(['rooms' => fn ($query) => $query->with('building')->orderBy('code')]);
        $facility->loadCount('rooms');
        $facility->total_units = (int) DB::table('facility_room')
            ->where('facility_id', $facility->id)
            ->sum('quantity');

        $conditions = collect(Facility::conditions())
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'placements' => $facility->rooms->filter(fn ($room) => $room->pivot->condition === $key)->count(),
                'units' => (int) $facility->rooms->filter(fn ($room) => $room->pivot->condition === $key)->sum(fn ($room) => $room->pivot->quantity),
            ]);

        return view('facilities.show', [
            'facility' => $facility,
            'conditions' => $conditions,
            'activities' => ActivityLog::with('user')
                ->where('subject_type', $facility->getMorphClass())
                ->where('subject_id', $facility->id)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function edit(Facility $facility): View
    {
        $facility->loadCount('rooms');
        $facility->total_units = (int) DB::table('facility_room')
            ->where('facility_id', $facility->id)
            ->sum('quantity');

        return view('facilities.edit', [
            'facility' => $facility,
            'roomsUsing' => $facility->rooms()->with('building')->orderBy('code')->get(),
        ]);
    }

    public function update(FacilityRequest $request, Facility $facility): RedirectResponse
    {
        $facility->update($request->validated());

        ActivityLogger::log('facility.updated', $facility, "Memperbarui fasilitas {$facility->name}.");

        return redirect()->route('facilities.index')
            ->with('success', 'Data fasilitas berhasil diperbarui.');
    }

    public function destroy(Facility $facility): RedirectResponse
    {
        if ($facility->rooms()->exists()) {
            return back()->with('error', 'Fasilitas tidak dapat dihapus karena masih terpasang pada ruang. Lepaskan dari ruang terlebih dahulu.');
        }

        $name = $facility->name;
        $facility->delete();

        ActivityLogger::log('facility.deleted', null, "Menghapus fasilitas {$name}.");

        return redirect()->route('facilities.index')
            ->with('success', "Fasilitas {$name} berhasil dihapus.");
    }
}
