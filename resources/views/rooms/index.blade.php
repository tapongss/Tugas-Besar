@extends('layouts.app')

@section('title', 'Ruang')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Ruang']]" />
@endsection

@section('content')
    @php
        $sortLink = function (string $column) use ($sort, $direction, $filters) {
            return route('rooms.index', array_merge($filters, [
                'sort' => $column,
                'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
            ]));
        };
        $sortIcon = fn (string $column) => $sort === $column ? ($direction === 'asc' ? 'arrow-up' : 'arrow-down') : 'sort';
        $hasFilter = array_filter($filters);
    @endphp

    <div class="space-y-5">
        <x-page-header title="Master Ruang" subtitle="Kelola ruang sidang, ruang ujian, kapasitas, dan fasilitas pendukungnya." icon="door">
            <x-slot:actions>
                @can('manage-module')
                    <a href="{{ route('rooms.create') }}" class="btn-primary btn-sm">
                        <x-icon name="plus" class="h-4 w-4" />
                        Tambah Ruang
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Total Ruang', 'value' => $summary['total'], 'icon' => 'door', 'tone' => 'bg-brand-50 text-brand-700'],
                ['label' => 'Tersedia', 'value' => $summary['available'], 'icon' => 'check-circle', 'tone' => 'bg-emerald-50 text-emerald-700'],
                ['label' => 'Perbaikan', 'value' => $summary['repair'], 'icon' => 'alert', 'tone' => 'bg-amber-50 text-amber-700'],
                ['label' => 'Total Kapasitas', 'value' => number_format($summary['capacity'], 0, ',', '.'), 'icon' => 'users', 'tone' => 'bg-sky-50 text-sky-700'],
            ] as $stat)
                <div class="card flex items-center gap-3 p-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $stat['tone'] }}">
                        <x-icon :name="$stat['icon']" class="h-4 w-4" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p>
                        <p class="text-lg font-bold text-slate-900">{{ $stat['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <form method="GET" action="{{ route('rooms.index') }}" class="card card-pad space-y-3">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="relative xl:col-span-2">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari kode, nama ruang, atau gedung…" class="input pl-10">
                </div>
                <select name="building_id" data-auto-submit class="input">
                    <option value="">Semua gedung</option>
                    @foreach ($buildings as $building)
                        <option value="{{ $building->id }}" @selected((string) ($filters['building_id'] ?? '') === (string) $building->id)>{{ $building->name }}</option>
                    @endforeach
                </select>
                <select name="status" data-auto-submit class="input">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Room::statuses() as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="type" data-auto-submit class="input">
                    <option value="">Semua jenis</option>
                    @foreach (\App\Models\Room::types() as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="facility_id" data-auto-submit class="input">
                    <option value="">Semua fasilitas</option>
                    @foreach ($facilities as $facility)
                        <option value="{{ $facility->id }}" @selected((string) ($filters['facility_id'] ?? '') === (string) $facility->id)>{{ $facility->name }}</option>
                    @endforeach
                </select>
                <input type="number" name="min_capacity" value="{{ $filters['min_capacity'] ?? '' }}" min="1" placeholder="Kapasitas minimal" class="input">
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">
                        <x-icon name="filter" class="h-4 w-4" />
                        Terapkan
                    </button>
                    @if ($hasFilter)
                        <a href="{{ route('rooms.index') }}" class="btn-secondary" title="Reset filter">
                            <x-icon name="refresh" class="h-4 w-4" />
                        </a>
                    @endif
                </div>
            </div>
            <p class="text-xs text-slate-500">Menampilkan <span class="font-semibold text-slate-700">{{ $rooms->count() }}</span> dari {{ $rooms->total() }} ruang.</p>
        </form>

        <div class="card overflow-hidden">
            @if ($rooms->isEmpty())
                <x-empty-state icon="door" title="Ruang tidak ditemukan" description="Tidak ada ruang yang cocok dengan filter. Ubah kriteria pencarian atau tambahkan ruang baru.">
                    <x-slot:actions>
                        @can('manage-module')
                            <a href="{{ route('rooms.create') }}" class="btn-primary btn-sm">
                                <x-icon name="plus" class="h-4 w-4" />
                                Tambah Ruang
                            </a>
                        @endcan
                        <a href="{{ route('rooms.index') }}" class="btn-secondary btn-sm">Reset Filter</a>
                    </x-slot:actions>
                </x-empty-state>
            @else
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ $sortLink('code') }}" class="inline-flex items-center gap-1 hover:text-slate-800">
                                        Ruang <x-icon :name="$sortIcon('code')" class="h-3 w-3" />
                                    </a>
                                </th>
                                <th>Gedung</th>
                                <th>Jenis</th>
                                <th>
                                    <a href="{{ $sortLink('capacity') }}" class="inline-flex items-center gap-1 hover:text-slate-800">
                                        Kapasitas <x-icon :name="$sortIcon('capacity')" class="h-3 w-3" />
                                    </a>
                                </th>
                                <th>Fasilitas</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rooms as $room)
                                @php $conditions = $room->facilityConditions(); @endphp
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-500 ring-1 ring-slate-200/70">
                                                <x-icon name="door" class="h-4 w-4" />
                                            </span>
                                            <div class="min-w-0">
                                                <a href="{{ route('rooms.show', $room) }}" class="block truncate text-sm font-semibold text-slate-800 hover:text-brand-700">{{ $room->name }}</a>
                                                <p class="text-[11px] text-slate-400">{{ $room->code }} · {{ $room->floor_label }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('buildings.show', $room->building) }}" class="text-xs font-medium text-slate-600 hover:text-brand-700">{{ $room->building->name }}</a>
                                        <p class="text-[10px] text-slate-400">{{ $room->building->code }}</p>
                                    </td>
                                    <td><span class="text-xs text-slate-600">{{ $room->type_label }}</span></td>
                                    <td>
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-700">
                                            <x-icon name="users" class="h-3.5 w-3.5 text-slate-400" />
                                            {{ $room->capacity }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-50 px-1.5 py-0.5 text-[11px] font-medium text-slate-600 ring-1 ring-slate-200/70">
                                                <x-icon name="chair" class="h-3 w-3 text-slate-400" />
                                                {{ $room->facilities->count() }}
                                            </span>
                                            @if ($conditions['rusak_ringan'] + $conditions['rusak_berat'] > 0)
                                                <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 ring-1 ring-amber-200/70" title="{{ $conditions['rusak_ringan'] + $conditions['rusak_berat'] }} fasilitas perlu perhatian">
                                                    <x-icon name="alert" class="h-3 w-3" />
                                                    {{ $conditions['rusak_ringan'] + $conditions['rusak_berat'] }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td><x-status-badge :status="$room->live_status" :label="\App\Models\Room::statuses()[$room->live_status] ?? $room->status_label" /></td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('rooms.show', $room) }}" class="btn-icon text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Detail ruang">
                                                <x-icon name="eye" class="h-4 w-4" />
                                            </a>
                                            @can('manage-module')
                                                <a href="{{ route('schedules.create', ['room_id' => $room->id]) }}" class="btn-icon text-slate-400 hover:bg-brand-50 hover:text-brand-700" title="Jadwalkan sidang">
                                                    <x-icon name="calendar-days" class="h-4 w-4" />
                                                </a>
                                                <a href="{{ route('rooms.edit', $room) }}" class="btn-icon text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Edit ruang">
                                                    <x-icon name="pencil" class="h-4 w-4" />
                                                </a>
                                                @if ($room->upcoming_count === 0)
                                                    <form method="POST" action="{{ route('rooms.destroy', $room) }}" data-confirm="Ruang {{ $room->name }} akan dihapus permanen beserta data fasilitasnya." data-confirm-title="Hapus ruang?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-icon text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus ruang">
                                                            <x-icon name="trash" class="h-4 w-4" />
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 px-4 py-3">
                    {{ $rooms->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
