<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Terjadi kesalahan') · Ruang & Fasilitas Sidang</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-full items-center justify-center bg-slate-100 px-4 py-10">
    <div class="w-full max-w-lg">
        <div class="mb-6 flex items-center justify-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-ink-950 shadow-lg shadow-brand-900/20">
                <x-icon name="graduation" class="h-6 w-6" />
            </span>
            <div>
                <p class="text-sm font-bold tracking-tight text-slate-900">Sistem TA & Skripsi</p>
                <p class="text-[11px] font-medium text-slate-500">Modul Ruang & Fasilitas Sidang</p>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="relative overflow-hidden bg-gradient-to-br from-ink-900 via-ink-900 to-brand-900 px-6 py-8 text-center text-white">
                <div class="bg-grid pointer-events-none absolute inset-0 opacity-25"></div>
                <div class="relative">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-brand-300 ring-1 ring-white/15">
                        <x-icon :name="trim($__env->yieldContent('icon', 'alert'))" class="h-7 w-7" />
                    </span>
                    <p class="mt-4 text-5xl font-bold tracking-tight">@yield('code')</p>
                    <p class="mt-1 text-sm font-semibold text-brand-200">@yield('title')</p>
                </div>
            </div>

            <div class="px-6 py-6 text-center">
                <p class="text-sm leading-relaxed text-slate-600">@yield('message')</p>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                    <a href="{{ url('/') }}" class="btn-primary btn-sm">
                        <x-icon name="home" class="h-4 w-4" />
                        Kembali ke Dashboard
                    </a>
                    <button type="button" onclick="history.back()" class="btn-secondary btn-sm">
                        <x-icon name="arrow-left" class="h-4 w-4" />
                        Halaman Sebelumnya
                    </button>
                </div>
            </div>
        </div>

        <p class="mt-5 text-center text-[11px] text-slate-400">Individu D · Laravel {{ \Illuminate\Foundation\Application::VERSION }}</p>
    </div>
</body>
</html>
