@extends('layouts.app')

@section('title', $building->name)

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Gedung', 'url' => route('buildings.index')], ['label' => $building->name]]" />
@endsection

@section('content')
    <div class="space-y-5">
        <x-page-header :title="$building->name" :subtitle="$building->address ?: 'Alamat gedung belum dilengkapi.'" icon="building">
            <x-slot:actions>
                <a href="{{ route('buildings.index') }}" class="btn-secondary btn-sm">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali
                </a>
                @can('manage-module')
                    <a href="{{ route('rooms.create', ['building_id' => $building->id]) }}" class="btn-secondary btn-sm">
                        <x-icon name="plus" class="h-4 w-4" />
                        Tambah Ruang
                    </a>
                    <a href="{{ route('buildings.edit', $building) }}" class="btn-primary btn-sm">
                        <x-icon name="pencil" class="h-4 w-4" />
                        Edit Gedung
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-ink-900 via-ink-900 to-brand-900 p-5 text-white shadow-lg shadow-brand-950/15 sm:p-6">
            <div class="bg-grid pointer-events-none absolute inset-0 opacity-25"></div>
            <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-brand-300 ring-1 ring-white/15">
                        <x-icon name="building" class="h-7 w-7" />
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-white/10 px-2 py-0.5 text-[11px] font-bold tracking-wide text-brand-200 ring-1 ring-white/15">{{ $building->code }}</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-300 ring-1 ring-emerald-400/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                {{ $building->status_label }}
                            </span>
                        </div>
                        <p class="mt-2 text-lg font-semibold">{{ $building->name }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $building->floors }} lantai · {{ $building->rooms->count() }} ruang terdaftar</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 sm:gap-8">
                    <div>
                        <p class="text-2xl font-bold">{{ $building->rooms->count() }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Ruang</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($building->rooms->sum('capacity'), 0, ',', '.') }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Kapasitas</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $building->rooms->sum('facilities_count') }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Fasilitas</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="card xl:col-span-2">
                <div class="card-header">
                    <div>
                        <h2 class="section-title">Ruang di gedung ini</h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $building->rooms->count() }} ruang terdaftar</p>
                    </div>
                    @can('manage-module')
                        <a href="{{ route('rooms.create', ['building_id' => $building->id]) }}" class="btn-secondary btn-sm">
                            <x-icon name="plus" class="h-3.5 w-3.5" />
                            Ruang Baru
                        </a>
                    @endcan
                </div>

                @if ($building->rooms->isEmpty())
                    <x-empty-state icon="door" title="Belum ada ruang" description="Tambahkan ruang sidang atau ruang ujian pertama pada gedung ini.">
                        <x-slot:actions>
                            @can('manage-module')
                                <a href="{{ route('rooms.create', ['building_id' => $building->id]) }}" class="btn-primary btn-sm">Tambah Ruang</a>
                            @endcan
                        </x-slot:actions>
                    </x-empty-state>
                @else
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ruang</th>
                                    <th>Lantai</th>
                                    <th>Kapasitas</th>
                                    <th>Fasilitas</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($building->rooms as $room)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-500 ring-1 ring-slate-200/70">
                                                    <x-icon name="door" class="h-4 w-4" />
                                                </span>
                                                <div class="min-w-0">
                                                    <a href="{{ route('rooms.show', $room) }}" class="block truncate text-sm font-semibold text-slate-800 hover:text-brand-700">{{ $room->name }}</a>
                                                    <p class="text-[11px] text-slate-400">{{ $room->code }} · {{ $room->type_label }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-xs text-slate-600">{{ $room->floor_label }}</td>
                                        <td class="text-xs text-slate-600">{{ $room->capacity }} orang</td>
                                        <td>
                                            <span class="inline-flex items-center gap-1 text-xs text-slate-600">
                                                <x-icon name="chair" class="h-3.5 w-3.5 text-slate-400" />
                                                {{ $room->facilities_count }}
                                            </span>
                                        </td>
                                        <td><x-status-badge :status="$room->live_status" :label="\App\Models\Room::statuses()[$room->live_status] ?? $room->status_label" /></td>
                                        <td class="text-right">
                                            <a href="{{ route('rooms.show', $room) }}" class="btn-icon text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Lihat ruang">
                                                <x-icon name="eye" class="h-4 w-4" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="space-y-5">
                <div class="card card-pad">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-900">Informasi gedung</h2>
                        <x-status-badge :status="$building->is_active ? 'aktif' : 'nonaktif'" :label="$building->status_label" />
                    </div>
                    <dl class="mt-4 space-y-3.5 text-sm">
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Alamat</dt>
                            <dd class="mt-0.5 text-slate-700">{{ $building->address ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Jumlah lantai</dt>
                            <dd class="mt-0.5 text-slate-700">{{ $building->floors }} lantai</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Catatan</dt>
                            <dd class="mt-0.5 text-xs leading-relaxed text-slate-600">{{ $building->notes ?: 'Tidak ada catatan tambahan.' }}</dd>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-400">
                            <span>Dibuat {{ $building->created_at->translatedFormat('d M Y') }}</span>
                            <span>Diubah {{ $building->updated_at->diffForHumans() }}</span>
                        </div>
                    </dl>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Jadwal mendatang</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Sidang di gedung ini</p>
                        </div>
                    </div>
                    <div class="p-5">
                        @if ($upcomingSessions->isEmpty())
                            <p class="text-xs leading-relaxed text-slate-500">Belum ada jadwal sidang mendatang di gedung ini.</p>
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
                                                <p class="text-[11px] text-slate-400">{{ $session->room->code }} · {{ $session->time_range }}</p>
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
                            <p class="text-xs leading-relaxed text-slate-500">Belum ada aktivitas terkait gedung ini.</p>
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
            </div>
        </div>
    </div>
@endsection
