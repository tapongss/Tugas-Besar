@extends('layouts.app')

@section('title', 'Edit '.$room->name)

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Ruang', 'url' => route('rooms.index')], ['label' => $room->code, 'url' => route('rooms.show', $room)], ['label' => 'Edit']]" />
@endsection

@section('content')
    <div class="space-y-5">
        <x-page-header :title="'Edit '.$room->name" subtitle="Perbarui data ruang dan komposisi fasilitas yang terpasang." icon="door">
            <x-slot:actions>
                <a href="{{ route('rooms.show', $room) }}" class="btn-secondary btn-sm">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali
                </a>
            </x-slot:actions>
        </x-page-header>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="xl:col-span-2">
                @include('rooms._form', ['room' => $room, 'buildings' => $buildings, 'facilities' => $facilities])
            </div>

            <div class="space-y-5">
                <div class="card card-pad">
                    <h2 class="text-sm font-semibold text-slate-900">Ringkasan ruang</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Gedung</dt>
                            <dd class="text-right font-medium text-slate-800">{{ $room->building?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Fasilitas terpasang</dt>
                            <dd class="font-semibold text-slate-900">{{ $room->facilities->count() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Jadwal mendatang</dt>
                            <dd class="font-semibold text-slate-900">{{ $room->upcomingSessions()->count() }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Terakhir diubah</dt>
                            <dd class="text-slate-700">{{ $room->updated_at->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="card card-pad">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                            <x-icon name="alert" class="h-4 w-4" />
                        </span>
                        <h2 class="text-sm font-semibold text-slate-900">Perhatian</h2>
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-slate-500">Mengubah status menjadi perbaikan tidak membatalkan jadwal yang sudah ada. Periksa jadwal mendatang sebelum menonaktifkan ruang.</p>
                    <a href="{{ route('schedules.index', ['room_id' => $room->id]) }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800">
                        Lihat jadwal ruang
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
