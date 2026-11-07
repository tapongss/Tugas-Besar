<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Building;
use App\Models\DefenseSession;
use App\Models\Facility;
use App\Models\Room;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ModuleStats
{
    /**
     * Ringkasan angka utama untuk dashboard.
     *
     * @return array<string, int|float>
     */
    public static function overview(): array
    {
        $todaySessions = DefenseSession::whereDate('date', today())
            ->where('status', '!=', DefenseSession::STATUS_CANCELLED);

        return [
            'buildings' => Building::count(),
            'buildings_active' => Building::where('is_active', true)->count(),
            'rooms' => Room::count(),
            'rooms_available' => Room::where('status', 'tersedia')->count(),
            'rooms_repair' => Room::where('status', 'perbaikan')->count(),
            'rooms_inactive' => Room::where('status', 'nonaktif')->count(),
            'capacity' => (int) Room::sum('capacity'),
            'facilities' => Facility::count(),
            'facility_units' => (int) DB::table('facility_room')->sum('quantity'),
            'sessions_today' => (clone $todaySessions)->count(),
            'sessions_week' => DefenseSession::whereDate('date', '>=', today())
                ->whereDate('date', '<=', today()->addDays(6))
                ->where('status', '!=', DefenseSession::STATUS_CANCELLED)
                ->count(),
            'sessions_total' => DefenseSession::count(),
        ];
    }

    /**
     * Distribusi status ruang untuk donut chart.
     *
     * @return array<int, array{label: string, key: string, count: int, percent: float, color: string}>
     */
    public static function roomStatusBreakdown(): array
    {
        $counts = Room::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = max(1, (int) $counts->sum());
        $meta = [
            'tersedia' => ['Tersedia', '#0d9488'],
            'perbaikan' => ['Perbaikan', '#f59e0b'],
            'nonaktif' => ['Nonaktif', '#94a3b8'],
        ];

        $result = [];
        foreach ($meta as $key => [$label, $color]) {
            $count = (int) ($counts[$key] ?? 0);
            $result[] = [
                'key' => $key,
                'label' => $label,
                'count' => $count,
                'percent' => round($count / $total * 100, 1),
                'color' => $color,
            ];
        }

        return $result;
    }

    /**
     * Distribusi kondisi seluruh fasilitas yang terpasang di ruangan.
     *
     * @return array<string, array{label: string, count: int, units: int, color: string, tone: string}>
     */
    public static function facilityConditionBreakdown(): array
    {
        $rows = DB::table('facility_room')
            ->select('condition', DB::raw('count(*) as total'), DB::raw('sum(quantity) as units'))
            ->groupBy('condition')
            ->get()
            ->keyBy('condition');

        $meta = [
            'baik' => ['Baik', '#0d9488', 'emerald'],
            'rusak_ringan' => ['Rusak Ringan', '#f59e0b', 'amber'],
            'rusak_berat' => ['Rusak Berat', '#e11d48', 'rose'],
        ];

        $result = [];
        foreach ($meta as $key => [$label, $color, $tone]) {
            $result[$key] = [
                'label' => $label,
                'count' => (int) ($rows[$key]->total ?? 0),
                'units' => (int) ($rows[$key]->units ?? 0),
                'color' => $color,
                'tone' => $tone,
            ];
        }

        return $result;
    }

    /**
     * Ruang dengan penggunaan tertinggi pada bulan berjalan.
     *
     * @return Collection<int, array{room: Room, sessions: int, minutes: int, percent: float}>
     */
    public static function topRooms(int $limit = 5): Collection
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $sessions = DefenseSession::with('room.building')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('status', '!=', DefenseSession::STATUS_CANCELLED)
            ->get()
            ->groupBy('room_id');

        $officeMinutes = self::officeMinutesInMonth($start);

        return Room::with('building')
            ->get()
            ->map(function (Room $room) use ($sessions, $officeMinutes) {
                $items = $sessions->get($room->id, collect());
                $minutes = (int) $items->sum(fn (DefenseSession $session) => $session->duration);

                return [
                    'room' => $room,
                    'sessions' => $items->count(),
                    'minutes' => $minutes,
                    'percent' => $officeMinutes > 0 ? min(100, round($minutes / $officeMinutes * 100, 1)) : 0,
                ];
            })
            ->sortByDesc('minutes')
            ->take($limit)
            ->values();
    }

    /**
     * Jadwal terdekat.
     */
    public static function upcomingSessions(int $limit = 6): Collection
    {
        return DefenseSession::with(['room.building'])
            ->upcoming()
            ->limit($limit)
            ->get();
    }

    /**
     * Aktivitas terbaru pada modul.
     */
    public static function recentActivities(int $limit = 6): Collection
    {
        return ActivityLog::with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Jumlah sidang 7 hari ke depan untuk grafik mini.
     *
     * @return array<int, array{date: Carbon, label: string, count: int}>
     */
    public static function weeklyAgenda(): array
    {
        $start = today();
        $end = today()->addDays(6);

        $counts = DefenseSession::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('status', '!=', DefenseSession::STATUS_CANCELLED)
            ->selectRaw('date, count(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $days[] = [
                'date' => $date,
                'label' => $date->translatedFormat('D'),
                'count' => (int) ($counts[$date->toDateString()] ?? 0),
            ];
        }

        return $days;
    }

    /**
     * Laporan utilisasi seluruh ruang pada bulan tertentu.
     *
     * @return array{rows: Collection<int, array{room: Room, sessions: int, minutes: int, percent: float}>, month: Carbon, office_minutes: int, total_minutes: int, average: float}
     */
    public static function utilizationReport(?Carbon $month = null): array
    {
        $month = ($month ?? now())->copy()->startOfMonth();
        $officeMinutes = self::officeMinutesInMonth($month);

        $sessions = DefenseSession::whereBetween('date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->where('status', '!=', DefenseSession::STATUS_CANCELLED)
            ->get()
            ->groupBy('room_id');

        $rows = Room::with(['building', 'facilities'])
            ->orderBy('code')
            ->get()
            ->map(function (Room $room) use ($sessions, $officeMinutes) {
                $items = $sessions->get($room->id, collect());
                $minutes = (int) $items->sum(fn (DefenseSession $session) => $session->duration);

                return [
                    'room' => $room,
                    'sessions' => $items->count(),
                    'minutes' => $minutes,
                    'percent' => $officeMinutes > 0 ? min(100, round($minutes / $officeMinutes * 100, 1)) : 0,
                ];
            });

        $totalMinutes = (int) $rows->sum('minutes');

        return [
            'rows' => $rows->sortByDesc('minutes')->values(),
            'month' => $month,
            'office_minutes' => $officeMinutes,
            'total_minutes' => $totalMinutes,
            'average' => $rows->count() > 0 ? round($rows->avg('percent'), 1) : 0,
        ];
    }

    /**
     * Inventaris fasilitas lengkap dengan kondisi.
     */
    public static function facilityInventory(): Collection
    {
        $units = DB::table('facility_room')
            ->select('facility_id', DB::raw('sum(quantity) as units'))
            ->groupBy('facility_id')
            ->pluck('units', 'facility_id');

        return Facility::withCount('rooms')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->each(fn (Facility $facility) => $facility->total_units = (int) ($units[$facility->id] ?? 0));
    }

    public static function officeMinutesInMonth(Carbon $month): int
    {
        $workdays = 0;
        $cursor = $month->copy()->startOfMonth();

        while ($cursor->month === $month->month) {
            if ($cursor->isWeekday()) {
                $workdays++;
            }
            $cursor->addDay();
        }

        return $workdays * 8 * 60;
    }

    /**
     * Format menit menjadi "2j 30m".
     */
    public static function formatMinutes(int $minutes): string
    {
        if ($minutes <= 0) {
            return '0m';
        }

        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return trim(($hours > 0 ? $hours.'j ' : '').($rest > 0 ? $rest.'m' : ''));
    }
}
