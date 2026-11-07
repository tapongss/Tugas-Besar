@props(['facility'])

<form method="POST" action="{{ $facility->exists ? route('facilities.update', $facility) : route('facilities.store') }}" class="space-y-5">
    @csrf
    @if ($facility->exists)
        @method('PUT')
    @endif

    @include('partials.errors')

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="section-title">Informasi fasilitas</h2>
                <p class="mt-0.5 text-xs text-slate-500">Data master jenis fasilitas yang dapat dipasang pada ruang.</p>
            </div>
            <span class="hidden rounded-lg bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-500 ring-1 ring-slate-200/70 sm:inline-flex">Wajib diisi *</span>
        </div>

        <div class="card-pad grid gap-5 sm:grid-cols-2">
            <div>
                <label for="code" class="label">Kode fasilitas <span class="text-rose-500">*</span></label>
                <input id="code" name="code" type="text" value="{{ old('code', $facility->code) }}" required maxlength="20" placeholder="FS-PRJ" class="input uppercase @error('code') ring-rose-400 @enderror">
                @error('code')
                    <p class="field-error">{{ $message }}</p>
                @else
                    <p class="hint">Kode unik, contoh FS-PRJ untuk proyektor.</p>
                @enderror
            </div>

            <div>
                <label for="name" class="label">Nama fasilitas <span class="text-rose-500">*</span></label>
                <input id="name" name="name" type="text" value="{{ old('name', $facility->name) }}" required maxlength="120" placeholder="Proyektor Epson EB-X06" class="input @error('name') ring-rose-400 @enderror">
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="category" class="label">Kategori <span class="text-rose-500">*</span></label>
                <select id="category" name="category" required class="input @error('category') ring-rose-400 @enderror">
                    @foreach (\App\Models\Facility::categories() as $value => $label)
                        <option value="{{ $value }}" @selected(old('category', $facility->category) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="unit" class="label">Satuan <span class="text-rose-500">*</span></label>
                <input id="unit" name="unit" type="text" value="{{ old('unit', $facility->unit ?: 'unit') }}" required maxlength="30" placeholder="unit" class="input @error('unit') ring-rose-400 @enderror">
                @error('unit')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="label">Deskripsi</label>
                <textarea id="description" name="description" rows="3" maxlength="1000" placeholder="Spesifikasi singkat fasilitas…" class="input resize-y @error('description') ring-rose-400 @enderror">{{ old('description', $facility->description) }}</textarea>
                @error('description')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-inset ring-slate-200">
                    <span>
                        <span class="block text-sm font-medium text-slate-700">Status aktif</span>
                        <span class="block text-[11px] text-slate-500">Fasilitas aktif dapat dipilih saat menambahkan fasilitas ruang.</span>
                    </span>
                    <span class="relative inline-flex">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $facility->is_active ?? true)) class="peer sr-only">
                        <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-brand-600"></span>
                        <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition peer-checked:translate-x-5"></span>
                    </span>
                </label>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3.5 sm:px-6">
            <a href="{{ route('facilities.index') }}" class="btn-secondary btn-sm">Batalkan</a>
            <button type="submit" class="btn-primary btn-sm">
                <x-icon name="check" class="h-4 w-4" />
                {{ $facility->exists ? 'Simpan Perubahan' : 'Simpan Fasilitas' }}
            </button>
        </div>
    </div>
</form>
