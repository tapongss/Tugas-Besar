@extends('layouts.app')

@section('title', 'Edit Jadwal')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Jadwal Sidang', 'url' => route('schedules.index')], ['label' => $session->title, 'url' => route('schedules.show', $session)], ['label' => 'Edit']]" />
@endsection

@section('content')
    <div class="space-y-5">
        <x-page-header title="Edit Jadwal Sidang" subtitle="Perbarui detail jadwal. Validasi bentrok tetap berlaku saat menyimpan." icon="calendar-days">
            <x-slot:actions>
                <a href="{{ route('schedules.show', $session) }}" class="btn-secondary btn-sm">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali
                </a>
            </x-slot:actions>
        </x-page-header>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="xl:col-span-2">
                @include('schedules._form', ['session' => $session, 'rooms' => $rooms])
            </div>

            <div class="space-y-5">
                <div class="card card-pad">
                    <h2 class="text-sm font-semibold text-slate-900">Jadwal saat ini</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Tanggal</dt>
                            <dd class="font-medium text-slate-800">{{ $session->date->translatedFormat('d M Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Waktu</dt>
                            <dd class="font-medium text-slate-800">{{ $session->time_range }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Ruang</dt>
                            <dd class="text-right font-medium text-slate-800">{{ $session->room?->code }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Status</dt>
                            <dd><x-status-badge :status="$session->status" :label="$session->status_label" /></dd>
                        </div>
                    </dl>
                    <p class="mt-4 border-t border-slate-100 pt-3 text-[10px] leading-relaxed text-slate-400">Dibuat oleh {{ $session->creator?->name ?? 'Sistem' }} · {{ $session->created_at->diffForHumans() }}</p>
                </div>

                <div class="card card-pad">
                    <h2 class="text-sm font-semibold text-slate-900">Perubahan status</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">Anda juga dapat mengubah status langsung dari halaman detail jadwal tanpa mengedit seluruh data.</p>
                    <a href="{{ route('schedules.show', $session) }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800">
                        Buka detail jadwal
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
