@props(['building'])

<form method="POST" action="{{ $building->exists ? route('buildings.update', $building) : route('buildings.store') }}" class="space-y-5">
    @csrf
    @if ($building->exists)
        @method('PUT')
    @endif

    @include('partials.errors')

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="section-title">Informasi gedung</h2>
                <p class="mt-0.5 text-xs text-slate-500">Data identitas dan lokasi gedung kampus.</p>
            </div>
            <span class="hidden rounded-lg bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-500 ring-1 ring-slate-200/70 sm:inline-flex">Wajib diisi *</span>
        </div>

        <div class="card-pad grid gap-5 sm:grid-cols-2">
            <div>
                <label for="code" class="label">Kode gedung <span class="text-rose-500">*</span></label>
                <input id="code" name="code" type="text" value="{{ old('code', $building->code) }}" required maxlength="20" placeholder="GD-A" class="input uppercase @error('code') ring-rose-400 @enderror">
                @error('code')
                    <p class="field-error">{{ $message }}</p>
                @else
                    <p class="hint">Gunakan kode unik, contoh GD-A atau GD-B.</p>
                @enderror
            </div>

            <div>
                <label for="name" class="label">Nama gedung <span class="text-rose-500">*</span></label>
                <input id="name" name="name" type="text" value="{{ old('name', $building->name) }}" required maxlength="120" placeholder="Gedung Utama" class="input @error('name') ring-rose-400 @enderror">
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="address" class="label">Alamat</label>
                <input id="address" name="address" type="text" value="{{ old('address', $building->address) }}" maxlength="255" placeholder="Jl. Pendidikan No. 1, Kampus Pusat" class="input @error('address') ring-rose-400 @enderror">
                @error('address')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="floors" class="label">Jumlah lantai <span class="text-rose-500">*</span></label>
                <input id="floors" name="floors" type="number" min="1" max="50" value="{{ old('floors', $building->floors ?: 1) }}" required class="input @error('floors') ring-rose-400 @enderror">
                @error('floors')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-end">
                <label class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-inset ring-slate-200">
                    <span>
                        <span class="block text-sm font-medium text-slate-700">Status aktif</span>
                        <span class="block text-[11px] text-slate-500">Gedung aktif dapat digunakan pada jadwal.</span>
                    </span>
                    <span class="relative inline-flex">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $building->is_active ?? true)) class="peer sr-only">
                        <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-brand-600"></span>
                        <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>
                    </span>
                </label>
            </div>

            <div class="sm:col-span-2">
                <label for="notes" class="label">Catatan</label>
                <textarea id="notes" name="notes" rows="3" maxlength="1000" placeholder="Informasi tambahan mengenai gedung…" class="input resize-y @error('notes') ring-rose-400 @enderror">{{ old('notes', $building->notes) }}</textarea>
                @error('notes')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3.5 sm:px-6">
            <a href="{{ $building->exists ? route('buildings.show', $building) : route('buildings.index') }}" class="btn-secondary btn-sm">Batalkan</a>
            <button type="submit" class="btn-primary btn-sm">
                <x-icon name="check" class="h-4 w-4" />
                {{ $building->exists ? 'Simpan Perubahan' : 'Simpan Gedung' }}
            </button>
        </div>
    </div>
</form>
