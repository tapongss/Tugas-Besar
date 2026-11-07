@props(['index', 'row', 'facilities'])

@php
    $selectedFacility = (string) ($row['facility_id'] ?? '');
    $quantity = (int) ($row['quantity'] ?? 1);
    $condition = $row['condition'] ?? 'baik';
    $note = $row['note'] ?? '';
@endphp

<div data-facility-row class="rounded-xl border border-slate-200 bg-white p-3.5 transition hover:border-slate-300">
    <div class="grid gap-2.5 lg:grid-cols-[auto_1fr_90px_150px_1fr_auto] lg:items-center">
        <span data-facility-index class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-[10px] font-bold text-slate-400 ring-1 ring-slate-200/70">{{ is_numeric($index) ? str_pad($index + 1, 2, '0', STR_PAD_LEFT) : '01' }}</span>

        <div>
            <label class="sr-only" for="facility-{{ $index }}-id">Fasilitas</label>
            <select id="facility-{{ $index }}-id" name="facilities[{{ $index }}][facility_id]" data-facility-select class="input py-2 text-xs">
                <option value="">Pilih fasilitas…</option>
                @foreach ($facilities as $facility)
                    <option value="{{ $facility->id }}" @selected($selectedFacility === (string) $facility->id)>{{ $facility->name }} ({{ $facility->code }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="sr-only" for="facility-{{ $index }}-quantity">Jumlah</label>
            <input id="facility-{{ $index }}-quantity" type="number" name="facilities[{{ $index }}][quantity]" value="{{ $quantity }}" min="1" max="999" class="input py-2 text-center text-xs" title="Jumlah">
        </div>

        <div>
            <label class="sr-only" for="facility-{{ $index }}-condition">Kondisi</label>
            <select id="facility-{{ $index }}-condition" name="facilities[{{ $index }}][condition]" class="input py-2 text-xs">
                @foreach (\App\Models\Facility::conditions() as $value => $label)
                    <option value="{{ $value }}" @selected($condition === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="sr-only" for="facility-{{ $index }}-note">Catatan</label>
            <input id="facility-{{ $index }}-note" type="text" name="facilities[{{ $index }}][note]" value="{{ $note }}" maxlength="255" placeholder="Catatan kondisi (opsional)" class="input py-2 text-xs">
        </div>

        <button type="button" data-remove-facility class="btn-icon h-8 w-8 shrink-0 text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus baris fasilitas">
            <x-icon name="x" class="h-4 w-4" />
        </button>
    </div>
</div>
