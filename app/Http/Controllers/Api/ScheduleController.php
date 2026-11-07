<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\DefenseSession;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ScheduleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);

        $sort = in_array($request->input('sort'), ['date', 'start_time', 'created_at'], true)
            ? $request->input('sort')
            : 'date';

        $sessions = DefenseSession::query()
            ->with(['room.building', 'creator'])
            ->filter($request->only(['q', 'room_id', 'building_id', 'status', 'type', 'date_from', 'date_to']))
            ->orderBy($sort, $request->input('direction') === 'desc' ? 'desc' : 'asc')
            ->orderBy('start_time')
            ->paginate($perPage)
            ->withQueryString();

        return ScheduleResource::collection($sessions);
    }

    public function show(DefenseSession $schedule): ScheduleResource
    {
        $schedule->load(['room.building', 'creator']);

        return new ScheduleResource($schedule);
    }

    public function store(ScheduleRequest $request): JsonResponse
    {
        $session = DefenseSession::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        ActivityLogger::log('schedule.created', $session, "Menjadwalkan {$session->title} melalui API.");

        return (new ScheduleResource($session->load(['room.building', 'creator'])))
            ->response()
            ->setStatusCode(201);
    }

    public function update(ScheduleRequest $request, DefenseSession $schedule): ScheduleResource
    {
        $schedule->update($request->validated());
        ActivityLogger::log('schedule.updated', $schedule, "Memperbarui jadwal {$schedule->title} melalui API.");

        return new ScheduleResource($schedule->load(['room.building', 'creator']));
    }

    public function destroy(DefenseSession $schedule): JsonResponse
    {
        $label = "{$schedule->title} ({$schedule->student_name})";
        $schedule->delete();
        ActivityLogger::log('schedule.deleted', null, "Menghapus jadwal {$label} melalui API.");

        return response()->json(['message' => 'Jadwal sidang berhasil dihapus.']);
    }
}
