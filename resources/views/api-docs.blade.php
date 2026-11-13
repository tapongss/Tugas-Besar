@extends('layouts.app')

@section('title', 'Dokumentasi API')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Dokumentasi API']]" />
@endsection

@section('content')
    @php
        $groupTones = [
            'sky' => ['bg-sky-50 text-sky-700 ring-sky-100', 'bg-sky-500'],
            'teal' => ['bg-brand-50 text-brand-700 ring-brand-100', 'bg-brand-500'],
            'violet' => ['bg-violet-50 text-violet-700 ring-violet-100', 'bg-violet-500'],
            'amber' => ['bg-amber-50 text-amber-700 ring-amber-100', 'bg-amber-500'],
        ];

        $methodTones = [
            'GET' => 'bg-sky-50 text-sky-700 ring-sky-200/70',
            'POST' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/70',
            'PUT' => 'bg-amber-50 text-amber-700 ring-amber-200/70',
            'DELETE' => 'bg-rose-50 text-rose-700 ring-rose-200/70',
        ];

        $sampleList = <<<'JSON'
{
  "data": [
    {
      "id": 3,
      "code": "GD-A-201",
      "name": "Ruang Sidang A201",
      "capacity": 40,
      "status": "tersedia",
      "building": { "id": 1, "name": "Gedung A" }
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": null },
  "meta": { "current_page": 1, "per_page": 15, "total": 11 }
}
JSON;

        $sampleError = <<<'JSON'
{
  "message": "Token API diperlukan. Sertakan header Authorization: Bearer <token>.",
  "errors": {
    "room_id": ["Ruang yang dipilih sedang dalam perbaikan."]
  }
}
JSON;

        $curlRead = 'curl -s "'.$base.'/rooms?status=tersedia&per_page=5" \\'."\n".'  -H "Accept: application/json"';

        $curlWrite = 'curl -s -X POST "'.$base.'/schedules" \\'."\n".'  -H "Accept: application/json" \\'."\n".'  -H "Authorization: Bearer <token>" \\'."\n".'  -H "Content-Type: application/json" \\'."\n".'  -d \'{
    "room_id": 1,
    "title": "Sidang Skripsi — Evaluasi Sistem Informasi",
    "student_name": "Nadia Putri",
    "student_nim": "2110101099",
    "type": "skripsi",
    "date": "2026-11-12",
    "start_time": "09:00",
    "end_time": "11:00",
    "status": "dijadwalkan"
  }\'';
    @endphp

    <div class="space-y-5">
        <x-page-header title="Dokumentasi REST API" subtitle="Integrasi data ruang, fasilitas, dan jadwal sidang melalui API versi 1." icon="code">
            <x-slot:actions>
                <a href="{{ $base }}" target="_blank" rel="noopener" class="btn-secondary btn-sm">
                    <x-icon name="external" class="h-4 w-4" />
                    Buka Endpoint
                </a>
                <a href="{{ route('profile.edit') }}" class="btn-primary btn-sm">
                    <x-icon name="key" class="h-4 w-4" />
                    Kelola Token
                </a>
            </x-slot:actions>
        </x-page-header>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-ink-900 via-ink-900 to-brand-900 p-5 text-white shadow-lg shadow-brand-950/15 sm:p-6">
            <div class="bg-grid pointer-events-none absolute inset-0 opacity-25"></div>
            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-3.5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-brand-300 ring-1 ring-white/15">
                        <x-icon name="database" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-brand-300">Base URL</p>
                        <code class="mt-1 block break-all text-sm font-medium text-white sm:text-base">{{ $base }}</code>
                        <p class="mt-1.5 text-xs text-slate-400">JSON REST API · Versi 1.0.0 · Tanpa dependensi modul lain</p>
                    </div>
                </div>
                <button type="button" class="btn-secondary btn-sm w-full shrink-0 sm:w-auto" data-copy data-copy-value="{{ $base }}">
                    <x-icon name="copy" class="h-4 w-4" />
                    <span>Salin Base URL</span>
                </button>
            </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <div class="card flex items-start gap-3 p-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-700 ring-1 ring-sky-100">
                    <x-icon name="eye" class="h-4 w-4" />
                </span>
                <div>
                    <p class="text-xs font-semibold text-slate-800">Baca tanpa token</p>
                    <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500">Seluruh endpoint GET dapat diakses publik untuk kebutuhan integrasi tampilan.</p>
                </div>
            </div>
            <div class="card flex items-start gap-3 p-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">
                    <x-icon name="shield" class="h-4 w-4" />
                </span>
                <div>
                    <p class="text-xs font-semibold text-slate-800">Tulis dengan Bearer token</p>
                    <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500">POST dan PUT memerlukan token akun admin atau operator yang aktif. DELETE hanya boleh memakai token admin.</p>
                </div>
            </div>
            <div class="card flex items-start gap-3 p-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-violet-50 text-violet-700 ring-1 ring-violet-100">
                    <x-icon name="list" class="h-4 w-4" />
                </span>
                <div>
                    <p class="text-xs font-semibold text-slate-800">Paginasi konsisten</p>
                    <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500">Gunakan <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px]">page</code> dan <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px]">per_page</code> (maks. 100) pada endpoint daftar.</p>
                </div>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="space-y-5 xl:col-span-2">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Daftar endpoint</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Seluruh resource tersedia pada prefix <code class="rounded bg-slate-100 px-1 py-0.5 text-[11px]">/api/v1</code>.</p>
                        </div>
                        <span class="rounded-lg bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-500 ring-1 ring-slate-200/70">{{ collect($endpoints)->sum(fn ($group) => count($group['items'])) }} endpoint</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($endpoints as $group)
                            @php
                                [$groupTone, $groupDot] = $groupTones[$group['color']] ?? $groupTones['teal'];
                            @endphp
                            <section id="grup-{{ \Illuminate\Support\Str::slug($group['group']) }}" class="scroll-mt-24">
                                <div class="flex items-center gap-2.5 bg-slate-50/60 px-5 py-3 sm:px-6">
                                    <span class="h-2 w-2 rounded-full {{ $groupDot }}"></span>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">{{ $group['group'] }}</h3>
                                    <span class="ml-auto rounded-md px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset {{ $groupTone }}">{{ count($group['items']) }} endpoint</span>
                                </div>

                                <ul class="divide-y divide-slate-100">
                                    @foreach ($group['items'] as $item)
                                        <li class="px-5 py-3.5 sm:px-6">
                                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                                <div class="flex min-w-0 items-start gap-2.5">
                                                    <span class="mt-0.5 inline-flex shrink-0 items-center rounded-md px-2 py-0.5 text-[10px] font-bold ring-1 ring-inset {{ $methodTones[$item['method']] ?? $methodTones['GET'] }}">{{ $item['method'] }}</span>
                                                    <div class="min-w-0">
                                                        <code class="block break-all font-mono text-xs font-semibold text-slate-800">{{ $item['path'] }}</code>
                                                        <p class="mt-1 text-[11px] leading-relaxed text-slate-500">{{ $item['description'] }}</p>
                                                    </div>
                                                </div>
                                                <span class="ml-15 inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset sm:ml-0 {{ $item['auth'] ? 'bg-amber-50 text-amber-700 ring-amber-200/70' : 'bg-slate-50 text-slate-500 ring-slate-200' }}">
                                                    <x-icon :name="$item['auth'] ? 'lock' : 'eye'" class="h-3 w-3" />
                                                    {{ $item['auth'] ? 'Token' : 'Publik' }}
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endforeach
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Contoh permintaan</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Gunakan header <code class="rounded bg-slate-100 px-1 py-0.5 text-[11px]">Accept: application/json</code> pada setiap permintaan.</p>
                        </div>
                    </div>
                    <div class="card-pad space-y-5">
                        <div>
                            <div class="mb-2 flex items-center gap-2">
                                <span class="inline-flex items-center rounded-md bg-sky-50 px-2 py-0.5 text-[10px] font-bold text-sky-700 ring-1 ring-inset ring-sky-200/70">GET</span>
                                <p class="text-xs font-semibold text-slate-700">Daftar ruang tersedia</p>
                            </div>
                            <pre class="scrollbar-thin overflow-x-auto rounded-xl bg-ink-950 p-4 text-[11px] leading-relaxed text-slate-300"><code>{{ $curlRead }}</code></pre>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center gap-2">
                                <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200/70">POST</span>
                                <p class="text-xs font-semibold text-slate-700">Membuat jadwal sidang</p>
                            </div>
                            <pre class="scrollbar-thin overflow-x-auto rounded-xl bg-ink-950 p-4 text-[11px] leading-relaxed text-slate-300"><code>{{ $curlWrite }}</code></pre>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="section-title">Format respons daftar</h2>
                        </div>
                        <pre class="scrollbar-thin overflow-x-auto p-5 text-[10.5px] leading-relaxed text-slate-600"><code>{{ $sampleList }}</code></pre>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h2 class="section-title">Format kesalahan</h2>
                        </div>
                        <pre class="scrollbar-thin overflow-x-auto p-5 text-[10.5px] leading-relaxed text-slate-600"><code>{{ $sampleError }}</code></pre>
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="card card-pad xl:sticky xl:top-20">
                    <div class="flex items-center gap-2">
                        <x-icon name="key" class="h-4 w-4 text-brand-700" />
                        <h2 class="text-sm font-semibold text-slate-900">Autentikasi</h2>
                    </div>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500">Sertakan token pada header berikut untuk endpoint tulis:</p>
                    <code class="mt-2 block rounded-lg bg-slate-50 px-3 py-2 text-[11px] font-medium text-slate-700 ring-1 ring-slate-200">Authorization: Bearer &lt;token&gt;</code>

                    <div class="mt-4 space-y-2 border-t border-slate-100 pt-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Token demo (seeder)</p>
                        @foreach ($demoTokens as $label => $token)
                            <div class="rounded-lg bg-slate-50 p-2.5 ring-1 ring-slate-200/70">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-[10px] font-semibold text-slate-600">{{ $label }}</p>
                                    <button type="button" class="rounded-md p-1 text-slate-400 transition hover:bg-white hover:text-slate-700" data-copy data-copy-value="{{ $token }}" title="Salin token">
                                        <x-icon name="copy" class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                                <code class="mt-1 block break-all font-mono text-[10px] text-slate-500">{{ $token }}</code>
                            </div>
                        @endforeach
                        <p class="text-[10px] leading-relaxed text-slate-400">Token demo hanya tersedia pada data seeder. Buat token pribadi melalui halaman profil untuk penggunaan nyata.</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="section-title">Kode status</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ([
                            ['200', 'Permintaan berhasil', 'emerald'],
                            ['201', 'Resource berhasil dibuat', 'emerald'],
                            ['401', 'Token tidak ada atau tidak valid', 'amber'],
                            ['403', 'Akun tidak memiliki izin untuk aksi ini', 'amber'],
                            ['404', 'Resource tidak ditemukan', 'rose'],
                            ['422', 'Validasi data gagal', 'rose'],
                        ] as [$code, $description, $tone])
                            <div class="flex items-center gap-3 px-5 py-2.5">
                                <span class="inline-flex w-10 shrink-0 items-center justify-center rounded-md px-1.5 py-0.5 text-[10px] font-bold ring-1 ring-inset {{ $tone === 'emerald' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200/70' : ($tone === 'amber' ? 'bg-amber-50 text-amber-700 ring-amber-200/70' : 'bg-rose-50 text-rose-700 ring-rose-200/70') }}">{{ $code }}</span>
                                <p class="text-[11px] text-slate-600">{{ $description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="section-title">Catatan integrasi</h2>
                    </div>
                    <div class="card-pad">
                        <ul class="space-y-2.5 text-[11px] leading-relaxed text-slate-600">
                            <li class="flex items-start gap-2">
                                <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-600" />
                                <span>Filter jadwal: <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px]">room_id</code>, <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px]">building_id</code>, <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px]">status</code>, <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px]">type</code>, <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px]">date_from</code>, <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px]">date_to</code>.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-600" />
                                <span>Jadwal bentrok pada ruang dan tanggal yang sama ditolak dengan status <strong>422</strong>.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-600" />
                                <span>Ruang berstatus perbaikan atau nonaktif tidak dapat dijadwalkan.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <x-icon name="check" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-emerald-600" />
                                <span>Waktu menggunakan format 24 jam <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px]">HH:MM</code> dan tanggal <code class="rounded bg-slate-100 px-1 py-0.5 text-[10px]">YYYY-MM-DD</code>.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
