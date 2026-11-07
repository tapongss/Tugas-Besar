@extends('layouts.app')

@section('title', 'Edit '.$building->name)

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Gedung', 'url' => route('buildings.index')], ['label' => $building->code, 'url' => route('buildings.show', $building)], ['label' => 'Edit']]" />
@endsection

@section('content')
    <div class="space-y-5">
        <x-page-header :title="'Edit '.$building->name" subtitle="Perbarui identitas, alamat, atau status aktif gedung." icon="building">
            <x-slot:actions>
                <a href="{{ route('buildings.show', $building) }}" class="btn-secondary btn-sm">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali
                </a>
            </x-slot:actions>
        </x-page-header>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="xl:col-span-2">
                @include('buildings._form', ['building' => $building])
            </div>

            <div class="space-y-5">
                <div class="card card-pad">
                    <h2 class="text-sm font-semibold text-slate-900">Ringkasan</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Jumlah ruang</dt>
                            <dd class="font-semibold text-slate-900">{{ $building->rooms()->count() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Total kapasitas</dt>
                            <dd class="font-semibold text-slate-900">{{ number_format((int) $building->rooms()->sum('capacity'), 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Dibuat</dt>
                            <dd class="text-slate-700">{{ $building->created_at->translatedFormat('d M Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Terakhir diubah</dt>
                            <dd class="text-slate-700">{{ $building->updated_at->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </div>

                @can('manage-module')
                    @if ($building->rooms()->doesntExist())
                        <div class="card border border-rose-100 p-5">
                            <div class="flex items-start gap-2.5">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </span>
                                <div>
                                    <h2 class="text-sm font-semibold text-slate-900">Hapus gedung</h2>
                                    <p class="mt-1 text-xs leading-relaxed text-slate-500">Gedung ini belum memiliki ruang, sehingga dapat dihapus permanen.</p>
                                    <form method="POST" action="{{ route('buildings.destroy', $building) }}" class="mt-3" data-confirm="Gedung {{ $building->name }} akan dihapus permanen." data-confirm-title="Hapus gedung?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger-soft btn-sm">
                                            <x-icon name="trash" class="h-3.5 w-3.5" />
                                            Hapus Gedung
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @endcan
            </div>
        </div>
    </div>
@endsection
