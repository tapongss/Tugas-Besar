@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <div class="mb-6">
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-brand-600">Selamat datang</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Masuk ke akun Anda</h1>
        <p class="mt-1.5 text-sm text-slate-500">Kelola ruang, fasilitas, dan jadwal sidang dari satu dashboard.</p>
    </div>

    @if (request()->boolean('expired'))
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
            <div class="flex items-start gap-2.5">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
                <p class="text-sm text-amber-800">Sesi Anda telah berakhir. Silakan masuk kembali.</p>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3">
            <div class="flex items-start gap-2.5">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-rose-500" />
                <p class="text-sm text-rose-700">{{ $errors->first() }}</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="label">Alamat email</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="mail" class="h-4 w-4" />
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="nama@kampus.ac.id" class="input pl-10 @error('email') ring-rose-400 @enderror">
            </div>
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="label">Kata sandi</label>
                <span class="text-[11px] text-slate-400">Minimal 8 karakter</span>
            </div>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <x-icon name="lock" class="h-4 w-4" />
                </span>
                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="input pl-10 pr-11 @error('password') ring-rose-400 @enderror">
                <button type="button" data-password-toggle="password" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition hover:text-slate-600" aria-label="Tampilkan kata sandi">
                    <x-icon name="eye" class="h-4 w-4" />
                </button>
            </div>
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Ingat saya
            </label>
            <a href="{{ route('register') }}" class="text-xs font-semibold text-brand-700 hover:text-brand-800">Belum punya akun?</a>
        </div>

        <button type="submit" class="btn-primary w-full py-3">
            Masuk
            <x-icon name="arrow-right" class="h-4 w-4" />
        </button>
    </form>

    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
        <p class="flex items-center gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
            <x-icon name="key" class="h-3.5 w-3.5" />
            Akun demo
        </p>
        <div class="mt-2.5 grid gap-1.5 text-[11px] text-slate-600">
            <p><span class="font-semibold text-slate-800">Admin:</span> admin@ta.test · <span class="font-mono">password</span></p>
            <p><span class="font-semibold text-slate-800">Operator:</span> operator@ta.test · <span class="font-mono">password</span></p>
            <p><span class="font-semibold text-slate-800">Viewer:</span> viewer@ta.test · <span class="font-mono">password</span></p>
        </div>
    </div>

    <p class="mt-6 text-center text-sm text-slate-500">
        Belum memiliki akun?
        <a href="{{ route('register') }}" class="font-semibold text-brand-700 hover:text-brand-800">Daftar sekarang</a>
    </p>
@endsection
