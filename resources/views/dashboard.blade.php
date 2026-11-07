@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Dashboard']]" />
@endsection

@section('content')
    @php
        $hour = now()->hour;
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
        $firstName = \Illuminate\Support\Str::of(auth()->user()->name)
            ->replaceMatches('/\b(prof|dr|drs|dra|ir|hj|h|apt)\.?\s+/i', '')
            ->trim()
            ->explode(' ')
            ->first() ?: 'Pengguna';
        $ongoing = $ongoingSessions->first();
        $weeklyMax = max(1, collect($weeklyAgenda)->max('count'));
        $conditionTotal = max(1, collect($conditions)->sum('count'));
    @endphp

    <div class="space-y-6">
        <x-page-header :title="$greeting.', '.$firstName" :subtitle="now()->translatedFormat('l, d F Y').' · Ringkasan pengelolaan ruang dan fasilitas sidang.'" icon="dashboard">
            <x-slot:actions>
                @can('manage-module')
                    <a href="{{ route('rooms.create') }}" class="btn-secondary btn-sm">
                        <x-icon name="plus" class="h-4 w-4" />
                        Ruang Baru
                    </a>
                    <a href="{{ route('schedules.create') }}" class="btn-primary btn-sm">
                        <x-icon name="calendar-days" class="h-4 w-4" />
                        Jadwal Sidang
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>

        @if ($ongoing)
            <a href="{{ route('schedules.show', $ongoing) }}" class="group relative block overflow-hidden rounded-2xl bg-gradient-to-r from-ink-900 via-ink-900 to-brand-900 p-5 shadow-lg shadow-brand-950/20 sm:p-6">
                <div class="bg-grid pointer-events-none absolute inset-0 opacity-30"></div>
                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-4">
                        <span class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-500/20 text-brand-300 ring-1 ring-brand-400/30">
                            <span class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-400 ring-2 ring-ink-900"></span>
                            <x-icon name="play" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-brand-300">Sedang berlangsung</p>
                            <p class="mt-1 truncate text-base font-semibold text-white sm:text-lg">{{ $ongoing->title }}</p>
                            <p class="mt-1 text-xs text-slate-400">
                                {{ $ongoing->student_name }} · {{ $ongoing->student_nim }} · {{ $ongoing->room->code }} {{ $ongoing->room->name }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 pl-15 sm:pl-0">
                        <div class="text-right">
                            <p class="text-lg font-bold text-white">{{ $ongoing->time_range }}</p>
                            <p class="text-[11px] text-slate-400">{{ $ongoing->type_label }}</p>
                        </div>
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white transition group-hover:bg-white/20">
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </span>
                    </div>
                </div>
            </a>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Total Ruang" :value="$overview['rooms']" icon="door" tone="brand" :hint="$overview['rooms_available'].' tersedia · '.$overview['rooms_repair'].' perbaikan'" :href="route('rooms.index')" />
            <x-stat-card label="Kapasitas Total" :value="number_format($overview['capacity'], 0, ',', '.')" icon="users" tone="sky" :hint="$overview['buildings'].' gedung terdaftar'" :href="route('buildings.index')" />
            <x-stat-card label="Fasilitas Terdata" :value="$overview['facilities']" icon="chair" tone="violet" :hint="$overview['facility_units'].' unit terpasang di ruang'" :href="route('facilities.index')" />
            <x-stat-card label="Jadwal Hari Ini" :value="$overview['sessions_today']" icon="calendar-days" tone="amber" :hint="$overview['sessions_week'].' jadwal dalam 7 hari ke depan'" :href="route('schedules.index')" />
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="card-header">
                    <div>
                        <h2 class="section-title">Agenda sidang hari ini</h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ now()->translatedFormat('l, d F Y') }} · {{ $todaySessions->count() }} kegiatan</p>
                    </div>
                    <a href="{{ route('schedules.index', ['date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800">
                        Lihat semua
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </div>

                @if ($todaySessions->isEmpty())
                    <x-empty-state icon="calendar" title="Belum ada agenda hari ini" description="Tidak ada sidang yang dijadwalkan pada tanggal ini. Anda dapat membuat jadwal baru kapan saja.">
                        <x-slot:actions>
                            @can('manage-module')
                                <a href="{{ route('schedules.create') }}" class="btn-primary btn-sm">
                                    <x-icon name="plus" class="h-4 w-4" />
                                    Buat Jadwal
                                </a>
                            @endcan
                        </x-slot:actions>
                    </x-empty-state>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($todaySessions as $session)
                            <a href="{{ route('schedules.show', $session) }}" class="group flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50/80 sm:px-6">
                                <div class="flex w-14 shrink-0 flex-col items-center rounded-xl bg-slate-50 py-2 ring-1 ring-slate-100">
                                    <span class="text-sm font-bold text-slate-900">{{ $session->start_time_label }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $session->end_time_label }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-900 group-hover:text-brand-700">{{ $session->title }}</p>
                                    <p class="mt-0.5 truncate text-xs text-slate-500">{{ $session->student_name }} · {{ $session->student_nim }}</p>
                                </div>
                                <div class="hidden shrink-0 items-center gap-2 sm:flex">
                                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 px-2.5 py-1.5 text-[11px] font-medium text-slate-600 ring-1 ring-slate-200/70">
                                        <x-icon name="map-pin" class="h-3.5 w-3.5 text-slate-400" />
                                        {{ $session->room->code }}
                                    </span>
                                    <x-status-badge :status="$session->status" :label="$session->status_label" />
                                </div>
                                <x-icon name="chevron-right" class="h-4 w-4 shrink-0 text-slate-300 transition group-hover:text-brand-600" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="section-title">Status ruang</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Distribusi ketersediaan</p>
                    </div>
                    <span class="rounded-lg bg-slate-50 px-2 py-1 text-[11px] font-semibold text-slate-500 ring-1 ring-slate-200/70">{{ $overview['rooms'] }} ruang</span>
                </div>

                <div class="card-pad">
                    <div class="flex items-center justify-center">
                        <div class="relative h-40 w-40 rounded-full" style="background: {{ $donutGradient }}">
                            <div class="absolute inset-4 flex flex-col items-center justify-center rounded-full bg-white shadow-inner">
                                <span class="text-3xl font-bold tracking-tight text-slate-900">{{ $overview['rooms'] }}</span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total ruang</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        @foreach ($breakdown as $item)
                            <div class="flex items-center gap-2.5">
                                <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $item['color'] }}"></span>
                                <span class="flex-1 text-xs font-medium text-slate-600">{{ $item['label'] }}</span>
                                <span class="text-xs font-semibold text-slate-900">{{ $item['count'] }}</span>
                                <span class="w-10 text-right text-[11px] text-slate-400">{{ $item['percent'] }}%</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-4">
                        <p class="mb-3 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Agenda 7 hari</p>
                        <div class="flex h-16 items-end gap-1.5">
                            @foreach ($weeklyAgenda as $day)
                                <div class="group flex flex-1 flex-col items-center gap-1.5">
                                    <div class="relative flex w-full flex-1 items-end">
                                        <div class="w-full rounded-md bg-brand-500/20 transition group-hover:bg-brand-500/40" style="height: {{ max(8, round($day['count'] / $weeklyMax * 100)) }}%">
                                            <div class="h-full w-full rounded-md bg-brand-600/80 transition group-hover:bg-brand-600" style="opacity: {{ $day['count'] > 0 ? 1 : 0 }}"></div>
                                        </div>
                                        <span class="absolute -top-4 left-1/2 hidden -translate-x-1/2 text-[10px] font-semibold text-slate-600 group-hover:block">{{ $day['count'] }}</span>
                                    </div>
                                    <span class="text-[9px] font-medium {{ $day['date']->isToday() ? 'text-brand-700' : 'text-slate-400' }}">{{ $day['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <a href="{{ route('rooms.index') }}" class="mt-5 inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-slate-200 py-2 text-xs font-semibold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700">
                        Kelola ruang
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </div>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="card-header">
                    <div>
                        <h2 class="section-title">Utilisasi ruang bulan ini</h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ now()->translatedFormat('F Y') }} · Berdasarkan jam pemakaian sidang</p>
                    </div>
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800">
                        Laporan lengkap
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </div>

                <div class="card-pad space-y-5">
                    @forelse ($topRooms as $item)
                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-500 ring-1 ring-slate-200/70">
                                        <x-icon name="door" class="h-4 w-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <a href="{{ route('rooms.show', $item['room']) }}" class="block truncate text-sm font-semibold text-slate-800 hover:text-brand-700">{{ $item['room']->code }} — {{ $item['room']->name }}</a>
                                        <p class="truncate text-[11px] text-slate-400">{{ $item['room']->building->name }} · {{ $item['sessions'] }} sesi · {{ \App\Support\ModuleStats::formatMinutes($item['minutes']) }}</p>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-md bg-slate-50 px-2 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200/60">{{ $item['percent'] }}%</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-600 transition-all duration-700" style="width: {{ max(2, $item['percent']) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state icon="chart" title="Belum ada data utilisasi" description="Utilisasi akan muncul setelah ada jadwal sidang pada bulan ini." />
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="section-title">Kondisi fasilitas</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Seluruh unit terpasang</p>
                    </div>
                    <a href="{{ route('facilities.index') }}" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-50 hover:text-slate-700" aria-label="Kelola fasilitas">
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                <div class="card-pad">
                    <div class="space-y-5">
                        @foreach ($conditions as $condition)
                            <div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $condition['color'] }}"></span>
                                        <span class="text-xs font-medium text-slate-600">{{ $condition['label'] }}</span>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-900">{{ $condition['units'] }} unit</span>
                                </div>
                                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full" style="width: {{ max($condition['count'] > 0 ? 3 : 0, round($condition['count'] / $conditionTotal * 100)) }}%; background: {{ $condition['color'] }}"></div>
                                </div>
                                <p class="mt-1 text-[10px] text-slate-400">{{ $condition['count'] }} penempatan fasilitas</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 rounded-xl bg-amber-50 p-3.5 ring-1 ring-amber-100">
                        <div class="flex items-start gap-2.5">
                            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                            <div>
                                <p class="text-xs font-semibold text-amber-800">{{ $overview['rooms_repair'] }} ruang dalam perbaikan</p>
                                <p class="mt-0.5 text-[11px] leading-relaxed text-amber-700">Ruang berstatus perbaikan tidak dapat dijadwalkan untuk sidang.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="card-header">
                    <div>
                        <h2 class="section-title">Jadwal terdekat</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Sidang yang akan datang</p>
                    </div>
                    <a href="{{ route('schedules.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800">
                        Semua jadwal
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </div>

                @if ($upcomingSessions->isEmpty())
                    <x-empty-state icon="calendar-days" title="Tidak ada jadwal mendatang" description="Belum ada sidang yang dijadwalkan setelah hari ini." />
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($upcomingSessions as $session)
                            <a href="{{ route('schedules.show', $session) }}" class="group flex items-center gap-4 px-5 py-3.5 transition hover:bg-slate-50/80 sm:px-6">
                                <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                                    <span class="text-sm font-bold leading-none">{{ $session->date->format('d') }}</span>
                                    <span class="mt-0.5 text-[9px] font-semibold uppercase">{{ $session->date->translatedFormat('M') }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-900 group-hover:text-brand-700">{{ $session->title }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $session->student_name }} · {{ $session->room->code }} · {{ $session->time_range }}</p>
                                </div>
                                <x-status-badge :status="$session->status" :label="$session->status_label" class="hidden sm:inline-flex" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="section-title">Aktivitas terbaru</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Jejak perubahan modul</p>
                    </div>
                </div>

                <div class="card-pad">
                    @if ($recentActivities->isEmpty())
                        <x-empty-state icon="activity" title="Belum ada aktivitas" description="Perubahan data ruang dan fasilitas akan tercatat di sini." />
                    @else
                        <ol class="relative space-y-5 before:absolute before:bottom-2 before:left-[15px] before:top-2 before:w-px before:bg-slate-200">
                            @foreach ($recentActivities as $activity)
                                @php
                                    $toneMap = [
                                        'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
                                        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
                                        'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
                                        'sky' => 'bg-sky-50 text-sky-600 ring-sky-100',
                                        'slate' => 'bg-slate-50 text-slate-500 ring-slate-200',
                                    ];
                                @endphp
                                <li class="relative flex gap-3">
                                    <span class="z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full ring-4 ring-white {{ $toneMap[$activity->tone] ?? $toneMap['slate'] }}">
                                        <x-icon :name="$activity->icon" class="h-3.5 w-3.5" />
                                    </span>
                                    <div class="min-w-0 pt-0.5">
                                        <p class="text-xs leading-relaxed text-slate-700">{{ $activity->description }}</p>
                                        <p class="mt-1 text-[10px] text-slate-400">{{ $activity->user?->name ?? 'Sistem' }} · {{ $activity->created_at->diffForHumans() }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
