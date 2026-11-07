@extends('layouts.app')

@section('title', 'Gedung')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Gedung']]" />
@endsection

@section('content')
    <div class="space-y-5">
        <x-page-header title="Master Gedung" subtitle="Kelola data gedung kampus beserta jumlah ruang dan kapasitasnya." icon="building">
            <x-slot:actions>
                @can('manage-module')
                    <a href="{{ route('buildings.create') }}" class="btn-primary btn-sm">
                        <x-icon name="plus" class="h-4 w-4" />
                        Tambah Gedung
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Total Gedung', 'value' => $summary['total'], 'icon' => 'building', 'tone' => 'bg-brand-50 text-brand-700'],
                ['label' => 'Gedung Aktif', 'value' => $summary['active'], 'icon' => 'check-circle', 'tone' => 'bg-emerald-50 text-emerald-700'],
                ['label' => 'Total Ruang', 'value' => $summary['rooms'], 'icon' => 'door', 'tone' => 'bg-sky-50 text-sky-700'],
                ['label' => 'Kapasitas', 'value' => number_format($summary['capacity'], 0, ',', '.'), 'icon' => 'users', 'tone' => 'bg-violet-50 text-violet-700'],
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

        <form method="GET" action="{{ route('buildings.index') }}" class="card card-pad">
            <div class="grid gap-3 sm:grid-cols-[1fr_180px_auto]">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama, kode, atau alamat gedung…" class="input pl-10">
                </div>
                <select name="status" data-auto-submit class="input">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(($filters['status'] ?? '') === 'nonaktif')>Nonaktif</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Terapkan</button>
                    @if (array_filter($filters))
                        <a href="{{ route('buildings.index') }}" class="btn-secondary" title="Reset filter">
                            <x-icon name="refresh" class="h-4 w-4" />
                        </a>
                    @endif
                </div>
            </div>
            <p class="mt-3 text-xs text-slate-500">Menampilkan <span class="font-semibold text-slate-700">{{ $buildings->count() }}</span> dari {{ $buildings->total() }} gedung.</p>
        </form>

        @if ($buildings->isEmpty())
            <div class="card">
                <x-empty-state icon="building" title="Gedung tidak ditemukan" description="Belum ada gedung yang cocok dengan filter Anda. Tambahkan gedung baru atau ubah kata kunci pencarian.">
                    <x-slot:actions>
                        @can('manage-module')
                            <a href="{{ route('buildings.create') }}" class="btn-primary btn-sm">
                                <x-icon name="plus" class="h-4 w-4" />
                                Tambah Gedung
                            </a>
                        @endcan
                        <a href="{{ route('buildings.index') }}" class="btn-secondary btn-sm">Reset Filter</a>
                    </x-slot:actions>
                </x-empty-state>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($buildings as $building)
                    <div class="card group flex flex-col overflow-hidden transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3 border-b border-slate-100 p-5">
                            <div class="flex min-w-0 items-start gap-3">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-ink-900 to-ink-800 text-brand-300 shadow-sm">
                                    <x-icon name="building" class="h-5 w-5" />
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold tracking-wide text-slate-600">{{ $building->code }}</span>
                                        <x-status-badge :status="$building->is_active ? 'aktif' : 'nonaktif'" :label="$building->status_label" />
                                    </div>
                                    <a href="{{ route('buildings.show', $building) }}" class="mt-1.5 block truncate text-sm font-semibold text-slate-900 transition hover:text-brand-700">{{ $building->name }}</a>
                                    <p class="mt-0.5 line-clamp-1 text-[11px] text-slate-400">{{ $building->address ?: 'Alamat belum diisi' }}</p>
                                </div>
                            </div>

                            @can('manage-module')
                                <div class="flex shrink-0 gap-1">
                                    <a href="{{ route('buildings.edit', $building) }}" class="btn-icon text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Edit gedung">
                                        <x-icon name="pencil" class="h-4 w-4" />
                                    </a>
                                    @if ($building->rooms_count === 0)
                                        <form method="POST" action="{{ route('buildings.destroy', $building) }}" data-confirm="Gedung {{ $building->name }} akan dihapus permanen. Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus gedung?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus gedung">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endcan
                        </div>

                        <div class="grid grid-cols-3 divide-x divide-slate-100 border-b border-slate-100 bg-slate-50/50 text-center">
                            <div class="px-2 py-3">
                                <p class="text-sm font-bold text-slate-900">{{ $building->rooms_count }}</p>
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">Ruang</p>
                            </div>
                            <div class="px-2 py-3">
                                <p class="text-sm font-bold text-slate-900">{{ $building->floors }}</p>
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">Lantai</p>
                            </div>
                            <div class="px-2 py-3">
                                <p class="text-sm font-bold text-slate-900">{{ number_format((int) $building->capacity_total, 0, ',', '.') }}</p>
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">Kapasitas</p>
                            </div>
                        </div>

                        <div class="mt-auto flex items-center justify-between gap-2 p-4">
                            <p class="line-clamp-1 text-[11px] text-slate-400">{{ $building->notes ?: 'Tidak ada catatan tambahan.' }}</p>
                            <a href="{{ route('buildings.show', $building) }}" class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800">
                                Detail
                                <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-1">
                {{ $buildings->links() }}
            </div>
        @endif
    </div>
@endsection
