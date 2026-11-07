<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cetak') · Ruang & Fasilitas Sidang</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-slate-900">
    <div class="mx-auto max-w-5xl px-6 py-8 print:px-0 print:py-0">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b-2 border-slate-900 pb-4">
            <div class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-900 text-white print:h-9 print:w-9">
                    <x-icon name="graduation" class="h-6 w-6" />
                </span>
                <div>
                    <p class="text-sm font-bold uppercase tracking-wide">Sistem Informasi Tugas Akhir & Skripsi</p>
                    <p class="text-xs text-slate-500">Modul Ruang & Fasilitas Sidang · Individu D</p>
                </div>
            </div>
            <div class="flex items-center gap-2 no-print">
                <a href="{{ route('reports.index') }}" class="btn-secondary btn-sm">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali
                </a>
                <button type="button" data-print class="btn-primary btn-sm">
                    <x-icon name="printer" class="h-4 w-4" />
                    Cetak
                </button>
            </div>
        </div>

        @yield('content')

        <div class="mt-8 border-t border-slate-200 pt-4 text-[10px] text-slate-500">
            <p>Dicetak melalui Sistem Informasi Tugas Akhir & Skripsi — Modul Ruang & Fasilitas Sidang.</p>
            <p class="mt-0.5">Dokumen ini dihasilkan secara otomatis oleh sistem.</p>
        </div>
    </div>
</body>
</html>
