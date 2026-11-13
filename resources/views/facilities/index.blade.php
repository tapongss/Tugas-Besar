@extends('layouts.app')

@section('title', 'Fasilitas')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Fasilitas']]" />
@endsection

@section('content')
    @php
        $hasFilter = array_filter($filters);
        $categoryColors = [
            'display' => 'bg-sky-50 text-sky-700 ring-sky-200/70',
            'komputer' => 'bg-violet-50 text-violet-700 ring-violet-200/70',
            'audio' => 'bg-amber-50 text-amber-700 ring-amber-200/70',
            'jaringan' => 'bg-brand-50 text-brand-700 ring-brand-200/70',
            'furnitur' => 'bg-orange-50 text-orange-700 ring-orange-200/70',
            'pendingin' => 'bg-cyan-50 text-cyan-700 ring-cyan-200/70',
            'lainnya' => 'bg-slate-100 text-slate-600 ring-slate-200',
        ];
    @endphp

    <div class="space-y-5">
        <x-page-header title="Master Fasilitas" subtitle="Kelola inventaris proyektor, PC, audio, jaringan, dan fasilitas pendukung sidang lainnya." icon="chair">
            <x-slot:actions>
                @can('manage-module')
                    <a href="{{ route('facilities.create') }}" class="btn-primary btn-sm">
                        <x-icon name="plus" class="h-4 w-4" />
                        Tambah Fasilitas
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Jenis Fasilitas', 'value' => $summary['total'], 'icon' => 'chair', 'tone' => 'bg-brand-50 text-brand-700'],
                ['label' => 'Fasilitas Aktif', 'value' => $summary['active'], 'icon' => 'check-circle', 'tone' => 'bg-emerald-50 text-emerald-700'],
                ['label' => 'Unit Terpasang', 'value' => number_format($summary['units'], 0, ',', '.'), 'icon' => 'layers', 'tone' => 'bg-sky-50 text-sky-700'],
                ['label' => 'Perlu Perbaikan', 'value' => $summary['damaged'], 'icon' => 'alert', 'tone' => 'bg-amber-50 text-amber-700'],
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

        <div class="card card-pad space-y-3">
            <div class="flex flex-wrap gap-1.5">
                <a href="{{ route('facilities.index') }}" class="filter-chip {{ empty($filters['category']) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua <span class="rounded-full bg-white/20 px-1.5 text-[10px]">{{ $summary['total'] }}</span>
                </a>
                @foreach (\App\Models\Facility::categories() as $value => $label)
                    <a href="{{ route('facilities.index', array_merge($filters, ['category' => $value])) }}" class="filter-chip {{ ($filters['category'] ?? '') === $value ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $label }}
                        <span class="rounded-full bg-white/20 px-1.5 text-[10px]">{{ $categoryCounts[$value] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('facilities.index') }}" class="grid gap-3 sm:grid-cols-[1fr_180px_auto]">
                @if (! empty($filters['category']))
                    <input type="hidden" name="category" value="{{ $filters['category'] }}">
                @endif
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama, kode, atau deskripsi fasilitas…" class="input pl-10">
                </div>
                <select name="status" data-auto-submit class="input">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(($filters['status'] ?? '') === 'nonaktif')>Nonaktif</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Terapkan</button>
                    @if ($hasFilter)
                        <a href="{{ route('facilities.index') }}" class="btn-secondary" title="Reset filter">
                            <x-icon name="refresh" class="h-4 w-4" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="card overflow-hidden">
            @if ($facilities->isEmpty())
                <x-empty-state icon="chair" title="Fasilitas tidak ditemukan" description="Belum ada fasilitas yang cocok dengan filter Anda. Tambahkan jenis fasilitas baru atau ubah pencarian.">
                    <x-slot:actions>
                        @can('manage-module')
                            <a href="{{ route('facilities.create') }}" class="btn-primary btn-sm">
                                <x-icon name="plus" class="h-4 w-4" />
                                Tambah Fasilitas
                            </a>
                        @endcan
                        <a href="{{ route('facilities.index') }}" class="btn-secondary btn-sm">Reset Filter</a>
                    </x-slot:actions>
                </x-empty-state>
            @else
                <div class="table-scroll">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fasilitas</th>
                                <th>Kategori</th>
                                <th class="text-center">Dipakai di</th>
                                <th class="text-center">Total Unit</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($facilities as $facility)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-500 ring-1 ring-slate-200/70">
                                                <x-icon :name="$facility->icon_name" class="h-4 w-4" />
                                            </span>
                                            <div class="min-w-0">
                                                <a href="{{ route('facilities.show', $facility) }}" class="truncate text-sm font-semibold text-slate-800 hover:text-brand-700">{{ $facility->name }}</a>
                                                <p class="truncate text-[11px] text-slate-400">{{ $facility->code }} · {{ $facility->unit }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $categoryColors[$facility->category] ?? $categoryColors['lainnya'] }}">
                                            {{ $facility->category_label }}
                                        </span>
                                    </td>
                                    <td class="text-center text-xs text-slate-600">{{ $facility->rooms_count }} ruang</td>
                                    <td class="text-center text-xs font-semibold text-slate-800">{{ number_format($facility->total_units, 0, ',', '.') }}</td>
                                    <td><x-status-badge :status="$facility->is_active ? 'aktif' : 'nonaktif'" :label="$facility->is_active ? 'Aktif' : 'Nonaktif'" /></td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('facilities.show', $facility) }}" class="btn-icon text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Lihat detail fasilitas">
                                                <x-icon name="eye" class="h-4 w-4" />
                                            </a>
                                            @can('manage-module')
                                                <a href="{{ route('facilities.edit', $facility) }}" class="btn-icon text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Edit fasilitas">
                                                    <x-icon name="pencil" class="h-4 w-4" />
                                                </a>
                                                @if ($facility->rooms_count === 0)
                                                    @can('delete-module')
                                                        <form method="POST" action="{{ route('facilities.destroy', $facility) }}" data-confirm="Fasilitas {{ $facility->name }} akan dihapus permanen." data-confirm-title="Hapus fasilitas?">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn-icon text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus fasilitas">
                                                                <x-icon name="trash" class="h-4 w-4" />
                                                            </button>
                                                        </form>
                                                    @endcan
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
                    {{ $facilities->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
