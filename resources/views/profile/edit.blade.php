@extends('layouts.app')

@section('title', 'Profil & Token API')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Profil & Token API']]" />
@endsection

@section('content')
    @php
        $issuedToken = session('api_token');
    @endphp

    <div class="space-y-5">
        <x-page-header title="Profil & Token API" subtitle="Kelola informasi akun, keamanan kata sandi, dan token akses REST API." icon="user">
            <x-slot:actions>
                <a href="{{ route('api-docs') }}" class="btn-secondary btn-sm">
                    <x-icon name="code" class="h-4 w-4" />
                    Dokumentasi API
                </a>
            </x-slot:actions>
        </x-page-header>

        @if ($issuedToken)
            <div class="card overflow-hidden border border-brand-200 bg-gradient-to-br from-brand-50 to-white">
                <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-600 text-white">
                            <x-icon name="key" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-brand-900">Token API baru berhasil dibuat</p>
                            <p class="mt-0.5 text-xs leading-relaxed text-brand-700">Salin token ini sekarang. Demi keamanan, token tidak akan ditampilkan lagi setelah halaman dimuat ulang.</p>
                        </div>
                    </div>
                    <div class="flex w-full items-center gap-2 sm:w-auto">
                        <code class="flex-1 truncate rounded-lg bg-white px-3 py-2 text-xs font-medium text-slate-700 ring-1 ring-brand-200 sm:w-72 sm:flex-none">{{ $issuedToken }}</code>
                        <button type="button" class="btn-primary btn-sm shrink-0" data-copy data-copy-value="{{ $issuedToken }}">
                            <x-icon name="copy" class="h-4 w-4" />
                            <span>Salin</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="space-y-5 xl:col-span-2">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Informasi akun</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Perbarui identitas yang tampil pada jejak aktivitas modul.</p>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-slate-200/70">
                            <x-icon name="shield" class="h-3.5 w-3.5 text-slate-400" />
                            {{ $user->role_label }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('profile.update') }}" class="card-pad grid gap-5 sm:grid-cols-2">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="name" class="label">Nama lengkap <span class="text-rose-500">*</span></label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="100" class="input @error('name') ring-rose-400 @enderror">
                            @error('name')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="label">Email <span class="text-rose-500">*</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="120" class="input @error('email') ring-rose-400 @enderror">
                            @error('email')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="label">Nomor telepon</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" maxlength="30" placeholder="08xxxxxxxxxx" class="input @error('phone') ring-rose-400 @enderror">
                            @error('phone')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <p class="label">Peran & status</p>
                            <div class="flex h-[42px] items-center gap-2 rounded-xl bg-slate-50 px-3.5 ring-1 ring-inset ring-slate-200">
                                <span class="text-sm font-medium text-slate-700">{{ $user->role_label }}</span>
                                <span class="text-slate-300">·</span>
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $user->is_active ? 'text-emerald-700' : 'text-slate-500' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <p class="hint">Peran akun hanya dapat diubah oleh administrator sistem.</p>
                        </div>

                        <div class="flex justify-end border-t border-slate-100 pt-4 sm:col-span-2">
                            <button type="submit" class="btn-primary btn-sm">
                                <x-icon name="check" class="h-4 w-4" />
                                Simpan Profil
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Keamanan kata sandi</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Gunakan minimal 8 karakter dengan kombinasi huruf dan angka.</p>
                        </div>
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                            <x-icon name="lock" class="h-4 w-4" />
                        </span>
                    </div>

                    <form method="POST" action="{{ route('profile.password') }}" class="card-pad grid gap-5 sm:grid-cols-2">
                        @csrf
                        @method('PUT')

                        <div class="sm:col-span-2">
                            <label for="current_password" class="label">Kata sandi saat ini <span class="text-rose-500">*</span></label>
                            <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="input @error('current_password') ring-rose-400 @enderror">
                            @error('current_password')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="label">Kata sandi baru <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <input id="password" name="password" type="password" required autocomplete="new-password" class="input pr-11 @error('password') ring-rose-400 @enderror">
                                <button type="button" data-password-toggle="password" class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-xl text-slate-400 transition hover:text-slate-700" aria-label="Tampilkan kata sandi">
                                    <x-icon name="eye" class="h-4 w-4" />
                                </button>
                            </div>
                            @error('password')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="label">Konfirmasi kata sandi <span class="text-rose-500">*</span></label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
                        </div>

                        <div class="flex justify-end border-t border-slate-100 pt-4 sm:col-span-2">
                            <button type="submit" class="btn-primary btn-sm">
                                <x-icon name="lock" class="h-4 w-4" />
                                Perbarui Kata Sandi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="space-y-5">
                <div class="card card-pad">
                    <div class="flex items-center gap-3">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-lg font-bold text-white shadow-sm">{{ $user->initials }}</span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                            <p class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-brand-700">{{ $user->role_label }}</p>
                        </div>
                    </div>

                    <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs text-slate-500">Jadwal dibuat</dt>
                            <dd class="text-xs font-semibold text-slate-900">{{ $stats['schedules'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs text-slate-500">Aktivitas tercatat</dt>
                            <dd class="text-xs font-semibold text-slate-900">{{ $stats['activities'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs text-slate-500">Login terakhir</dt>
                            <dd class="text-xs font-semibold text-slate-900">{{ $stats['last_login']?->diffForHumans() ?? 'Belum pernah' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs text-slate-500">Bergabung</dt>
                            <dd class="text-xs font-semibold text-slate-900">{{ $user->created_at?->translatedFormat('d M Y') }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Token API</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Akses REST API dengan Bearer token.</p>
                        </div>
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600 ring-1 ring-violet-100">
                            <x-icon name="key" class="h-4 w-4" />
                        </span>
                    </div>

                    <div class="card-pad">
                        <div class="flex items-center gap-2.5 rounded-xl {{ $stats['has_token'] ? 'bg-emerald-50 ring-emerald-100' : 'bg-slate-50 ring-slate-200' }} p-3 ring-1">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white {{ $stats['has_token'] ? 'text-emerald-600' : 'text-slate-400' }} ring-1 ring-slate-200/70">
                                <x-icon :name="$stats['has_token'] ? 'check-circle' : 'alert'" class="h-4 w-4" />
                            </span>
                            <div>
                                <p class="text-xs font-semibold {{ $stats['has_token'] ? 'text-emerald-800' : 'text-slate-700' }}">{{ $stats['has_token'] ? 'Token aktif' : 'Belum ada token' }}</p>
                                <p class="text-[10px] text-slate-500">{{ $stats['has_token'] ? 'Token tersimpan sebagai hash SHA-256.' : 'Buat token untuk mengakses endpoint tulis.' }}</p>
                            </div>
                        </div>

                        <p class="mt-3 text-xs leading-relaxed text-slate-500">
                            Token hanya ditampilkan satu kali saat dibuat. Membuat token baru akan langsung membatalkan token lama yang sedang digunakan.
                        </p>

                        @if ($user->can('delete-module'))
                            <p class="mt-2 text-[11px] leading-relaxed text-slate-400">Akun {{ $user->role_label }} dapat menggunakan token untuk operasi baca, tambah, ubah, dan hapus.</p>
                        @elseif ($user->can('manage-module'))
                            <p class="mt-2 text-[11px] leading-relaxed text-slate-400">Akun {{ $user->role_label }} dapat menggunakan token untuk operasi baca, tambah, dan ubah. Endpoint hapus (DELETE) hanya untuk akun admin.</p>
                        @else
                            <p class="mt-2 text-[11px] leading-relaxed text-amber-600">Akun {{ $user->role_label }} hanya dapat mengakses endpoint baca (GET).</p>
                        @endif

                        <form method="POST" action="{{ route('profile.token') }}" class="mt-4" data-confirm="Token API lama akan langsung tidak berlaku. Token baru hanya ditampilkan sekali setelah dibuat." data-confirm-title="Buat ulang token API?" data-confirm-button="Ya, buat token baru" data-confirm-tone="primary">
                            @csrf
                            <button type="submit" class="btn-secondary btn-sm w-full">
                                <x-icon name="refresh" class="h-4 w-4" />
                                {{ $stats['has_token'] ? 'Buat Ulang Token' : 'Buat Token API' }}
                            </button>
                        </form>

                        <a href="{{ route('api-docs') }}" class="mt-2 inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-slate-200 py-2 text-xs font-semibold text-slate-600 transition hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700">
                            Lihat dokumentasi API
                            <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2 class="section-title">Aktivitas saya</h2>
                    </div>
                    <div class="p-5">
                        @if ($recentActivities->isEmpty())
                            <p class="text-xs leading-relaxed text-slate-500">Belum ada aktivitas yang tercatat untuk akun ini.</p>
                        @else
                            <ul class="space-y-3.5">
                                @foreach ($recentActivities as $activity)
                                    <li class="flex items-start gap-2.5">
                                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-50 text-slate-400 ring-1 ring-slate-200">
                                            <x-icon :name="$activity->icon" class="h-3 w-3" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-[11px] leading-relaxed text-slate-600">{{ $activity->description }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $activity->created_at->diffForHumans() }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
