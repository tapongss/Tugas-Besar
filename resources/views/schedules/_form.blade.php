@props(['session', 'rooms'])

@php
    $isEdit = $session->exists;
    $dateValue = old('date', $session->date?->format('Y-m-d') ?? today()->toDateString());
    $startValue = old('start_time', $session->start_time ? substr((string) $session->start_time, 0, 5) : '09:00');
    $endValue = old('end_time', $session->end_time ? substr((string) $session->end_time, 0, 5) : '11:00');

    $startParts = array_map('intval', explode(':', $startValue));
    $startHour = $startParts[0] ?? 9;
    $startMinute = $startParts[1] ?? 0;
    $startTotal = ($startHour * 60) + $startMinute;
    $minEndTotal = min($startTotal + 1, (23 * 60) + 59);
    $minEndHour = intdiv($minEndTotal, 60);
    $minEndMinute = $minEndTotal % 60;

    $endParts = array_map('intval', explode(':', $endValue));
    $endTotal = (($endParts[0] ?? 11) * 60) + ($endParts[1] ?? 0);
    if ($endTotal < $minEndTotal) {
        $endTotal = min($minEndTotal + 60, (23 * 60) + 59);
    }
    $endHour = intdiv($endTotal, 60);
    $endMinute = $endTotal % 60;
    $endValue = sprintf('%02d:%02d', $endHour, $endMinute);
@endphp

<form method="POST" action="{{ $isEdit ? route('schedules.update', $session) : route('schedules.store') }}" class="space-y-5" id="schedule-form"
      data-availability-endpoint="{{ url('/api/v1/schedules') }}" data-session-id="{{ $session->id }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @include('partials.errors')

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="section-title">Detail kegiatan</h2>
                <p class="mt-0.5 text-xs text-slate-500">Jadwal, ruang, dan jenis sidang.</p>
            </div>
            <span class="hidden rounded-lg bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-500 ring-1 ring-slate-200/70 sm:inline-flex">Wajib diisi *</span>
        </div>

        <div class="card-pad grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="title" class="label">Judul kegiatan <span class="text-rose-500">*</span></label>
                <input id="title" name="title" type="text" value="{{ old('title', $session->title) }}" required maxlength="160" placeholder="Sidang Skripsi — Analisis Sentimen Media Sosial" class="input @error('title') ring-rose-400 @enderror">
                @error('title')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="type" class="label">Jenis sidang <span class="text-rose-500">*</span></label>
                <select id="type" name="type" required class="input @error('type') ring-rose-400 @enderror">
                    @foreach (\App\Models\DefenseSession::types() as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $session->type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="status" class="label">Status <span class="text-rose-500">*</span></label>
                <select id="status" name="status" required class="input @error('status') ring-rose-400 @enderror">
                    @foreach (\App\Models\DefenseSession::statuses() as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $session->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="date" class="label">Tanggal sidang <span class="text-rose-500">*</span></label>
                <input id="date" name="date" type="date" value="{{ $dateValue }}" min="{{ $isEdit ? null : today()->toDateString() }}" required class="input @error('date') ring-rose-400 @enderror">
                @error('date')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="start_time" class="label">Mulai <span class="text-rose-500">*</span></label>
                    <input id="start_time" name="start_time" type="time" value="{{ $startValue }}" required class="input @error('start_time') ring-rose-400 @enderror">
                    @error('start_time')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_hour" class="label">Selesai <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-1.5">
                        <select id="end_hour" class="input min-w-0" aria-label="Jam selesai" required>
                            @for ($hour = $minEndHour; $hour <= 23; $hour++)
                                <option value="{{ $hour }}" @selected($hour === $endHour)>{{ sprintf('%02d', $hour) }}</option>
                            @endfor
                        </select>
                        <span class="text-sm font-semibold text-slate-400">:</span>
                        <select id="end_minute" class="input min-w-0" aria-label="Menit selesai" required>
                            @php($minuteStart = $endHour === $minEndHour ? $minEndMinute : 0)
                            @for ($minute = $minuteStart; $minute <= 59; $minute++)
                                <option value="{{ $minute }}" @selected($minute === $endMinute)>{{ sprintf('%02d', $minute) }}</option>
                            @endfor
                        </select>
                    </div>
                    <input type="hidden" id="end_time" name="end_time" value="{{ $endValue }}">
                    @error('end_time')
                        <p class="field-error">{{ $message }}</p>
                    @else
                        <p class="hint">Pilihan jam mengikuti waktu mulai.</p>
                    @enderror
                </div>
            </div>

            <div class="sm:col-span-2">
                <label for="room_id" class="label">Ruang sidang <span class="text-rose-500">*</span></label>
                <select id="room_id" name="room_id" required class="input @error('room_id') ring-rose-400 @enderror">
                    <option value="">Pilih ruang…</option>
                    @foreach ($rooms->groupBy(fn ($room) => $room->building->name) as $buildingName => $group)
                        <optgroup label="{{ $buildingName }}">
                            @foreach ($group as $room)
                                <option value="{{ $room->id }}" @selected((string) old('room_id', $session->room_id) === (string) $room->id)>
                                    {{ $room->code }} — {{ $room->name }} · {{ $room->capacity }} orang{{ $room->status !== 'tersedia' ? ' ('.$room->status_label.')' : '' }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('room_id')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2 rounded-xl border border-slate-200 bg-slate-50/60 p-4" id="room-availability" data-state="idle">
                <div class="flex items-start gap-2.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-slate-400 ring-1 ring-slate-200">
                        <x-icon name="clock" class="h-4 w-4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-slate-700">Cek ketersediaan ruang</p>
                        <p class="mt-0.5 text-[11px] text-slate-400" data-availability-message>Pilih ruang dan tanggal untuk melihat jadwal lain pada waktu yang sama.</p>
                        <ul class="mt-2.5 hidden space-y-1.5" data-availability-list></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="section-title">Peserta & penguji</h2>
                <p class="mt-0.5 text-xs text-slate-500">Informasi mahasiswa dan dosen yang terlibat.</p>
            </div>
        </div>

        <div class="card-pad grid gap-5 sm:grid-cols-2">
            <div>
                <label for="student_name" class="label">Nama mahasiswa <span class="text-rose-500">*</span></label>
                <input id="student_name" name="student_name" type="text" value="{{ old('student_name', $session->student_name) }}" required maxlength="120" placeholder="Nama lengkap mahasiswa" class="input @error('student_name') ring-rose-400 @enderror">
                @error('student_name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="student_nim" class="label">NIM <span class="text-rose-500">*</span></label>
                <input id="student_nim" name="student_nim" type="text" inputmode="numeric" value="{{ old('student_nim', $session->student_nim) }}" required maxlength="20" placeholder="2110101001" class="input font-mono @error('student_nim') ring-rose-400 @enderror">
                @error('student_nim')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="supervisor_name" class="label">Dosen pembimbing</label>
                <input id="supervisor_name" name="supervisor_name" type="text" value="{{ old('supervisor_name', $session->supervisor_name) }}" maxlength="120" placeholder="Nama dosen pembimbing" class="input @error('supervisor_name') ring-rose-400 @enderror">
                @error('supervisor_name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="examiner_name" class="label">Dosen penguji</label>
                <input id="examiner_name" name="examiner_name" type="text" value="{{ old('examiner_name', $session->examiner_name) }}" maxlength="120" placeholder="Nama dosen penguji" class="input @error('examiner_name') ring-rose-400 @enderror">
                @error('examiner_name')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="notes" class="label">Catatan</label>
                <textarea id="notes" name="notes" rows="3" maxlength="1000" placeholder="Catatan tambahan mengenai jalannya sidang…" class="input resize-y @error('notes') ring-rose-400 @enderror">{{ old('notes', $session->notes) }}</textarea>
                @error('notes')
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3.5 sm:px-6">
            <a href="{{ $isEdit ? route('schedules.show', $session) : route('schedules.index') }}" class="btn-secondary btn-sm">Batalkan</a>
            <button type="submit" class="btn-primary btn-sm">
                <x-icon name="check" class="h-4 w-4" />
                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Jadwal' }}
            </button>
        </div>
    </div>
</form>
