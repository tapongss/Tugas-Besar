<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacilityRequest;
use App\Http\Resources\FacilityResource;
use App\Models\Facility;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class FacilityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        $sort = in_array($request->input('sort'), ['name', 'code', 'category', 'created_at'], true)
            ? $request->input('sort')
            : 'name';

        $facilities = Facility::query()
            ->withCount('rooms')
            ->filter($request->only(['q', 'category', 'status']))
            ->orderBy($sort, $request->input('direction') === 'desc' ? 'desc' : 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $units = DB::table('facility_room')
            ->select('facility_id', DB::raw('sum(quantity) as units'))
            ->groupBy('facility_id')
            ->pluck('units', 'facility_id');

        $facilities->getCollection()->each(
            fn (Facility $facility) => $facility->total_units = (int) ($units[$facility->id] ?? 0)
        );

        return FacilityResource::collection($facilities);
    }

    public function show(Facility $facility): FacilityResource
    {
        $facility->load(['rooms.building'])->loadCount('rooms');
        $facility->total_units = (int) DB::table('facility_room')
            ->where('facility_id', $facility->id)
            ->sum('quantity');

        return new FacilityResource($facility);
    }

    public function store(FacilityRequest $request): JsonResponse
    {
        $facility = Facility::create($request->validated());
        ActivityLogger::log('facility.created', $facility, "Menambahkan fasilitas {$facility->name} melalui API.");

        return (new FacilityResource($facility->loadCount('rooms')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(FacilityRequest $request, Facility $facility): FacilityResource
    {
        $facility->update($request->validated());
        ActivityLogger::log('facility.updated', $facility, "Memperbarui fasilitas {$facility->name} melalui API.");

        return new FacilityResource($facility->loadCount('rooms'));
    }

    public function destroy(Facility $facility): JsonResponse
    {
        if ($facility->rooms()->exists()) {
            return response()->json([
                'message' => 'Fasilitas tidak dapat dihapus karena masih terpasang pada ruang.',
            ], 409);
        }

        $name = $facility->name;
        $facility->delete();
        ActivityLogger::log('facility.deleted', null, "Menghapus fasilitas {$name} melalui API.");

        return response()->json(['message' => 'Fasilitas berhasil dihapus.']);
    }
}
