@extends('layouts.app')

@section('title', 'Jadwal Baru')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Jadwal Sidang', 'url' => route('schedules.index')], ['label' => 'Jadwal Baru']]" />
@endsection

@section('content')
    <div class="space-y-5">
        <x-page-header title="Buat Jadwal Sidang" subtitle="Sistem akan memeriksa bentrok ruang dan waktu secara otomatis sebelum menyimpan." icon="calendar-days">
            <x-slot:actions>
                <a href="{{ route('schedules.index') }}" class="btn-secondary btn-sm">
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
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                            <x-icon name="shield" class="h-4 w-4" />
                        </span>
                        <h2 class="text-sm font-semibold text-slate-900">Deteksi bentrok</h2>
                    </div>
                    <ul class="mt-4 space-y-3 text-xs leading-relaxed text-slate-600">
                        <li class="flex gap-2"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-500"></span><span>Sistem menolak jadwal yang tumpang tindih pada ruang dan tanggal yang sama.</span></li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-500"></span><span>Ruang berstatus perbaikan atau nonaktif tidak dapat dipilih.</span></li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-500"></span><span>Jadwal berstatus batal tidak dihitung sebagai bentrok.</span></li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-500"></span><span>Gunakan panel ketersediaan untuk melihat jadwal lain sebelum menyimpan.</span></li>
                    </ul>
                </div>

                <div class="card card-pad">
                    <h2 class="text-sm font-semibold text-slate-900">Ruang tersedia</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $rooms->where('status', 'tersedia')->count() }} dari {{ $rooms->count() }} ruang siap dijadwalkan.</p>
                    <div class="mt-3.5 space-y-2">
                        @foreach ($rooms->where('status', 'tersedia')->take(5) as $room)
                            <div class="flex items-center justify-between gap-2 rounded-lg bg-slate-50 px-2.5 py-2">
                                <div class="min-w-0">
                                    <p class="truncate text-[11px] font-semibold text-slate-700">{{ $room->code }} — {{ $room->name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $room->building->name }}</p>
                                </div>
                                <span class="shrink-0 text-[10px] text-slate-500">{{ $room->capacity }} org</span>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('rooms.index', ['status' => 'tersedia']) }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brand-700 hover:text-brand-800">
                        Lihat semua ruang
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </div>

                <div class="card bg-gradient-to-br from-ink-900 to-brand-900 p-5 text-white">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-brand-300">Alur sidang</p>
                    <ol class="mt-3 space-y-2.5">
                        @foreach (['Buat jadwal dan pilih ruang.', 'Verifikasi tidak ada bentrok waktu.', 'Pantau status hingga selesai.'] as $index => $step)
                            <li class="flex items-start gap-2.5">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/10 text-[10px] font-bold text-brand-200 ring-1 ring-white/15">{{ $index + 1 }}</span>
                                <span class="text-xs leading-relaxed text-slate-300">{{ $step }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection
