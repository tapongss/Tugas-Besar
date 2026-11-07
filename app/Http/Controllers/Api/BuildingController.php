<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuildingRequest;
use App\Http\Resources\BuildingResource;
use App\Models\Building;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BuildingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        $sort = in_array($request->input('sort'), ['name', 'code', 'created_at'], true)
            ? $request->input('sort')
            : 'name';

        $buildings = Building::query()
            ->withCount('rooms')
            ->withSum('rooms as capacity_total', 'capacity')
            ->filter($request->only(['q', 'status']))
            ->orderBy($sort, $request->input('direction') === 'desc' ? 'desc' : 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return BuildingResource::collection($buildings);
    }

    public function show(Building $building): BuildingResource
    {
        $building->load([
            'rooms' => fn ($query) => $query->with('facilities')->orderBy('code'),
        ])->loadCount('rooms');

        return new BuildingResource($building);
    }

    public function store(BuildingRequest $request): JsonResponse
    {
        $building = Building::create($request->validated());
        ActivityLogger::log('building.created', $building, "Menambahkan gedung {$building->code} melalui API.");

        return (new BuildingResource($building->loadCount('rooms')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(BuildingRequest $request, Building $building): BuildingResource
    {
        $building->update($request->validated());
        ActivityLogger::log('building.updated', $building, "Memperbarui gedung {$building->code} melalui API.");

        return new BuildingResource($building->loadCount('rooms'));
    }

    public function destroy(Building $building): JsonResponse
    {
        if ($building->rooms()->exists()) {
            return response()->json([
                'message' => 'Gedung tidak dapat dihapus karena masih memiliki ruang terdaftar.',
            ], 409);
        }

        $label = "{$building->code} — {$building->name}";
        $building->delete();
        ActivityLogger::log('building.deleted', null, "Menghapus gedung {$label} melalui API.");

        return response()->json(['message' => 'Gedung berhasil dihapus.']);
    }
}
