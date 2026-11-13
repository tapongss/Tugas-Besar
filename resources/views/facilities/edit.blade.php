@extends('layouts.app')

@section('title', 'Edit '.$facility->name)

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Fasilitas', 'url' => route('facilities.index')], ['label' => $facility->name, 'url' => null], ['label' => 'Edit']]" />
@endsection

@section('content')
    <div class="space-y-5">
        <x-page-header :title="'Edit '.$facility->name" subtitle="Perbarui data master fasilitas dan pantau ruang yang menggunakannya." icon="chair">
            <x-slot:actions>
                <a href="{{ route('facilities.index') }}" class="btn-secondary btn-sm">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali
                </a>
            </x-slot:actions>
        </x-page-header>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="xl:col-span-2">
                @include('facilities._form', ['facility' => $facility])
            </div>

            <div class="space-y-5">
                <div class="card card-pad">
                    <h2 class="text-sm font-semibold text-slate-900">Ringkasan penggunaan</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Dipakai di</dt>
                            <dd class="font-semibold text-slate-900">{{ $facility->rooms_count }} ruang</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Total unit</dt>
                            <dd class="font-semibold text-slate-900">{{ number_format($facility->total_units, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Kategori</dt>
                            <dd class="text-slate-700">{{ $facility->category_label }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Terakhir diubah</dt>
                            <dd class="text-slate-700">{{ $facility->updated_at->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Ruang pengguna</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Kondisi tercatat per ruang</p>
                        </div>
                    </div>
                    <div class="p-5">
                        @if ($roomsUsing->isEmpty())
                            <p class="text-xs leading-relaxed text-slate-500">Fasilitas ini belum dipasang pada ruang mana pun.</p>
                        @else
                            <ul class="space-y-3">
                                @foreach ($roomsUsing as $room)
                                    <li>
                                        <a href="{{ route('rooms.show', $room) }}" class="group flex items-center gap-2.5 rounded-xl p-2 transition hover:bg-slate-50">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-500 ring-1 ring-slate-200/70">
                                                <x-icon name="door" class="h-4 w-4" />
                                            </span>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-xs font-semibold text-slate-800 group-hover:text-brand-700">{{ $room->code }} — {{ $room->name }}</p>
                                                <p class="text-[10px] text-slate-400">{{ $room->building?->name }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[10px] font-semibold text-slate-700">{{ $room->pivot->quantity }} {{ $facility->unit }}</p>
                                                <x-status-badge :status="$room->pivot->condition" :label="\App\Models\Facility::conditions()[$room->pivot->condition] ?? $room->pivot->condition" class="mt-0.5" />
                                            </div>
                                        </a>
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
                            <p class="mt-1 text-xs leading-relaxed text-slate-500">Fasilitas ini belum terpasang pada ruang, sehingga dapat dihapus permanen.</p>
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
