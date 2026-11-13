@extends('layouts.app')

@section('title', $facility->name)

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Fasilitas', 'url' => route('facilities.index')], ['label' => $facility->code]]" />
@endsection

@section('content')
    @php
        $categoryColors = [
            'display' => 'bg-sky-50 text-sky-700 ring-sky-200/70',
            'komputer' => 'bg-violet-50 text-violet-700 ring-violet-200/70',
            'audio' => 'bg-amber-50 text-amber-700 ring-amber-200/70',
            'jaringan' => 'bg-brand-50 text-brand-700 ring-brand-200/70',
            'furnitur' => 'bg-orange-50 text-orange-700 ring-orange-200/70',
            'pendingin' => 'bg-cyan-50 text-cyan-700 ring-cyan-200/70',
            'lainnya' => 'bg-slate-100 text-slate-600 ring-slate-200',
        ];
        $damagedUnits = $conditions->whereIn('key', ['rusak_ringan', 'rusak_berat'])->sum('units');
        $goodUnits = $conditions->firstWhere('key', 'baik')['units'] ?? 0;
    @endphp

    <div class="space-y-5">
        <x-page-header :title="$facility->name" :subtitle="$facility->category_label.' · '.$facility->code" icon="chair">
            <x-slot:actions>
                <a href="{{ route('facilities.index') }}" class="btn-secondary btn-sm">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali
                </a>
                @can('manage-module')
                    <a href="{{ route('rooms.index') }}" class="btn-secondary btn-sm">
                        <x-icon name="door" class="h-4 w-4" />
                        Daftar Ruang
                    </a>
                    <a href="{{ route('facilities.edit', $facility) }}" class="btn-primary btn-sm">
                        <x-icon name="pencil" class="h-4 w-4" />
                        Edit Fasilitas
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-ink-900 via-ink-900 to-brand-900 p-5 text-white shadow-lg shadow-brand-950/15 sm:p-6">
            <div class="bg-grid pointer-events-none absolute inset-0 opacity-25"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-brand-300 ring-1 ring-white/15">
                        <x-icon :name="$facility->icon_name" class="h-7 w-7" />
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-white/10 px-2 py-0.5 text-[11px] font-bold tracking-wide text-brand-200 ring-1 ring-white/15">{{ $facility->code }}</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full {{ $facility->is_active ? 'bg-emerald-400/10 text-emerald-300 ring-emerald-400/20' : 'bg-slate-400/10 text-slate-300 ring-slate-400/20' }} px-2.5 py-1 text-[11px] font-semibold ring-1">
                                <span class="h-1.5 w-1.5 rounded-full {{ $facility->is_active ? 'bg-emerald-400' : 'bg-slate-400' }}"></span>
                                {{ $facility->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <p class="mt-2 text-lg font-semibold">{{ $facility->name }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $facility->category_label }} · Satuan {{ $facility->unit }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:gap-7">
                    <div>
                        <p class="text-2xl font-bold">{{ $facility->rooms_count }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Ruang</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ number_format($facility->total_units, 0, ',', '.') }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total unit</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-emerald-300">{{ $goodUnits }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Unit baik</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-amber-300">{{ $damagedUnits }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Perlu perbaikan</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="space-y-5 xl:col-span-2">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Sebaran per kondisi</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Akumulasi unit pada seluruh ruang terpasang</p>
                        </div>
                    </div>
                    <div class="grid gap-3 p-5 sm:grid-cols-3">
                        @foreach ($conditions as $condition)
                            @php
                                $tone = match ($condition['key']) {
                                    'baik' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/70',
                                    'rusak_ringan' => 'bg-amber-50 text-amber-700 ring-amber-200/70',
                                    default => 'bg-rose-50 text-rose-700 ring-rose-200/70',
                                };
                            @endphp
                            <div class="rounded-xl border border-slate-100 p-4">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $tone }}">
                                    {{ $condition['label'] }}
                                </span>
                                <p class="mt-3 text-2xl font-bold text-slate-900">{{ number_format($condition['units'], 0, ',', '.') }}</p>
                                <p class="text-[11px] text-slate-500">{{ $condition['placements'] }} penempatan ruang</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Ruang yang menggunakan</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Jumlah dan kondisi dicatat per ruang</p>
                        </div>
                        <a href="{{ route('rooms.index') }}" class="text-xs font-semibold text-brand-700 hover:text-brand-800">Semua ruang</a>
                    </div>

                    @if ($facility->rooms->isEmpty())
                        <x-empty-state icon="door" title="Belum terpasang di ruang" description="Fasilitas ini belum ditambahkan ke ruang mana pun. Buka edit ruang untuk memasang proyektor, PC, atau fasilitas lain.">
                            <x-slot:actions>
                                <a href="{{ route('rooms.index') }}" class="btn-secondary btn-sm">Lihat Daftar Ruang</a>
                            </x-slot:actions>
                        </x-empty-state>
                    @else
                        <div class="table-scroll">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Ruang</th>
                                        <th>Gedung</th>
                                        <th class="text-center">Jumlah</th>
                                        <th>Kondisi</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($facility->rooms as $room)
                                        <tr>
                                            <td>
                                                <a href="{{ route('rooms.show', $room) }}" class="flex items-center gap-2.5 group">
                                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-500 ring-1 ring-slate-200/70">
                                                        <x-icon name="door" class="h-4 w-4" />
                                                    </span>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-medium text-slate-800 group-hover:text-brand-700">{{ $room->name }}</p>
                                                        <p class="text-[10px] text-slate-400">{{ $room->code }}</p>
                                                    </div>
                                                </a>
                                            </td>
                                            <td class="text-xs text-slate-600">
                                                {{ $room->building?->name ?? '—' }}
                                                <p class="text-[10px] text-slate-400">{{ $room->floor_label }}</p>
                                            </td>
                                            <td class="text-center text-xs font-semibold text-slate-800">{{ $room->pivot->quantity }} <span class="font-normal text-slate-400">{{ $facility->unit }}</span></td>
                                            <td><x-status-badge :status="$room->pivot->condition" :label="\App\Models\Facility::conditions()[$room->pivot->condition] ?? $room->pivot->condition" /></td>
                                            <td class="max-w-48 text-xs text-slate-500">{{ $room->pivot->note ?: '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                @if (filled($facility->description))
                    <div class="card card-pad">
                        <h2 class="text-sm font-semibold text-slate-900">Deskripsi fasilitas</h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $facility->description }}</p>
                    </div>
                @endif
            </div>

            <div class="space-y-5">
                <div class="card card-pad">
                    <h2 class="text-sm font-semibold text-slate-900">Detail fasilitas</h2>
                    <dl class="mt-4 space-y-3.5 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Kode</dt>
                            <dd class="font-medium text-slate-800">{{ $facility->code }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Kategori</dt>
                            <dd>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $categoryColors[$facility->category] ?? $categoryColors['lainnya'] }}">
                                    {{ $facility->category_label }}
                                </span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Satuan</dt>
                            <dd class="font-medium text-slate-800">{{ $facility->unit }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Status master</dt>
                            <dd><x-status-badge :status="$facility->is_active ? 'aktif' : 'nonaktif'" :label="$facility->is_active ? 'Aktif' : 'Nonaktif'" /></dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Dibuat</dt>
                            <dd class="text-slate-700">{{ $facility->created_at->translatedFormat('d M Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Diperbarui</dt>
                            <dd class="text-slate-700">{{ $facility->updated_at->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="section-title">Aktivitas</h2>
                    </div>
                    <div class="p-5">
                        @if ($activities->isEmpty())
                            <p class="text-xs leading-relaxed text-slate-500">Belum ada aktivitas terkait fasilitas ini.</p>
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

                @can('delete-module')
                    @if ($facility->rooms_count === 0)
                        <div class="card border border-rose-100 p-5">
                            <h2 class="text-sm font-semibold text-slate-900">Hapus fasilitas</h2>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500">Fasilitas ini belum terpasang pada ruang mana pun sehingga dapat dihapus permanen.</p>
                            <form method="POST" action="{{ route('facilities.destroy', $facility) }}" class="mt-3" data-confirm="Fasilitas {{ $facility->name }} akan dihapus permanen." data-confirm-title="Hapus fasilitas?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger-soft btn-sm">
                                    <x-icon name="trash" class="h-3.5 w-3.5" />
                                    Hapus Fasilitas
                                </button>
                            </form>
                        </div>
                    @endif
                @endcan
            </div>
        </div>
    </div>
@endsection
