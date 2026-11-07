<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Modul pengelolaan ruang dan fasilitas sidang — Sistem Informasi Tugas Akhir & Skripsi.">
    <title>@yield('title', 'Dashboard') · Ruang & Fasilitas Sidang</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-slate-100">
    @php
        $navGroups = [
            'Utama' => [
                ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'dashboard', 'active' => 'dashboard'],
            ],
            'Master Data' => [
                ['label' => 'Gedung', 'icon' => 'building', 'route' => 'buildings.index', 'active' => 'buildings.*'],
                ['label' => 'Ruang', 'icon' => 'door', 'route' => 'rooms.index', 'active' => 'rooms.*'],
                ['label' => 'Fasilitas', 'icon' => 'chair', 'route' => 'facilities.index', 'active' => 'facilities.*'],
            ],
            'Penjadwalan' => [
                ['label' => 'Jadwal Sidang', 'icon' => 'calendar-days', 'route' => 'schedules.index', 'active' => 'schedules.*'],
            ],
            'Lainnya' => [
                ['label' => 'Laporan & Statistik', 'icon' => 'chart', 'route' => 'reports.index', 'active' => 'reports.*'],
                ['label' => 'Dokumentasi API', 'icon' => 'code', 'route' => 'api-docs', 'active' => 'api-docs'],
            ],
        ];

        $todaySessionCount = \App\Models\DefenseSession::whereDate('date', today())
            ->where('status', '!=', \App\Models\DefenseSession::STATUS_CANCELLED)
            ->count();
    @endphp

    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/50 backdrop-blur-sm lg:hidden"></div>

    <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-72 -translate-x-full flex-col bg-gradient-to-b from-ink-900 via-ink-900 to-ink-950 transition-transform duration-300 lg:translate-x-0">
        <div class="flex items-center gap-3 px-5 pb-5 pt-6">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-ink-950 shadow-lg shadow-brand-950/40">
                <x-icon name="graduation" class="h-6 w-6" />
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold tracking-tight text-white">Sistem TA & Skripsi</p>
                <p class="truncate text-[11px] font-medium text-brand-300">Modul Ruang & Fasilitas</p>
            </div>
            <button type="button" data-sidebar-close class="ml-auto rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-white lg:hidden" aria-label="Tutup menu">
                <x-icon name="x" class="h-4 w-4" />
            </button>
        </div>

        <div class="mx-4 mb-2 flex items-center gap-2 rounded-xl bg-white/5 px-3 py-2 ring-1 ring-inset ring-white/10">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-brand-500/20 text-[10px] font-bold text-brand-300">D</span>
            <p class="text-[11px] leading-tight text-slate-400">Individu D · Ruang & Fasilitas Sidang</p>
        </div>

        <nav class="scrollbar-thin flex-1 overflow-y-auto px-3 pb-4">
            @foreach ($navGroups as $group => $items)
                <p class="sidebar-label">{{ $group }}</p>
                <div class="space-y-1">
                    @foreach ($items as $item)
                        <a href="{{ route($item['route']) }}" class="sidebar-link {{ request()->routeIs($item['active']) ? 'sidebar-link-active' : '' }}">
                            <x-icon :name="$item['icon']" class="h-[18px] w-[18px] shrink-0" />
                            <span class="truncate">{{ $item['label'] }}</span>
                            @if ($item['route'] === 'schedules.index' && $todaySessionCount > 0)
                                <span class="ml-auto rounded-full bg-brand-500/20 px-2 py-0.5 text-[10px] font-bold text-brand-300">{{ $todaySessionCount }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach

            <div class="mt-6 rounded-xl bg-gradient-to-br from-brand-500/15 to-brand-700/10 p-4 ring-1 ring-inset ring-brand-400/20">
                <div class="flex items-center gap-2 text-brand-300">
                    <x-icon name="sparkles" class="h-4 w-4" />
                    <p class="text-[11px] font-semibold uppercase tracking-wide">Tips</p>
                </div>
                <p class="mt-2 text-xs leading-relaxed text-slate-400">Gunakan filter fasilitas untuk menemukan ruang dengan proyektor atau PC yang siap dipakai.</p>
                <a href="{{ route('rooms.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-brand-300 hover:text-brand-200">
                    Jelajahi ruang
                    <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            </div>
        </nav>

        <div class="border-t border-white/10 p-3">
            <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-500/20 text-xs font-bold text-brand-200 ring-1 ring-brand-400/30">{{ auth()->user()->initials }}</span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-semibold text-white">{{ auth()->user()->name }}</p>
                    <p class="truncate text-[10px] text-slate-400">{{ auth()->user()->role_label }}</p>
                </div>
                <a href="{{ route('profile.edit') }}" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-white/10 hover:text-white" title="Profil saya">
                    <x-icon name="user" class="h-4 w-4" />
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-500/15 hover:text-rose-300" title="Keluar">
                        <x-icon name="logout" class="h-4 w-4" />
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex min-h-screen flex-col lg:pl-72">
        <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur">
            <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
                <button type="button" data-sidebar-open class="btn-icon shrink-0 text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="Buka menu">
                    <x-icon name="menu" class="h-5 w-5" />
                </button>

                <div class="min-w-0 flex-1">
                    @yield('breadcrumbs')
                </div>

                <form action="{{ route('rooms.index') }}" method="GET" class="relative hidden xl:block">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" name="q" value="{{ request()->routeIs('rooms.index') ? request('q') : '' }}" placeholder="Cari ruang, gedung, fasilitas…" class="w-64 rounded-xl border-0 bg-slate-100 py-2 pl-9 pr-3 text-sm text-slate-700 ring-1 ring-inset ring-transparent transition placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-brand-600 focus:outline-none">
                </form>

                <a href="{{ route('schedules.index') }}" class="relative btn-icon shrink-0 text-slate-500 hover:bg-slate-100" title="{{ $todaySessionCount }} jadwal hari ini" aria-label="Jadwal hari ini">
                    <x-icon name="calendar-days" class="h-5 w-5" />
                    @if ($todaySessionCount > 0)
                        <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-600 px-1 text-[9px] font-bold text-white">{{ $todaySessionCount }}</span>
                    @endif
                </a>

                @can('manage-module')
                    <a href="{{ route('schedules.create') }}" class="btn-primary btn-sm hidden sm:inline-flex">
                        <x-icon name="plus" class="h-4 w-4" />
                        Jadwal Baru
                    </a>
                @endcan

                <div class="relative shrink-0" data-dropdown>
                    <button type="button" data-dropdown-toggle class="flex items-center gap-2 rounded-xl p-1 pr-2 transition hover:bg-slate-100" aria-label="Menu pengguna">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-[11px] font-bold text-white">{{ auth()->user()->initials }}</span>
                        <span class="hidden text-left md:block">
                            <span class="block max-w-32 truncate text-xs font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                            <span class="block text-[10px] text-slate-500">{{ auth()->user()->role_label }}</span>
                        </span>
                        <x-icon name="chevron-down" class="hidden h-3.5 w-3.5 text-slate-400 md:block" />
                    </button>

                    <div data-dropdown-menu class="absolute right-0 z-30 mt-2 hidden w-56 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10">
                        <div class="px-3 py-2">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="my-1 h-px bg-slate-100"></div>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                            <x-icon name="user" class="h-4 w-4 text-slate-400" />
                            Profil & Token API
                        </a>
                        <a href="{{ route('reports.index') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                            <x-icon name="chart" class="h-4 w-4 text-slate-400" />
                            Laporan Bulanan
                        </a>
                        <a href="{{ route('api-docs') }}" class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                            <x-icon name="code" class="h-4 w-4 text-slate-400" />
                            Dokumentasi API
                        </a>
                        <div class="my-1 h-px bg-slate-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-rose-600 transition hover:bg-rose-50">
                                <x-icon name="logout" class="h-4 w-4" />
                                Keluar dari sistem
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1500px] flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            @yield('content')
        </main>

        <footer class="mx-auto w-full max-w-[1500px] px-4 pb-6 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center justify-between gap-2 border-t border-slate-200 pt-4 text-[11px] text-slate-400 sm:flex-row">
                <p>Sistem Informasi Tugas Akhir & Skripsi · Modul Ruang & Fasilitas Sidang</p>
                <p>Individu D · Laravel {{ \Illuminate\Foundation\Application::VERSION }}</p>
            </div>
        </footer>
    </div>

    @include('partials.flash')

    <x-modal id="confirm-modal" title="Konfirmasi tindakan" max-width="max-w-md">
        <div class="px-5 py-5 sm:px-6">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 ring-1 ring-rose-100">
                    <x-icon name="alert" class="h-5 w-5" />
                </span>
                <div>
                    <p data-confirm-title class="text-sm font-semibold text-slate-900">Konfirmasi tindakan</p>
                    <p data-confirm-message class="mt-1.5 text-sm leading-relaxed text-slate-500">Apakah Anda yakin ingin melanjutkan?</p>
                </div>
            </div>
        </div>
        <x-slot:footer>
            <button type="button" data-modal-close class="btn-secondary btn-sm">Batalkan</button>
            <button type="button" data-confirm-submit class="btn-danger btn-sm">Ya, lanjutkan</button>
        </x-slot:footer>
    </x-modal>
</body>
</html>
