@extends('layouts.app')

@section('title', 'Tambah Gedung')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Gedung', 'url' => route('buildings.index')], ['label' => 'Tambah']]" />
@endsection

@section('content')
    <div class="space-y-5">
        <x-page-header title="Tambah Gedung" subtitle="Daftarkan gedung baru sebagai wadah ruang sidang dan fasilitas." icon="building">
            <x-slot:actions>
                <a href="{{ route('buildings.index') }}" class="btn-secondary btn-sm">
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
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                            <x-icon name="info" class="h-4 w-4" />
                        </span>
                        <h2 class="text-sm font-semibold text-slate-900">Panduan pengisian</h2>
                    </div>
                    <ul class="mt-4 space-y-3 text-xs leading-relaxed text-slate-600">
                        <li class="flex gap-2"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-500"></span><span>Kode gedung harus unik dan mudah dikenali, misalnya <span class="font-mono text-[11px] text-slate-800">GD-A</span>.</span></li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-500"></span><span>Jumlah lantai digunakan sebagai acuan saat menambahkan ruang.</span></li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-500"></span><span>Gedung hanya dapat dihapus jika belum memiliki ruang terdaftar.</span></li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-brand-500"></span><span>Nonaktifkan gedung yang sedang direnovasi agar tidak dipakai pada jadwal baru.</span></li>
                    </ul>
                </div>

                <div class="card bg-gradient-to-br from-ink-900 to-brand-900 p-5 text-white">
                    <div class="flex items-center gap-2 text-brand-300">
                        <x-icon name="sparkles" class="h-4 w-4" />
                        <p class="text-[11px] font-semibold uppercase tracking-wide">Setelah gedung dibuat</p>
                    </div>
                    <p class="mt-2.5 text-sm leading-relaxed text-slate-300">Tambahkan ruang pada gedung ini, lalu lengkapi fasilitas seperti proyektor dan PC agar siap dijadwalkan untuk sidang.</p>
                    <a href="{{ route('rooms.index') }}" class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-brand-300 hover:text-brand-200">
                        Lihat master ruang
                        <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
