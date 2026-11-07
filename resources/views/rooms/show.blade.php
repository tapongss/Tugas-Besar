@extends('layouts.app')

@section('title', $room->name)

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Ruang', 'url' => route('rooms.index')], ['label' => $room->code]]" />
@endsection

@section('content')
    @php
        $liveLabel = \App\Models\Room::statuses()[$room->live_status] ?? \Illuminate\Support\Str::headline($room->live_status);
        $totalFacilities = $room->facilities->count();
        $damaged = $conditions['rusak_ringan'] + $conditions['rusak_berat'];
        $hasSessions = $room->defenseSessions()->exists();
    @endphp

    <div class="space-y-5">
        <x-page-header :title="$room->name" :subtitle="$room->building->name.' · '.$room->floor_label.' · '.$room->type_label" icon="door">
            <x-slot:actions>
                <a href="{{ route('rooms.index') }}" class="btn-secondary btn-sm">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali
                </a>
                @can('manage-module')
                    <a href="{{ route('schedules.create', ['room_id' => $room->id]) }}" class="btn-secondary btn-sm">
                        <x-icon name="calendar-days" class="h-4 w-4" />
                        Jadwalkan
                    </a>
                    <a href="{{ route('rooms.edit', $room) }}" class="btn-primary btn-sm">
                        <x-icon name="pencil" class="h-4 w-4" />
                        Edit Ruang
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-ink-900 via-ink-900 to-brand-900 p-5 text-white shadow-lg shadow-brand-950/15 sm:p-6">
            <div class="bg-grid pointer-events-none absolute inset-0 opacity-25"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-brand-300 ring-1 ring-white/15">
                        <x-icon name="door" class="h-7 w-7" />
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-white/10 px-2 py-0.5 text-[11px] font-bold tracking-wide text-brand-200 ring-1 ring-white/15">{{ $room->code }}</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full {{ $room->live_status === 'tersedia' ? 'bg-emerald-400/10 text-emerald-300 ring-emerald-400/20' : ($room->live_status === 'digunakan' ? 'bg-sky-400/10 text-sky-300 ring-sky-400/20' : 'bg-amber-400/10 text-amber-300 ring-amber-400/20') }} px-2.5 py-1 text-[11px] font-semibold ring-1">
                                <span class="h-1.5 w-1.5 rounded-full {{ $room->live_status === 'tersedia' ? 'bg-emerald-400' : ($room->live_status === 'digunakan' ? 'bg-sky-400' : 'bg-amber-400') }}"></span>
                                {{ $liveLabel }}
                            </span>
                        </div>
                        <p class="mt-2 text-lg font-semibold">{{ $room->name }}</p>
                        <a href="{{ route('buildings.show', $room->building) }}" class="mt-0.5 inline-flex items-center gap-1 text-xs text-slate-400 hover:text-brand-300">
                            <x-icon name="building" class="h-3.5 w-3.5" />
                            {{ $room->building->name }} · {{ $room->floor_label }}
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:gap-7">
                    <div>
                        <p class="text-2xl font-bold">{{ $room->capacity }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Orang</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $totalFacilities }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Fasilitas</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $monthlySessions }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Sesi bulan ini</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $utilization }}%</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Utilisasi</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="space-y-5 xl:col-span-2">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Fasilitas ruangan</h2>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $totalFacilities }} jenis fasilitas · {{ $conditions['baik'] }} baik · {{ $damaged }} perlu perhatian</p>
                        </div>
                        @can('manage-module')
                            <a href="{{ route('rooms.edit', $room) }}" class="btn-secondary btn-sm">
                                <x-icon name="pencil" class="h-3.5 w-3.5" />
                                Kelola
                            </a>
                        @endcan
                    </div>

                    @if ($room->facilities->isEmpty())
                        <x-empty-state icon="chair" title="Belum ada fasilitas" description="Ruang ini belum memiliki fasilitas terdata. Tambahkan proyektor, PC, atau fasilitas lain melalui menu edit.">
                            <x-slot:actions>
                                @can('manage-module')
                                    <a href="{{ route('rooms.edit', $room) }}" class="btn-primary btn-sm">Tambah Fasilitas</a>
                                @endcan
                            </x-slot:actions>
                        </x-empty-state>
                    @else
                        <div class="table-scroll">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Fasilitas</th>
                                        <th>Kategori</th>
                                        <th class="text-center">Jumlah</th>
                                        <th>Kondisi</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($room->facilities as $facility)
                                        <tr>
                                            <td>
                                                <div class="flex items-center gap-2.5">
                                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-500 ring-1 ring-slate-200/70">
                                                        <x-icon :name="$facility->icon_name" class="h-4 w-4" />
                                                    </span>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-medium text-slate-800">{{ $facility->name }}</p>
                                                        <p class="text-[10px] text-slate-400">{{ $facility->code }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-xs text-slate-600">{{ $facility->category_label }}</td>
                                            <td class="text-center text-xs font-semibold text-slate-800">{{ $facility->pivot->quantity }} <span class="font-normal text-slate-400">{{ $facility->unit }}</span></td>
                                            <td><x-status-badge :status="$facility->pivot->condition" :label="\App\Models\Facility::conditions()[$facility->pivot->condition] ?? $facility->pivot->condition" /></td>
                                            <td class="max-w-48 text-xs text-slate-500">{{ $facility->pivot->note ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Jadwal hari ini</h2>
                            <p class="mt-0.5 text-xs text-slate-500">{{ now()->translatedFormat('l, d F Y') }}</p>
                        </div>
                        <a href="{{ route('schedules.index', ['room_id' => $room->id, 'date_from' => today()->toDateString(), 'date_to' => today()->toDateString()]) }}" class="text-xs font-semibold text-brand-700 hover:text-brand-800">Lihat semua</a>
                    </div>

                    @if ($todaySessions->isEmpty())
                        <div class="flex items-center gap-3 px-5 py-6 sm:px-6">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400 ring-1 ring-slate-200">
                                <x-icon name="calendar" class="h-4 w-4" />
                            </span>
                            <div>
                                <p class="text-sm font-medium text-slate-700">Ruang bebas hari ini</p>
                                <p class="text-xs text-slate-500">Tidak ada sidang yang dijadwalkan pada ruang ini.</p>
                            </div>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach ($todaySessions as $session)
                                <a href="{{ route('schedules.show', $session) }}" class="group flex items-center gap-4 px-5 py-3.5 transition hover:bg-slate-50/80 sm:px-6">
                                    <div class="flex w-14 shrink-0 flex-col items-center rounded-lg bg-slate-50 py-1.5 ring-1 ring-slate-100">
                                        <span class="text-xs font-bold text-slate-800">{{ $session->start_time_label }}</span>
                                        <span class="text-[9px] text-slate-400">{{ $session->end_time_label }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-800 group-hover:text-brand-700">{{ $session->title }}</p>
                                        <p class="truncate text-[11px] text-slate-400">{{ $session->student_name }} · {{ $session->student_nim }}</p>
                                    </div>
                                    <x-status-badge :status="$session->status" :label="$session->status_label" />
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Riwayat jadwal</h2>
                            <p class="mt-0.5 text-xs text-slate-500">8 jadwal terakhir di ruang ini</p>
                        </div>
                        <a href="{{ route('schedules.index', ['room_id' => $room->id]) }}" class="text-xs font-semibold text-brand-700 hover:text-brand-800">Semua jadwal</a>
                    </div>

                    @if ($historySessions->isEmpty())
                        <x-empty-state icon="calendar-days" title="Belum ada riwayat" description="Jadwal sidang yang menggunakan ruang ini akan tampil di sini." />
                    @else
                        <div class="table-scroll">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Kegiatan</th>
                                        <th>Mahasiswa</th>
                                        <th>Waktu</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($historySessions as $session)
                                        <tr>
                                            <td class="whitespace-nowrap text-xs text-slate-600">{{ $session->date->translatedFormat('d M Y') }}</td>
                                            <td>
                                                <a href="{{ route('schedules.show', $session) }}" class="block max-w-64 truncate text-xs font-medium text-slate-800 hover:text-brand-700">{{ $session->title }}</a>
                                                <p class="text-[10px] text-slate-400">{{ $session->type_label }}</p>
                                            </td>
                                            <td class="text-xs text-slate-600">{{ $session->student_name }}</td>
                                            <td class="whitespace-nowrap text-xs text-slate-600">{{ $session->time_range }}</td>
                                            <td><x-status-badge :status="$session->status" :label="$session->status_label" /></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-5">
                <div class="card card-pad">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-900">Utilisasi bulan ini</h2>
                        <span class="rounded-md bg-slate-50 px-2 py-1 text-[11px] font-semibold text-slate-500 ring-1 ring-slate-200/70">{{ now()->translatedFormat('F Y') }}</span>
                    </div>

                    <div class="mt-5 flex items-center justify-center">
                        <div class="relative flex h-32 w-32 items-center justify-center rounded-full" style="background: conic-gradient(#0d9488 {{ $utilization }}%, #e2e8f0 0)">
                            <div class="flex h-24 w-24 flex-col items-center justify-center rounded-full bg-white">
                                <span class="text-2xl font-bold text-slate-900">{{ $utilization }}%</span>
                                <span class="text-[9px] font-semibold uppercase tracking-wider text-slate-400">Terpakai</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-center">
                        <div>
                            <p class="text-lg font-bold text-slate-900">{{ $monthlySessions }}</p>
                            <p class="text-[10px] uppercase tracking-wide text-slate-400">Sesi</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-slate-900">{{ \App\Support\ModuleStats::formatMinutes($monthlyMinutes) }}</p>
                            <p class="text-[10px] uppercase tracking-wide text-slate-400">Durasi</p>
                        </div>
                    </div>
                    <p class="mt-3 text-[10px] leading-relaxed text-slate-400">Dihitung dari jam operasional 08.00–16.00 pada hari kerja.</p>
                </div>

                <div class="card card-pad">
                    <h2 class="text-sm font-semibold text-slate-900">Detail ruang</h2>
                    <dl class="mt-4 space-y-3.5 text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <dt class="text-slate-500">Gedung</dt>
                            <dd class="text-right">
                                <a href="{{ route('buildings.show', $room->building) }}" class="font-medium text-brand-700 hover:text-brand-800">{{ $room->building->name }}</a>
                                <p class="text-[10px] text-slate-400">{{ $room->building->code }}</p>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Lantai</dt>
                            <dd class="font-medium text-slate-800">{{ $room->floor_label }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Jenis</dt>
                            <dd class="font-medium text-slate-800">{{ $room->type_label }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Kapasitas</dt>
                            <dd class="font-medium text-slate-800">{{ $room->capacity }} orang</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Status master</dt>
                            <dd><x-status-badge :status="$room->status" :label="$room->status_label" /></dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Catatan</dt>
                            <dd class="mt-1 text-xs leading-relaxed text-slate-600">{{ $room->notes ?: 'Tidak ada catatan tambahan.' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Jadwal mendatang</h2>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $upcomingSessions->count() }} jadwal</p>
                        </div>
                    </div>
                    <div class="p-5">
                        @if ($upcomingSessions->isEmpty())
                            <p class="text-xs leading-relaxed text-slate-500">Belum ada jadwal sidang mendatang di ruang ini.</p>
                        @else
                            <ul class="space-y-3">
                                @foreach ($upcomingSessions as $session)
                                    <li>
                                        <a href="{{ route('schedules.show', $session) }}" class="group flex items-start gap-3 rounded-xl p-2 transition hover:bg-slate-50">
                                            <span class="flex h-9 w-9 shrink-0 flex-col items-center justify-center rounded-lg bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                                                <span class="text-xs font-bold leading-none">{{ $session->date->format('d') }}</span>
                                                <span class="text-[8px] font-semibold uppercase">{{ $session->date->translatedFormat('M') }}</span>
                                            </span>
                                            <div class="min-w-0">
                                                <p class="truncate text-xs font-semibold text-slate-800 group-hover:text-brand-700">{{ $session->title }}</p>
                                                <p class="text-[11px] text-slate-400">{{ $session->time_range }} · {{ $session->student_name }}</p>
                                            </div>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="section-title">Aktivitas</h2>
                    </div>
                    <div class="p-5">
                        @if ($activities->isEmpty())
                            <p class="text-xs leading-relaxed text-slate-500">Belum ada aktivitas terkait ruang ini.</p>
                        @else
                            <ul class="space-y-3">
                                @foreach ($activities as $activity)
                                    <li class="flex items-start gap-2.5">
                                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-50 text-slate-400 ring-1 ring-slate-200">
                                            <x-icon :name="$activity->icon" class="h-3 w-3" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-[11px] leading-relaxed text-slate-600">{{ $activity->description }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $activity->created_at->diffForHumans() }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                @can('manage-module')
                    @if (! $hasSessions)
                        <div class="card border border-rose-100 p-5">
                            <h2 class="text-sm font-semibold text-slate-900">Hapus ruang</h2>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500">Ruang ini belum memiliki riwayat jadwal sehingga dapat dihapus permanen.</p>
                            <form method="POST" action="{{ route('rooms.destroy', $room) }}" class="mt-3" data-confirm="Ruang {{ $room->name }} beserta seluruh data fasilitasnya akan dihapus permanen." data-confirm-title="Hapus ruang?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger-soft btn-sm">
                                    <x-icon name="trash" class="h-3.5 w-3.5" />
                                    Hapus Ruang
                                </button>
                            </form>
                        </div>
                    @endif
                @endcan
            </div>
        </div>
    </div>
@endsection
