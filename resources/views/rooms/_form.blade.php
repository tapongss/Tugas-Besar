@props(['room', 'buildings', 'facilities'])

@php
    $existingRows = $room->exists
        ? $room->facilities->map(fn ($facility) => [
            'facility_id' => $facility->id,
            'quantity' => $facility->pivot->quantity,
            'condition' => $facility->pivot->condition,
            'note' => $facility->pivot->note,
        ])->values()->all()
        : [];

    $rows = old('facilities', $existingRows);
    $rows = is_array($rows) ? array_values($rows) : [];
    $nextIndex = count($rows);
@endphp

<form method="POST" action="{{ $room->exists ? route('rooms.update', $room) : route('rooms.store') }}" class="space-y-5">
    @csrf
    @if ($room->exists)
        @method('PUT')
    @endif

    @include('partials.errors')

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="section-title">Informasi ruang</h2>
                <p class="mt-0.5 text-xs text-slate-500">Identitas, lokasi, dan kapasitas ruang.</p>
            </div>
            <span class="hidden rounded-lg bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-500 ring-1 ring-slate-200/70 sm:inline-flex">Wajib diisi *</span>
        </div>

        <div class="card-pad grid gap-5 sm:grid-cols-2">
            <div>
                <label for="building_id" class="label">Gedung <span class="text-rose-500">*</span></label>
                <select id="building_id" name="building_id" required class="input @error('building_id') ring-rose-400 @enderror">
                    <option value="">Pilih gedung…</option>
                    @foreach ($buildings as $building)
                        <option value="{{ $building->id }}" @selected((string) old('building_id', $room->building_id) === (string) $building->id)>{{ $building->code }} — {{ $building->name }}</option>
                    @endforeach
                </select>
                @error('building_id')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="code" class="label">Kode ruang <span class="text-rose-500">*</span></label>
                <input id="code" name="code" type="text" value="{{ old('code', $room->code) }}" required maxlength="30" placeholder="A.201" class="input uppercase @error('code') ring-rose-400 @enderror">
                @error('code')
                    <p class="field-error">{{ $message }}</p>
                @else
                    <p class="hint">Kode unik, biasanya terdiri dari gedung.lantai.nomor.</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="name" class="label">Nama ruang <span class="text-rose-500">*</span></label>
                <input id="name" name="name" type="text" value="{{ old('name', $room->name) }}" required maxlength="120" placeholder="Ruang Sidang Utama" class="input @error('name') ring-rose-400 @enderror">
                @error('name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="type" class="label">Jenis ruang <span class="text-rose-500">*</span></label>
                <select id="type" name="type" required class="input @error('type') ring-rose-400 @enderror">
                    @foreach (\App\Models\Room::types() as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $room->type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="floor" class="label">Lantai <span class="text-rose-500">*</span></label>
                    <input id="floor" name="floor" type="number" min="0" max="100" value="{{ old('floor', $room->floor ?? 1) }}" required class="input @error('floor') ring-rose-400 @enderror">
                    @error('floor')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="capacity" class="label">Kapasitas <span class="text-rose-500">*</span></label>
                    <input id="capacity" name="capacity" type="number" min="1" max="2000" value="{{ old('capacity', $room->capacity ?? 20) }}" required class="input @error('capacity') ring-rose-400 @enderror">
                    @error('capacity')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="status" class="label">Status ruang <span class="text-rose-500">*</span></label>
                <select id="status" name="status" required class="input @error('status') ring-rose-400 @enderror">
                    @foreach (\App\Models\Room::statuses() as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $room->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="field-error">{{ $message }}</p>
                @else
                    <p class="hint">Hanya ruang berstatus tersedia yang dapat dijadwalkan.</p>
                @enderror
            </div>

            <div>
                <label for="notes" class="label">Catatan ruang</label>
                <textarea id="notes" name="notes" rows="2" maxlength="1000" placeholder="Informasi tata letak atau catatan khusus…" class="input resize-y @error('notes') ring-rose-400 @enderror">{{ old('notes', $room->notes) }}</textarea>
                @error('notes')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="section-title">Fasilitas ruangan</h2>
                <p class="mt-0.5 text-xs text-slate-500">Tambahkan proyektor, PC, audio, dan fasilitas lain beserta kondisinya.</p>
            </div>
            <button type="button" id="add-facility" class="btn-secondary btn-sm">
                <x-icon name="plus" class="h-3.5 w-3.5" />
                Tambah Fasilitas
            </button>
        </div>

        <div class="card-pad space-y-3">
            <div class="hidden grid-cols-[auto_1fr_90px_150px_1fr_auto] gap-2.5 px-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400 lg:grid">
                <span class="w-7">#</span>
                <span>Fasilitas</span>
                <span class="text-center">Jumlah</span>
                <span>Kondisi</span>
                <span>Catatan</span>
                <span class="w-8"></span>
            </div>

            <div id="facility-rows" data-next-index="{{ $nextIndex }}" class="space-y-2.5">
                @foreach ($rows as $index => $row)
                    @include('rooms.partials.facility-row', ['index' => $index, 'row' => $row, 'facilities' => $facilities])
                @endforeach
            </div>

            <div id="facility-empty" class="{{ count($rows) > 0 ? 'hidden' : '' }} rounded-xl border border-dashed border-slate-300 bg-slate-50/60 px-4 py-8 text-center">
                <span class="mx-auto flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-400 ring-1 ring-slate-200">
                    <x-icon name="chair" class="h-5 w-5" />
                </span>
                <p class="mt-2 text-sm font-medium text-slate-600">Belum ada fasilitas</p>
                <p class="mt-0.5 text-xs text-slate-400">Klik “Tambah Fasilitas” untuk menambahkan proyektor, PC, atau fasilitas lainnya.</p>
            </div>

            <p class="text-[11px] leading-relaxed text-slate-400">Fasilitas yang sama tidak dapat ditambahkan dua kali pada satu ruang. Kondisi per unit menentukan laporan pemeliharaan.</p>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3.5 sm:px-6">
            <a href="{{ $room->exists ? route('rooms.show', $room) : route('rooms.index') }}" class="btn-secondary btn-sm">Batalkan</a>
            <button type="submit" class="btn-primary btn-sm">
                <x-icon name="check" class="h-4 w-4" />
                {{ $room->exists ? 'Simpan Perubahan' : 'Simpan Ruang' }}
            </button>
        </div>
    </div>

    <template id="facility-row-template">
        @include('rooms.partials.facility-row', ['index' => '__INDEX__', 'row' => ['facility_id' => '', 'quantity' => 1, 'condition' => 'baik', 'note' => ''], 'facilities' => $facilities])
    </template>
</form>
