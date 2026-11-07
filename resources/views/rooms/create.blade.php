@extends('layouts.app')

@section('title', 'Tambah Ruang')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Ruang', 'url' => route('rooms.index')], ['label' => 'Tambah']]" />
@endsection

@section('content')
    <div class="space-y-5">
        <x-page-header title="Tambah Ruang" subtitle="Daftarkan ruang baru lengkap dengan kapasitas dan fasilitas pendukung sidang." icon="door">
            <x-slot:actions>
                <a href="{{ route('rooms.index') }}" class="btn-secondary btn-sm">
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
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                            <x-icon name="info" class="h-4 w-4" />
                        </span>
                        <h2 class="text-sm font-semibold text-slate-900">Panduan ruang sidang</h2>
                    </div>
                    <ul class="mt-4 space-y-3 text-xs leading-relaxed text-slate-600">
                        <li class="flex gap-2"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-500"></span><span>Gunakan kode unik agar mudah dicari, misalnya <span class="font-mono text-[11px] text-slate-800">A.201</span>.</span></li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-500"></span><span>Ruang berstatus <span class="font-semibold">perbaikan</span> atau <span class="font-semibold">nonaktif</span> tidak dapat dijadwalkan.</span></li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-500"></span><span>Minimal satu proyektor disarankan untuk ruang sidang utama.</span></li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-500"></span><span>Catat kondisi fasilitas agar laporan pemeliharaan akurat.</span></li>
                    </ul>
                </div>

                <div class="card card-pad">
                    <h2 class="text-sm font-semibold text-slate-900">Status ruang</h2>
                    <div class="mt-3.5 space-y-3">
                        @foreach ([
                            ['status' => 'tersedia', 'desc' => 'Dapat dijadwalkan untuk sidang.'],
                            ['status' => 'perbaikan', 'desc' => 'Sedang diperbaiki, tidak dapat dijadwalkan.'],
                            ['status' => 'nonaktif', 'desc' => 'Tidak digunakan untuk kegiatan akademik.'],
                        ] as $item)
                            <div class="flex items-start gap-2.5">
                                <x-status-badge :status="$item['status']" />
                                <p class="pt-0.5 text-[11px] leading-relaxed text-slate-500">{{ $item['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
