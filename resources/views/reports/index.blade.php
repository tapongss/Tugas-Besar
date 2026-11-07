@extends('layouts.app')

@section('title', 'Laporan & Statistik')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Laporan & Statistik']]" />
@endsection

@section('content')
    @php
        $monthLabel = $utilization['month']->translatedFormat('F Y');
        $conditionTotal = max(1, collect($conditions)->sum('count'));
        $reportQuery = ['month' => $month->format('Y-m')];
        $scheduleQuery = [
            'date_from' => $month->copy()->startOfMonth()->toDateString(),
            'date_to' => $month->copy()->endOfMonth()->toDateString(),
        ];
        $maxPercent = max(1, (float) $utilization['rows']->max('percent'));
    @endphp

    <div class="space-y-5">
        <x-page-header title="Laporan & Statistik" subtitle="Rekapitulasi utilisasi ruang, kondisi fasilitas, dan distribusi kapasitas per gedung." icon="chart">
            <x-slot:actions>
                <a href="{{ route('reports.rooms.csv') }}" class="btn-secondary btn-sm">
                    <x-icon name="download" class="h-4 w-4" />
                    CSV Ruang
                </a>
                <a href="{{ route('reports.schedules.csv', $scheduleQuery) }}" class="btn-secondary btn-sm">
                    <x-icon name="download" class="h-4 w-4" />
                    CSV Jadwal
                </a>
                <a href="{{ route('reports.print', $reportQuery) }}" class="btn-primary btn-sm">
                    <x-icon name="printer" class="h-4 w-4" />
                    Cetak Laporan
                </a>
            </x-slot:actions>
        </x-page-header>

        <form method="GET" action="{{ route('reports.index') }}" class="card flex flex-col gap-3 card-pad sm:flex-row sm:items-end sm:justify-between">
            <div class="flex flex-col gap-1.5 sm:flex-row sm:items-center sm:gap-4">
                <div>
                    <label for="month" class="label">Periode laporan</label>
                    <input id="month" type="month" name="month" value="{{ $month->format('Y-m') }}" max="{{ now()->format('Y-m') }}" data-auto-submit class="input w-full sm:w-52">
                </div>
                <p class="text-xs leading-relaxed text-slate-500 sm:max-w-md">
                    Menampilkan data {{ $monthLabel }} · {{ number_format($utilization['office_minutes'] / 60, 0, ',', '.') }} jam kerja efektif sebagai basis perhitungan utilisasi.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn-primary btn-sm">
                    <x-icon name="filter" class="h-4 w-4" />
                    Tampilkan
                </button>
                <a href="{{ route('reports.index') }}" class="btn-secondary btn-sm">Bulan Ini</a>
            </div>
        </form>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Total Sesi Sidang" :value="$overview['sessions_total']" icon="calendar-days" tone="brand" :hint="$overview['sessions_week'].' jadwal dalam 7 hari ke depan'" :href="route('schedules.index')" />
            <x-stat-card label="Jam Terpakai" :value="\App\Support\ModuleStats::formatMinutes($utilization['total_minutes'])" icon="clock" tone="sky" :hint="'Dari '.number_format($utilization['office_minutes'] / 60, 0, ',', '.').' jam kerja pada '.$monthLabel" />
            <x-stat-card label="Rata-rata Utilisasi" :value="$utilization['average'].'%'" icon="chart" tone="violet" hint="Rata-rata seluruh ruang terdaftar" />
            <x-stat-card label="Unit Fasilitas" :value="number_format($overview['facility_units'], 0, ',', '.')" icon="chair" tone="amber" :hint="$overview['facilities'].' jenis fasilitas master'" :href="route('facilities.index')" />
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="card-header">
                    <div>
                        <h2 class="section-title">Utilisasi ruang</h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $monthLabel }} · Dihitung dari durasi sidang yang tidak dibatalkan.</p>
                    </div>
                    <span class="rounded-lg bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-500 ring-1 ring-slate-200/70">{{ $utilization['rows']->count() }} ruang</span>
                </div>

                @if ($utilization['rows']->isEmpty())
                    <x-empty-state icon="chart" title="Belum ada ruang terdaftar" description="Tambahkan data ruang terlebih dahulu untuk melihat laporan utilisasi.">
                        <x-slot:actions>
                            @can('manage-module')
                                <a href="{{ route('rooms.create') }}" class="btn-primary btn-sm">
                                    <x-icon name="plus" class="h-4 w-4" />
                                    Tambah Ruang
                                </a>
                            @endcan
                        </x-slot:actions>
                    </x-empty-state>
                @else
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ruang</th>
                                    <th>Sesi</th>
                                    <th>Durasi</th>
                                    <th class="w-40">Utilisasi</th>
                                    <th class="text-right">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($utilization['rows'] as $row)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-500 ring-1 ring-slate-200/70">
                                                    <x-icon name="door" class="h-4 w-4" />
                                                </span>
                                                <div class="min-w-0">
                                                    <a href="{{ route('rooms.show', $row['room']) }}" class="block truncate text-sm font-semibold text-slate-800 hover:text-brand-700">{{ $row['room']->code }} — {{ $row['room']->name }}</a>
                                                    <p class="truncate text-[11px] text-slate-400">{{ $row['room']->building?->name }} · {{ $row['room']->capacity }} orang</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-700">
                                                <x-icon name="calendar" class="h-3.5 w-3.5 text-slate-400" />
                                                {{ $row['sessions'] }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap text-xs text-slate-600">{{ \App\Support\ModuleStats::formatMinutes($row['minutes']) }}</td>
                                        <td>
                                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-600" style="width: {{ max($row['minutes'] > 0 ? 3 : 0, round($row['percent'] / $maxPercent * 100)) }}%"></div>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <span class="text-xs font-semibold text-slate-800">{{ $row['percent'] }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t border-slate-200 bg-slate-50/70">
                                <tr>
                                    <td class="px-4 py-3 text-xs font-semibold text-slate-700">Total</td>
                                    <td class="px-4 py-3 text-xs font-semibold text-slate-700">{{ $utilization['rows']->sum('sessions') }}</td>
                                    <td class="px-4 py-3 text-xs font-semibold text-slate-700">{{ \App\Support\ModuleStats::formatMinutes($utilization['total_minutes']) }}</td>
                                    <td></td>
                                    <td class="px-4 py-3 text-right text-xs font-bold text-slate-900">{{ $utilization['average'] }}%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>

            <div class="space-y-5">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Kondisi fasilitas</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Penempatan unit di seluruh ruang</p>
                        </div>
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
                                    <p class="mt-1 text-[10px] text-slate-400">{{ $condition['count'] }} penempatan · {{ round($condition['count'] / $conditionTotal * 100, 1) }}%</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Distribusi gedung</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Ruang dan kapasitas per gedung</p>
                        </div>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($buildingStats as $building)
                            <a href="{{ route('buildings.show', $building) }}" class="group flex items-center gap-3 px-5 py-3.5 transition hover:bg-slate-50/80">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-500 ring-1 ring-slate-200/70">
                                    <x-icon name="building" class="h-4 w-4" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-800 group-hover:text-brand-700">{{ $building->name }}</p>
                                    <p class="text-[11px] text-slate-400">{{ $building->code }} · {{ $building->rooms_count }} ruang</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-900">{{ number_format((int) $building->capacity_total, 0, ',', '.') }}</p>
                                    <p class="text-[9px] uppercase tracking-wide text-slate-400">kapasitas</p>
                                </div>
                            </a>
                        @empty
                            <p class="px-5 py-6 text-xs text-slate-500">Belum ada data gedung.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="section-title">Inventaris fasilitas</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Rekapitulasi unit terpasang dan jumlah ruang yang menggunakan.</p>
                </div>
                <a href="{{ route('facilities.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800">
                    Kelola fasilitas
                    <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            </div>

            @if ($inventory->isEmpty())
                <x-empty-state icon="chair" title="Belum ada fasilitas" description="Tambahkan master fasilitas untuk mulai mencatat inventaris ruang." />
            @else
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fasilitas</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Ruang</th>
                                <th class="text-right">Total Unit</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($inventory as $facility)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-500 ring-1 ring-slate-200/70">
                                                <x-icon :name="$facility->icon_name" class="h-4 w-4" />
                                            </span>
                                            <div class="min-w-0">
                                                <a href="{{ route('facilities.show', $facility) }}" class="block truncate text-sm font-semibold text-slate-800 hover:text-brand-700">{{ $facility->name }}</a>
                                                <p class="text-[11px] text-slate-400">{{ $facility->code }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="text-xs text-slate-600">{{ $facility->category_label }}</span></td>
                                    <td><span class="text-xs text-slate-600">{{ $facility->unit ?: '—' }}</span></td>
                                    <td><span class="text-xs font-medium text-slate-700">{{ $facility->rooms_count }}</span></td>
                                    <td class="text-right"><span class="text-sm font-bold text-slate-900">{{ number_format($facility->total_units, 0, ',', '.') }}</span></td>
                                    <td>
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $facility->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200/70' : 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $facility->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            {{ $facility->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
