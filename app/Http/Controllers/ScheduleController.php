<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleRequest;
use App\Models\ActivityLog;
use App\Models\Building;
use App\Models\DefenseSession;
use App\Models\Room;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['q', 'room_id', 'building_id', 'status', 'type', 'date_from', 'date_to']);
        $viewMode = $request->input('view') === 'kalender' ? 'kalender' : 'daftar';

        $sessions = DefenseSession::query()
            ->with(['room.building', 'creator'])
            ->filter($filters)
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->paginate(10)
            ->withQueryString();

        $calendar = null;

        if ($viewMode === 'kalender') {
            $monthInput = (string) $request->input('month', now()->format('Y-m'));
            $month = preg_match('/^\d{4}-\d{2}$/', $monthInput)
                ? Carbon::createFromFormat('Y-m-d', $monthInput.'-01')->startOfMonth()
                : now()->startOfMonth();

            $calendarFilters = $filters;
            unset($calendarFilters['date_from'], $calendarFilters['date_to']);

            $calendarSessions = DefenseSession::query()
                ->with(['room.building'])
                ->filter($calendarFilters)
                ->whereBetween('date', [
                    $month->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
                    $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY)->toDateString(),
                ])
                ->orderBy('start_time')
                ->get()
                ->groupBy(fn (DefenseSession $session) => $session->date->toDateString());

            $calendar = [
                'month' => $month,
                'prev' => $month->copy()->subMonth()->format('Y-m'),
                'next' => $month->copy()->addMonth()->format('Y-m'),
                'weeks' => $this->buildCalendarWeeks($month, $calendarSessions),
                'total' => $calendarSessions->flatten()->count(),
            ];
        }

        return view('schedules.index', [
            'sessions' => $sessions,
            'filters' => $filters,
            'viewMode' => $viewMode,
            'calendar' => $calendar,
            'buildings' => Building::orderBy('name')->get(),
            'rooms' => Room::with('building')->orderBy('code')->get(),
            'summary' => [
                'total' => DefenseSession::count(),
                'today' => DefenseSession::whereDate('date', today())->where('status', '!=', DefenseSession::STATUS_CANCELLED)->count(),
                'upcoming' => DefenseSession::upcoming()->count(),
                'cancelled' => DefenseSession::where('status', DefenseSession::STATUS_CANCELLED)->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('schedules.create', [
            'session' => new DefenseSession([
                'room_id' => $request->integer('room_id') ?: null,
                'date' => $request->input('date', today()->toDateString()),
                'start_time' => '09:00',
                'end_time' => '11:00',
                'status' => DefenseSession::STATUS_SCHEDULED,
                'type' => 'skripsi',
            ]),
            'rooms' => $this->roomOptions(),
        ]);
    }

    public function store(ScheduleRequest $request): RedirectResponse
    {
        $session = DefenseSession::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        $session->load('room.building');

        ActivityLogger::log('schedule.created', $session, "Menjadwalkan {$session->title} di {$session->room->code} pada {$session->date->translatedFormat('d M Y')}.", [
            'room' => $session->room->full_name,
            'time' => $session->time_range,
        ]);

        return redirect()->route('schedules.show', $session)
            ->with('success', 'Jadwal sidang berhasil dibuat.');
    }

    public function show(DefenseSession $schedule): View
    {
        $schedule->load(['room.building', 'creator']);

        return view('schedules.show', [
            'session' => $schedule,
            'roomSessions' => $schedule->room->defenseSessions()
                ->whereDate('date', $schedule->date)
                ->whereKeyNot($schedule->id)
                ->where('status', '!=', DefenseSession::STATUS_CANCELLED)
                ->orderBy('start_time')
                ->get(),
            'activities' => ActivityLog::with('user')
                ->where('subject_type', $schedule->getMorphClass())
                ->where('subject_id', $schedule->id)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function edit(DefenseSession $schedule): View
    {
        return view('schedules.edit', [
            'session' => $schedule,
            'rooms' => $this->roomOptions($schedule->room_id),
        ]);
    }

    public function update(ScheduleRequest $request, DefenseSession $schedule): RedirectResponse
    {
        $schedule->update($request->validated());

        ActivityLogger::log('schedule.updated', $schedule, "Memperbarui jadwal {$schedule->title} di {$schedule->room->code}.");

        return redirect()->route('schedules.show', $schedule)
            ->with('success', 'Jadwal sidang berhasil diperbarui.');
    }

    public function destroy(DefenseSession $schedule): RedirectResponse
    {
        $label = "{$schedule->title} ({$schedule->student_name})";
        $schedule->delete();

        ActivityLogger::log('schedule.deleted', null, "Menghapus jadwal {$label}.");

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal sidang berhasil dihapus.');
    }

    public function updateStatus(Request $request, DefenseSession $schedule): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(DefenseSession::statuses()))],
        ], [
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status jadwal tidak valid.',
        ]);

        $old = $schedule->status;
        $schedule->update(['status' => $data['status']]);

        ActivityLogger::log(
            'schedule.updated',
            $schedule,
            "Mengubah status jadwal {$schedule->title} dari ".DefenseSession::statuses()[$old].' menjadi '.$schedule->status_label.'.'
        );

        return back()->with('success', "Status jadwal berhasil diubah menjadi {$schedule->status_label}.");
    }

    private function roomOptions(?int $includeRoomId = null)
    {
        return Room::with('building')
            ->where(function ($query) use ($includeRoomId) {
                $query->where('status', 'tersedia');
                if ($includeRoomId) {
                    $query->orWhere('id', $includeRoomId);
                }
            })
            ->orderBy('code')
            ->get();
    }

    /**
     * @return array<int, array<int, array{date: Carbon, inMonth: bool, isToday: bool, sessions: \Illuminate\Support\Collection}>
     */
    private function buildCalendarWeeks(Carbon $month, Collection $sessions): array
    {
        $cursor = $month->copy()->startOfWeek(Carbon::MONDAY);
        $end = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];
        $week = [];

        while ($cursor->lessThanOrEqualTo($end)) {
            $week[] = [
                'date' => $cursor->copy(),
                'inMonth' => $cursor->month === $month->month,
                'isToday' => $cursor->isToday(),
                'sessions' => $sessions->get($cursor->toDateString(), collect()),
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }

            $cursor->addDay();
        }

        return $weeks;
    }
}
