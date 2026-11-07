<?php

namespace App\Http\Controllers;

use App\Models\DefenseSession;
use App\Support\ModuleStats;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $overview = ModuleStats::overview();
        $breakdown = ModuleStats::roomStatusBreakdown();

        $segments = [];
        $offset = 0;
        foreach ($breakdown as $item) {
            if ($item['count'] === 0) {
                continue;
            }
            $segments[] = "{$item['color']} {$offset}% ".($offset + $item['percent']).'%';
            $offset += $item['percent'];
        }

        $donutGradient = $segments
            ? 'conic-gradient('.implode(', ', $segments).')'
            : 'conic-gradient(#e2e8f0 0% 100%)';

        return view('dashboard', [
            'overview' => $overview,
            'breakdown' => $breakdown,
            'donutGradient' => $donutGradient,
            'conditions' => ModuleStats::facilityConditionBreakdown(),
            'topRooms' => ModuleStats::topRooms(5),
            'weeklyAgenda' => ModuleStats::weeklyAgenda(),
            'upcomingSessions' => ModuleStats::upcomingSessions(5),
            'recentActivities' => ModuleStats::recentActivities(6),
            'todaySessions' => DefenseSession::with(['room.building'])
                ->whereDate('date', today())
                ->where('status', '!=', DefenseSession::STATUS_CANCELLED)
                ->orderBy('start_time')
                ->get(),
            'ongoingSessions' => DefenseSession::with(['room.building'])
                ->whereDate('date', today())
                ->where('status', DefenseSession::STATUS_ONGOING)
                ->orderBy('start_time')
                ->get(),
        ]);
    }
}
