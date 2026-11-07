<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') · Ruang & Fasilitas Sidang</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-100">
    <div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">
        <section class="relative hidden overflow-hidden bg-gradient-to-br from-ink-950 via-ink-900 to-brand-950 lg:flex lg:flex-col lg:justify-between lg:p-12">
            <div class="bg-grid absolute inset-0 opacity-40"></div>
            <div class="absolute -left-20 top-10 h-72 w-72 rounded-full bg-brand-500/20 blur-3xl"></div>
            <div class="absolute -bottom-24 right-0 h-96 w-96 rounded-full bg-sky-500/10 blur-3xl"></div>

            <div class="relative">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-400 to-brand-600 text-ink-950 shadow-lg shadow-brand-950/50">
                        <x-icon name="graduation" class="h-7 w-7" />
                    </span>
                    <div>
                        <p class="text-sm font-bold tracking-tight text-white">Sistem Tugas Akhir & Skripsi</p>
                        <p class="text-xs text-brand-300">Modul Ruang & Fasilitas Sidang</p>
                    </div>
                </div>

                <h1 class="mt-16 max-w-lg text-balance text-4xl font-bold leading-tight tracking-tight text-white">
                    Kelola ruang sidang & fasilitas akademik dalam satu tempat.
                </h1>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-400">
                    Master gedung, ruang ujian, inventaris proyektor dan PC, hingga jadwal sidang dengan deteksi bentrok otomatis.
                </p>

                <div class="mt-10 grid max-w-lg gap-3">
                    @foreach ([
                        ['icon' => 'building', 'title' => 'Master data terstruktur', 'text' => 'Gedung, lantai, ruang, dan fasilitas saling terhubung.'],
                        ['icon' => 'calendar-days', 'title' => 'Jadwal tanpa bentrok', 'text' => 'Validasi otomatis bentrok ruang dan waktu sidang.'],
                        ['icon' => 'code', 'title' => 'REST API siap pakai', 'text' => 'Endpoint JSON dengan autentikasi Bearer token.'],
                    ] as $feature)
                        <div class="flex items-start gap-3 rounded-xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand-500/15 text-brand-300">
                                <x-icon :name="$feature['icon']" class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-white">{{ $feature['title'] }}</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-slate-400">{{ $feature['text'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative flex items-center justify-between text-[11px] text-slate-500">
                <p>© {{ date('Y') }} Sistem Informasi Akademik</p>
                <p>Individu D · Ruang & Fasilitas Sidang</p>
            </div>
        </section>

        <section class="flex items-center justify-center px-5 py-10 sm:px-8">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-lg shadow-brand-600/30">
                        <x-icon name="graduation" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="text-sm font-bold text-slate-900">Sistem TA & Skripsi</p>
                        <p class="text-xs text-slate-500">Modul Ruang & Fasilitas Sidang</p>
                    </div>
                </div>

                <div class="animate-fade-up rounded-2xl bg-white p-6 shadow-xl shadow-slate-900/5 ring-1 ring-slate-200/70 sm:p-8">
                    @yield('content')
                </div>

                <p class="mt-6 text-center text-[11px] leading-relaxed text-slate-400">
                    Dengan masuk, Anda menyetujui penggunaan data sesuai kebijakan akademik kampus.
                </p>
            </div>
        </section>
    </div>

    @include('partials.flash')
</body>
</html>
