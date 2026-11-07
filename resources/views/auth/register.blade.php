@extends('layouts.auth')

@section('title', 'Daftar Akun')

@section('content')
    <div class="mb-6">
        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-brand-600">Pendaftaran</p>
        <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Buat akun operator</h1>
        <p class="mt-1.5 text-sm text-slate-500">Akun baru otomatis memiliki akses kelola ruang dan fasilitas.</p>
    </div>

    @include('partials.errors')

    <form method="POST" action="{{ route('register') }}" class="mt-4 space-y-4">
        @csrf

        <div>
            <label for="name" class="label">Nama lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Nama sesuai identitas" class="input @error('name') ring-rose-400 @enderror">
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="nama@kampus.ac.id" class="input @error('email') ring-rose-400 @enderror">
                @error('email')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="phone" class="label">Nomor HP <span class="font-normal text-slate-400">(opsional)</span></label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="08xxxxxxxxxx" class="input @error('phone') ring-rose-400 @enderror">
                @error('phone')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="password" class="label">Kata sandi</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter, huruf & angka" class="input @error('password') ring-rose-400 @enderror">
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
            <p class="hint">Gunakan kombinasi huruf dan angka minimal 8 karakter.</p>
        </div>

        <div>
            <label for="password_confirmation" class="label">Konfirmasi kata sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi kata sandi" class="input">
        </div>

        <button type="submit" class="btn-primary w-full py-3">
            Daftar & masuk
            <x-icon name="arrow-right" class="h-4 w-4" />
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-brand-700 hover:text-brand-800">Masuk di sini</a>
    </p>
@endsection
