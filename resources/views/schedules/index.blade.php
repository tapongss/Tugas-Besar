@extends('layouts.app')

@section('title', 'Jadwal Sidang')

@section('breadcrumbs')
    <x-breadcrumbs :items="[['label' => 'Jadwal Sidang']]" />
@endsection

@section('content')
    @php
        $hasFilter = array_filter($filters);
        $statusChip = [
            'dijadwalkan' => 'bg-sky-50 text-sky-700 ring-sky-200/80',
            'berlangsung' => 'bg-violet-50 text-violet-700 ring-violet-200/80',
            'selesai' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80',
            'batal' => 'bg-rose-50 text-rose-500 ring-rose-200/80 line-through',
        ];
        $dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    @endphp

    <div class="space-y-5">
        <x-page-header title="Jadwal Sidang" subtitle="Atur jadwal sidang, seminar proposal, dan ujian dengan validasi bentrok ruang otomatis." icon="calendar-days">
            <x-slot:actions>
                <a href="{{ route('reports.schedules.csv', $filters) }}" class="btn-secondary btn-sm">
                    <x-icon name="download" class="h-4 w-4" />
                    Export CSV
                </a>
                @can('manage-module')
                    <a href="{{ route('schedules.create') }}" class="btn-primary btn-sm">
                        <x-icon name="plus" class="h-4 w-4" />
                        Jadwal Baru
                    </a>
                @endcan
            </x-slot:actions>
        </x-page-header>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Total Jadwal', 'value' => $summary['total'], 'icon' => 'calendar', 'tone' => 'bg-brand-50 text-brand-700'],
                ['label' => 'Hari Ini', 'value' => $summary['today'], 'icon' => 'clock', 'tone' => 'bg-sky-50 text-sky-700'],
                ['label' => 'Akan Datang', 'value' => $summary['upcoming'], 'icon' => 'calendar-days', 'tone' => 'bg-emerald-50 text-emerald-700'],
                ['label' => 'Dibatalkan', 'value' => $summary['cancelled'], 'icon' => 'ban', 'tone' => 'bg-rose-50 text-rose-700'],
            ] as $stat)
                <div class="card flex items-center gap-3 p-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $stat['tone'] }}">
                        <x-icon :name="$stat['icon']" class="h-4 w-4" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p>
                        <p class="text-lg font-bold text-slate-900">{{ $stat['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="inline-flex w-fit items-center gap-1 rounded-xl bg-slate-200/60 p-1">
                <a href="{{ route('schedules.index', array_merge($filters, ['view' => 'daftar'])) }}" class="tab-link {{ $viewMode === 'daftar' ? 'tab-link-active' : '' }}">
                    <x-icon name="list" class="h-4 w-4" />
                    Daftar
                </a>
                <a href="{{ route('schedules.index', array_merge($filters, ['view' => 'kalender'])) }}" class="tab-link {{ $viewMode === 'kalender' ? 'tab-link-active' : '' }}">
                    <x-icon name="calendar" class="h-4 w-4" />
                    Kalender
                </a>
            </div>

            @if ($viewMode === 'kalender' && $calendar)
                <div class="flex items-center gap-2">
                    <a href="{{ route('schedules.index', array_merge($filters, ['view' => 'kalender', 'month' => $calendar['prev']])) }}" class="btn-icon border border-slate-200 bg-white text-slate-500 hover:bg-slate-50" title="Bulan sebelumnya">
                        <x-icon name="chevron-left" class="h-4 w-4" />
                    </a>
                    <p class="min-w-36 text-center text-sm font-semibold text-slate-800">{{ $calendar['month']->translatedFormat('F Y') }}</p>
                    <a href="{{ route('schedules.index', array_merge($filters, ['view' => 'kalender', 'month' => $calendar['next']])) }}" class="btn-icon border border-slate-200 bg-white text-slate-500 hover:bg-slate-50" title="Bulan berikutnya">
                        <x-icon name="chevron-right" class="h-4 w-4" />
                    </a>
                    <a href="{{ route('schedules.index', ['view' => 'kalender']) }}" class="btn-secondary btn-sm" title="Bulan ini">Hari ini</a>
                </div>
            @endif
        </div>

        <form method="GET" action="{{ route('schedules.index') }}" class="card card-pad">
            <input type="hidden" name="view" value="{{ $viewMode }}">
            @if ($viewMode === 'kalender' && $calendar)
                <input type="hidden" name="month" value="{{ $calendar['month']->format('Y-m') }}">
            @endif

            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="relative xl:col-span-2">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari kegiatan, mahasiswa, NIM, atau ruang…" class="input pl-10">
                </div>
                <select name="building_id" data-auto-submit class="input">
                    <option value="">Semua gedung</option>
                    @foreach ($buildings as $building)
                        <option value="{{ $building->id }}" @selected((string) ($filters['building_id'] ?? '') === (string) $building->id)>{{ $building->name }}</option>
                    @endforeach
                </select>
                <select name="room_id" data-auto-submit class="input">
                    <option value="">Semua ruang</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}" @selected((string) ($filters['room_id'] ?? '') === (string) $room->id)>{{ $room->code }} — {{ $room->name }}</option>
                    @endforeach
                </select>
                <select name="status" data-auto-submit class="input">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\DefenseSession::statuses() as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="type" data-auto-submit class="input">
                    <option value="">Semua jenis</option>
                    @foreach (\App\Models\DefenseSession::types() as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="sr-only" for="date_from">Dari tanggal</label>
                        <input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="input text-xs" title="Dari tanggal">
                    </div>
                    <div>
                        <label class="sr-only" for="date_to">Sampai tanggal</label>
                        <input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="input text-xs" title="Sampai tanggal">
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">
                        <x-icon name="filter" class="h-4 w-4" />
                        Terapkan
                    </button>
                    @if ($hasFilter)
                        <a href="{{ route('schedules.index', ['view' => $viewMode]) }}" class="btn-secondary" title="Reset filter">
                            <x-icon name="refresh" class="h-4 w-4" />
                        </a>
                    @endif
                </div>
            </div>
            @if ($viewMode === 'daftar')
                <p class="mt-3 text-xs text-slate-500">Menampilkan <span class="font-semibold text-slate-700">{{ $sessions->count() }}</span> dari {{ $sessions->total() }} jadwal.</p>
            @endif
        </form>

        @if ($viewMode === 'kalender' && $calendar)
            <div class="card overflow-hidden">
                <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h2 class="section-title">{{ $calendar['month']->translatedFormat('F Y') }}</h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $calendar['total'] }} jadwal pada bulan ini</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-[10px] text-slate-500">
                        @foreach (['dijadwalkan' => 'Dijadwalkan', 'berlangsung' => 'Berlangsung', 'selesai' => 'Selesai', 'batal' => 'Batal'] as $status => $label)
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full {{ ['dijadwalkan' => 'bg-sky-500', 'berlangsung' => 'bg-violet-500', 'selesai' => 'bg-emerald-500', 'batal' => 'bg-rose-400'][$status] }}"></span>
                                {{ $label }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[820px]">
                        <div class="grid grid-cols-7 border-b border-slate-100 bg-slate-50/80">
                            @foreach ($dayNames as $day)
                                <div class="px-3 py-2.5 text-center text-[10px] font-semibold uppercase tracking-wider {{ in_array($day, ['Sab', 'Min']) ? 'text-slate-400' : 'text-slate-500' }}">{{ $day }}</div>
                            @endforeach
                        </div>

                        @foreach ($calendar['weeks'] as $week)
                            <div class="grid grid-cols-7 border-b border-slate-100 last:border-b-0">
                                @foreach ($week as $day)
                                    <div class="min-h-[116px] border-r border-slate-100 p-1.5 last:border-r-0 {{ ! $day['inMonth'] ? 'bg-slate-50/60' : '' }} {{ $day['isToday'] ? 'bg-brand-50/50' : '' }}">
                                        <div class="mb-1.5 flex items-center justify-between px-0.5">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-semibold {{ $day['isToday'] ? 'bg-brand-600 text-white' : ($day['inMonth'] ? 'text-slate-700' : 'text-slate-300') }}">
                                                {{ $day['date']->format('j') }}
                                            </span>
                                            @if ($day['sessions']->count() > 2)
                                                <span class="text-[9px] font-medium text-slate-400">+{{ $day['sessions']->count() - 2 }}</span>
                                            @endif
                                        </div>

                                        <div class="space-y-1">
                                            @foreach ($day['sessions']->take(2) as $session)
                                                <a href="{{ route('schedules.show', $session) }}" class="block truncate rounded-md px-1.5 py-1 text-[9px] font-medium ring-1 ring-inset transition hover:brightness-95 {{ $statusChip[$session->status] ?? 'bg-slate-50 text-slate-600 ring-slate-200' }}" title="{{ $session->title }} · {{ $session->time_range }} · {{ $session->room->code }}">
                                                    <span class="font-semibold">{{ $session->start_time_label }}</span>
                                                    {{ $session->room->code }} · {{ \Illuminate\Support\Str::limit($session->student_name, 12) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/50 px-5 py-3 text-[11px] text-slate-500">
                    <p>Klik salah satu jadwal untuk melihat detail.</p>
                    @can('manage-module')
                        <a href="{{ route('schedules.create', ['date' => $calendar['month']->isCurrentMonth() ? today()->toDateString() : $calendar['month']->toDateString()]) }}" class="inline-flex items-center gap-1 font-semibold text-brand-700 hover:text-brand-800">
                            <x-icon name="plus" class="h-3.5 w-3.5" />
                            Tambah jadwal
                        </a>
                    @endcan
                </div>
            </div>
        @else
            <div class="card overflow-hidden">
                @if ($sessions->isEmpty())
                    <x-empty-state icon="calendar-days" title="Jadwal tidak ditemukan" description="Belum ada jadwal yang cocok dengan filter Anda. Buat jadwal baru atau ubah kriteria pencarian.">
                        <x-slot:actions>
                            @can('manage-module')
                                <a href="{{ route('schedules.create') }}" class="btn-primary btn-sm">
                                    <x-icon name="plus" class="h-4 w-4" />
                                    Jadwal Baru
                                </a>
                            @endcan
                            <a href="{{ route('schedules.index') }}" class="btn-secondary btn-sm">Reset Filter</a>
                        </x-slot:actions>
                    </x-empty-state>
                @else
                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Tanggal & Waktu</th>
                                    <th>Kegiatan</th>
                                    <th>Mahasiswa</th>
                                    <th>Ruang</th>
                                    <th>Status</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sessions as $session)
                                    <tr>
                                        <td class="whitespace-nowrap">
                                            <div class="flex items-center gap-2.5">
                                                <div class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg bg-slate-50 text-slate-700 ring-1 ring-slate-200/70">
                                                    <span class="text-xs font-bold leading-none">{{ $session->date->format('d') }}</span>
                                                    <span class="text-[8px] font-semibold uppercase">{{ $session->date->translatedFormat('M') }}</span>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-semibold text-slate-800">{{ $session->date->translatedFormat('D, d M Y') }}</p>
                                                    <p class="text-[10px] text-slate-400">{{ $session->time_range }} · {{ $session->duration }} menit</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('schedules.show', $session) }}" class="block max-w-64 truncate text-sm font-semibold text-slate-800 hover:text-brand-700">{{ $session->title }}</a>
                                            <p class="text-[10px] text-slate-400">{{ $session->type_label }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-medium text-slate-700">{{ $session->student_name }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $session->student_nim }}</p>
                                        </td>
                                        <td>
                                            <a href="{{ route('rooms.show', $session->room) }}" class="text-xs font-medium text-slate-600 hover:text-brand-700">{{ $session->room->code }}</a>
                                            <p class="text-[10px] text-slate-400">{{ $session->room->building?->name }}</p>
                                        </td>
                                        <td><x-status-badge :status="$session->status" :label="$session->status_label" /></td>
                                        <td>
                                            <div class="flex items-center justify-end gap-1">
                                                <a href="{{ route('schedules.show', $session) }}" class="btn-icon text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Detail jadwal">
                                                    <x-icon name="eye" class="h-4 w-4" />
                                                </a>
                                                @can('manage-module')
                                                    <a href="{{ route('schedules.edit', $session) }}" class="btn-icon text-slate-400 hover:bg-slate-100 hover:text-slate-700" title="Edit jadwal">
                                                        <x-icon name="pencil" class="h-4 w-4" />
                                                    </a>
                                                    <form method="POST" action="{{ route('schedules.destroy', $session) }}" data-confirm="Jadwal {{ $session->title }} akan dihapus permanen." data-confirm-title="Hapus jadwal?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn-icon text-slate-400 hover:bg-rose-50 hover:text-rose-600" title="Hapus jadwal">
                                                            <x-icon name="trash" class="h-4 w-4" />
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-slate-100 px-4 py-3">
                        {{ $sessions->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
