@extends('layouts.app')

@section('title', $session->title)

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Jadwal Sidang', 'url' => route('schedules.index')], ['label' => $session->title]]" />
@endsection

@section('content')
    <div class="space-y-5">
        <x-page-header :title="$session->title" :subtitle="$session->type_label.' · '.$session->student_name.' ('.$session->student_nim.')'" icon="calendar-days">
            <x-slot:actions>
                <a href="{{ route('schedules.index') }}" class="btn-secondary btn-sm">
                    <x-icon name="arrow-left" class="h-4 w-4" />
                    Kembali
                </a>
                @can('manage-module')
                    <a href="{{ route('schedules.edit', $session) }}" class="btn-primary btn-sm">
                        <x-icon name="pencil" class="h-4 w-4" />
                        Edit Jadwal
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-ink-900 via-ink-900 to-brand-900 p-5 text-white shadow-lg shadow-brand-950/15 sm:p-6">
            <div class="bg-grid pointer-events-none absolute inset-0 opacity-25"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-2xl bg-white/10 text-white ring-1 ring-white/15">
                        <span class="text-2xl font-bold leading-none">{{ $session->date->format('d') }}</span>
                        <span class="mt-1 text-[10px] font-semibold uppercase tracking-wide text-brand-300">{{ $session->date->translatedFormat('M Y') }}</span>
                    </div>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-md bg-white/10 px-2 py-0.5 text-[11px] font-semibold text-brand-200 ring-1 ring-white/15">{{ $session->type_label }}</span>
                            <span class="inline-flex items-center gap-1.5 rounded-full {{ ['dijadwalkan' => 'bg-sky-400/10 text-sky-300 ring-sky-400/20', 'berlangsung' => 'bg-violet-400/10 text-violet-300 ring-violet-400/20', 'selesai' => 'bg-emerald-400/10 text-emerald-300 ring-emerald-400/20', 'batal' => 'bg-rose-400/10 text-rose-300 ring-rose-400/20'][$session->status] ?? 'bg-white/10 text-slate-300 ring-white/15' }} px-2.5 py-1 text-[11px] font-semibold ring-1">
                                <span class="h-1.5 w-1.5 rounded-full {{ ['dijadwalkan' => 'bg-sky-400', 'berlangsung' => 'bg-violet-400', 'selesai' => 'bg-emerald-400', 'batal' => 'bg-rose-400'][$session->status] ?? 'bg-slate-400' }}"></span>
                                {{ $session->status_label }}
                            </span>
                        </div>
                        <p class="mt-2 line-clamp-1 text-lg font-semibold">{{ $session->title }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $session->date->translatedFormat('l, d F Y') }} · {{ $session->time_range }} WIB</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-5">
                    <div>
                        <p class="text-xl font-bold">{{ $session->time_range }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $session->duration }} menit</p>
                    </div>
                    <div class="h-10 w-px bg-white/15"></div>
                    <div>
                        <a href="{{ route('rooms.show', $session->room) }}" class="text-xl font-bold hover:text-brand-300">{{ $session->room->code }}</a>
                        <p class="max-w-40 truncate text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $session->room->name }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-3">
            <div class="space-y-5 xl:col-span-2">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Detail kegiatan</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Informasi lengkap pelaksanaan sidang.</p>
                        </div>
                    </div>
                    <div class="card-pad grid gap-5 sm:grid-cols-2">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Judul kegiatan</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $session->title }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Jenis sidang</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $session->type_label }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Tanggal</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $session->date->translatedFormat('l, d F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Waktu</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $session->time_range }} WIB · {{ $session->duration }} menit</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Ruang</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $session->room->code }} — {{ $session->room->name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Gedung</p>
                            <p class="mt-1 text-sm font-medium text-slate-800">{{ $session->room->building?->name }}</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Peserta & penguji</h2>
                            <p class="mt-0.5 text-xs text-slate-500">Mahasiswa dan dosen yang terlibat dalam sidang.</p>
                        </div>
                    </div>
                    <div class="card-pad">
                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-100">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">{{ \Illuminate\Support\Str::of($session->student_name)->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($w, 0, 1)))->implode('') }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $session->student_name }}</p>
                                <p class="text-xs text-slate-500">NIM {{ $session->student_nim }}</p>
                            </div>
                            <span class="ml-auto hidden rounded-md bg-white px-2 py-1 text-[10px] font-semibold text-slate-500 ring-1 ring-slate-200 sm:inline-flex">Mahasiswa</span>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-200 p-4">
                                <div class="flex items-center gap-2 text-slate-400">
                                    <x-icon name="user" class="h-4 w-4" />
                                    <p class="text-[10px] font-semibold uppercase tracking-wider">Dosen pembimbing</p>
                                </div>
                                <p class="mt-2 text-sm font-medium text-slate-800">{{ $session->supervisor_name ?: 'Belum diisi' }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-200 p-4">
                                <div class="flex items-center gap-2 text-slate-400">
                                    <x-icon name="users" class="h-4 w-4" />
                                    <p class="text-[10px] font-semibold uppercase tracking-wider">Dosen penguji</p>
                                </div>
                                <p class="mt-2 text-sm font-medium text-slate-800">{{ $session->examiner_name ?: 'Belum diisi' }}</p>
                            </div>
                        </div>

                        @if ($session->notes)
                            <div class="mt-4 rounded-xl bg-amber-50 p-4 ring-1 ring-amber-100">
                                <div class="flex items-start gap-2.5">
                                    <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                                    <div>
                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-700">Catatan</p>
                                        <p class="mt-1 text-xs leading-relaxed text-amber-800">{{ $session->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="section-title">Jadwal lain di ruang ini</h2>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $session->date->translatedFormat('d F Y') }} · {{ $session->room->code }}</p>
                        </div>
                        <a href="{{ route('schedules.index', ['room_id' => $session->room_id, 'date_from' => $session->date->toDateString(), 'date_to' => $session->date->toDateString()]) }}" class="text-xs font-semibold text-brand-700 hover:text-brand-800">Lihat jadwal</a>
                    </div>

                    @if ($roomSessions->isEmpty())
                        <div class="flex items-center gap-3 px-5 py-6 sm:px-6">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                                <x-icon name="check-circle" class="h-4 w-4" />
                            </span>
                            <div>
                                <p class="text-sm font-medium text-slate-700">Tidak ada jadwal lain</p>
                                <p class="text-xs text-slate-500">Ruang ini hanya digunakan untuk kegiatan ini pada tanggal tersebut.</p>
                            </div>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach ($roomSessions as $other)
                                <a href="{{ route('schedules.show', $other) }}" class="group flex items-center gap-4 px-5 py-3.5 transition hover:bg-slate-50/80 sm:px-6">
                                    <div class="flex w-14 shrink-0 flex-col items-center rounded-lg bg-slate-50 py-1.5 ring-1 ring-slate-100">
                                        <span class="text-xs font-bold text-slate-800">{{ $other->start_time_label }}</span>
                                        <span class="text-[9px] text-slate-400">{{ $other->end_time_label }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-800 group-hover:text-brand-700">{{ $other->title }}</p>
                                        <p class="truncate text-[11px] text-slate-400">{{ $other->student_name }} · {{ $other->status_label }}</p>
                                    </div>
                                    <x-icon name="chevron-right" class="h-4 w-4 shrink-0 text-slate-300" />
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-5">
                <div class="card card-pad">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-900">Ruang sidang</h2>
                        <x-status-badge :status="$session->room->live_status" :label="\App\Models\Room::statuses()[$session->room->live_status] ?? $session->room->status_label" />
                    </div>
                    <a href="{{ route('rooms.show', $session->room) }}" class="mt-4 flex items-center gap-3 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-100 transition hover:bg-slate-100/70">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500 ring-1 ring-slate-200">
                            <x-icon name="door" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $session->room->name }}</p>
                            <p class="text-[11px] text-slate-400">{{ $session->room->building?->name }} · {{ $session->room->floor_label }}</p>
                        </div>
                        <x-icon name="chevron-right" class="ml-auto h-4 w-4 shrink-0 text-slate-300" />
                    </a>
                    <div class="mt-3 grid grid-cols-2 gap-2.5 text-center">
                        <div class="rounded-lg bg-slate-50 py-2">
                            <p class="text-sm font-bold text-slate-800">{{ $session->room->capacity }}</p>
                            <p class="text-[9px] uppercase tracking-wide text-slate-400">Kapasitas</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 py-2">
                            <p class="text-sm font-bold text-slate-800">{{ $session->room->facilities()->count() }}</p>
                            <p class="text-[9px] uppercase tracking-wide text-slate-400">Fasilitas</p>
                        </div>
                    </div>
                </div>

                @can('manage-module')
                    <div class="card card-pad">
                        <h2 class="text-sm font-semibold text-slate-900">Perbarui status</h2>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">Ubah status pelaksanaan sidang tanpa mengedit detail lainnya.</p>
                        <form method="POST" action="{{ route('schedules.status', $session) }}" class="mt-3.5 flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="input text-xs" aria-label="Status jadwal">
                                @foreach (\App\Models\DefenseSession::statuses() as $value => $label)
                                    <option value="{{ $value }}" @selected($session->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-primary btn-sm shrink-0">Simpan</button>
                        </form>
                    </div>
                @endcan

                <div class="card">
                    <div class="card-header">
                        <h2 class="section-title">Aktivitas</h2>
                    </div>
                    <div class="p-5">
                        @if ($activities->isEmpty())
                            <p class="text-xs leading-relaxed text-slate-500">Belum ada aktivitas terkait jadwal ini.</p>
                        @else
                            <ul class="space-y-3">
                                @foreach ($activities as $activity)
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
                        <p class="mt-4 border-t border-slate-100 pt-3 text-[10px] leading-relaxed text-slate-400">Dibuat oleh {{ $session->creator?->name ?? 'Sistem' }} · {{ $session->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                @can('manage-module')
                    <div class="card border border-rose-100 p-5">
                        <h2 class="text-sm font-semibold text-slate-900">Hapus jadwal</h2>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">Jadwal yang dihapus tidak dapat dikembalikan. Pertimbangkan mengubah status menjadi batal.</p>
                        <form method="POST" action="{{ route('schedules.destroy', $session) }}" class="mt-3" data-confirm="Jadwal {{ $session->title }} akan dihapus permanen." data-confirm-title="Hapus jadwal?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger-soft btn-sm">
                                <x-icon name="trash" class="h-3.5 w-3.5" />
                                Hapus Jadwal
                            </button>
                        </form>
                    </div>
                @endcan
            </div>
        </div>
    </div>
@endsection
