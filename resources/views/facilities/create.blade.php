@extends('layouts.app')

@section('title', 'Tambah Fasilitas')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Fasilitas', 'url' => route('facilities.index')], ['label' => 'Tambah']]" />
@endsection

@section('content')
    <div class="space-y-5">
        <x-page-header title="Tambah Fasilitas" subtitle="Daftarkan jenis fasilitas baru yang dapat dipasang pada ruang sidang." icon="chair">
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
                    <h2 class="text-sm font-semibold text-slate-900">Kategori fasilitas</h2>
                    <div class="mt-4 space-y-2.5">
                        @foreach ([
                            ['display', 'projector', 'Proyektor, layar, dan perangkat presentasi.'],
                            ['komputer', 'cpu', 'PC, laptop, kamera konferensi, dan periferal.'],
                            ['audio', 'speaker', 'Sound system, microphone, dan speaker.'],
                            ['jaringan', 'wifi', 'Router, access point, dan konektivitas internet.'],
                            ['furnitur', 'chair', 'Meja, kursi, papan tulis, dan interior.'],
                            ['pendingin', 'snow', 'AC dan pendingin ruangan.'],
                        ] as [$category, $icon, $description])
                            <div class="flex items-start gap-2.5">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-500 ring-1 ring-slate-200/70">
                                    <x-icon :name="$icon" class="h-4 w-4" />
                                </span>
                                <div>
                                    <p class="text-xs font-semibold text-slate-700">{{ \App\Models\Facility::categories()[$category] }}</p>
                                    <p class="text-[10px] leading-relaxed text-slate-400">{{ $description }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card bg-gradient-to-br from-ink-900 to-brand-900 p-5 text-white">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-brand-300">Catatan</p>
                    <p class="mt-2 text-sm leading-relaxed text-slate-300">Kondisi fasilitas dicatat per ruang, bukan pada master. Satu jenis proyektor dapat memiliki kondisi berbeda di setiap ruangan.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
