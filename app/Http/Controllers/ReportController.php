<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\DefenseSession;
use App\Models\Room;
use App\Support\ModuleStats;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $month = $this->resolveMonth($request);
        $utilization = ModuleStats::utilizationReport($month);

        return view('reports.index', [
            'month' => $month,
            'utilization' => $utilization,
            'conditions' => ModuleStats::facilityConditionBreakdown(),
            'inventory' => ModuleStats::facilityInventory(),
            'overview' => ModuleStats::overview(),
            'buildingStats' => Building::withCount('rooms')
                ->withSum('rooms as capacity_total', 'capacity')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function print(Request $request): View
    {
        $month = $this->resolveMonth($request);
        $utilization = ModuleStats::utilizationReport($month);

        return view('reports.print', [
            'month' => $month,
            'utilization' => $utilization,
            'conditions' => ModuleStats::facilityConditionBreakdown(),
            'inventory' => ModuleStats::facilityInventory(),
            'overview' => ModuleStats::overview(),
            'printedAt' => now(),
        ]);
    }

    public function exportRooms(): StreamedResponse
    {
        $rooms = Room::with(['building', 'facilities'])
            ->orderBy('code')
            ->get();

        return response()->streamDownload(function () use ($rooms) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Kode Ruang', 'Nama Ruang', 'Gedung', 'Lantai', 'Jenis', 'Kapasitas',
                'Status', 'Jumlah Fasilitas', 'Fasilitas Baik', 'Fasilitas Rusak Ringan',
                'Fasilitas Rusak Berat', 'Catatan',
            ], ',', '"', '\\');

            foreach ($rooms as $room) {
                $conditions = $room->facilityConditions();

                fputcsv($handle, [
                    $room->code,
                    $room->name,
                    $room->building?->name,
                    $room->floor,
                    $room->type_label,
                    $room->capacity,
                    $room->status_label,
                    $room->facilities->count(),
                    $conditions['baik'],
                    $conditions['rusak_ringan'],
                    $conditions['rusak_berat'],
                    $room->notes,
                ], ',', '"', '\\');
            }

            fclose($handle);
        }, 'data-ruang-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportSchedules(Request $request): StreamedResponse
    {
        $filters = $request->only(['q', 'room_id', 'building_id', 'status', 'type', 'date_from', 'date_to']);

        $sessions = DefenseSession::query()
            ->with(['room.building'])
            ->filter($filters)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        return response()->streamDownload(function () use ($sessions) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Tanggal', 'Mulai', 'Selesai', 'Ruang', 'Gedung', 'Kegiatan', 'Jenis',
                'Mahasiswa', 'NIM', 'Pembimbing', 'Penguji', 'Status', 'Catatan',
            ], ',', '"', '\\');

            foreach ($sessions as $session) {
                fputcsv($handle, [
                    $session->date->format('Y-m-d'),
                    $session->start_time_label,
                    $session->end_time_label,
                    $session->room?->code,
                    $session->room?->building?->name,
                    $session->title,
                    $session->type_label,
                    $session->student_name,
                    $session->student_nim,
                    $session->supervisor_name,
                    $session->examiner_name,
                    $session->status_label,
                    $session->notes,
                ], ',', '"', '\\');
            }

            fclose($handle);
        }, 'jadwal-sidang-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function resolveMonth(Request $request): Carbon
    {
        $value = (string) $request->input('month', now()->format('Y-m'));

        if (preg_match('/^\d{4}-\d{2}$/', $value)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value.'-01')->startOfMonth();
            } catch (\Throwable) {
                // fall through
            }
        }

        return now()->startOfMonth();
    }
}
