@extends('layouts.print')

@section('title', 'Laporan Bulanan')

@section('content')
    @php
        $monthLabel = $utilization['month']->translatedFormat('F Y');
        $conditionTotal = max(1, collect($conditions)->sum('count'));
    @endphp

    <div class="mb-5">
        <h1 class="text-xl font-bold tracking-tight">Laporan Ruang & Fasilitas Sidang</h1>
        <p class="mt-1 text-sm text-slate-500">Periode {{ $monthLabel }} · Dicetak {{ $printedAt->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>

    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-lg border border-slate-200 p-3">
            <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Total Ruang</p>
            <p class="mt-1 text-lg font-bold">{{ $overview['rooms'] }}</p>
            <p class="text-[9px] text-slate-500">{{ $overview['rooms_available'] }} tersedia</p>
        </div>
        <div class="rounded-lg border border-slate-200 p-3">
            <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Total Sesi</p>
            <p class="mt-1 text-lg font-bold">{{ $overview['sessions_total'] }}</p>
            <p class="text-[9px] text-slate-500">{{ $overview['sessions_today'] }} hari ini</p>
        </div>
        <div class="rounded-lg border border-slate-200 p-3">
            <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Jam Terpakai</p>
            <p class="mt-1 text-lg font-bold">{{ \App\Support\ModuleStats::formatMinutes($utilization['total_minutes']) }}</p>
            <p class="text-[9px] text-slate-500">{{ $monthLabel }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 p-3">
            <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Rata-rata Utilisasi</p>
            <p class="mt-1 text-lg font-bold">{{ $utilization['average'] }}%</p>
            <p class="text-[9px] text-slate-500">{{ number_format($utilization['office_minutes'] / 60, 0, ',', '.') }} jam kerja</p>
        </div>
    </div>

    <section class="mb-6 print-break">
        <h2 class="mb-2 text-sm font-bold uppercase tracking-wide">A. Utilisasi Ruang</h2>
        <table class="w-full border-collapse text-xs">
            <thead>
                <tr class="bg-slate-100 text-left">
                    <th class="border border-slate-300 px-2 py-1.5 font-semibold">No</th>
                    <th class="border border-slate-300 px-2 py-1.5 font-semibold">Ruang</th>
                    <th class="border border-slate-300 px-2 py-1.5 font-semibold">Gedung</th>
                    <th class="border border-slate-300 px-2 py-1.5 text-center font-semibold">Sesi</th>
                    <th class="border border-slate-300 px-2 py-1.5 text-center font-semibold">Durasi</th>
                    <th class="border border-slate-300 px-2 py-1.5 text-right font-semibold">Utilisasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($utilization['rows'] as $index => $row)
                    <tr>
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ $index + 1 }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">
                            <span class="font-medium">{{ $row['room']->code }}</span> — {{ $row['room']->name }}
                        </td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ $row['room']->building?->name }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ $row['sessions'] }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ \App\Support\ModuleStats::formatMinutes($row['minutes']) }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-right font-semibold">{{ $row['percent'] }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="border border-slate-300 px-2 py-3 text-center text-slate-500">Belum ada data ruang.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-slate-50 font-semibold">
                    <td colspan="3" class="border border-slate-300 px-2 py-1.5">Total</td>
                    <td class="border border-slate-300 px-2 py-1.5 text-center">{{ $utilization['rows']->sum('sessions') }}</td>
                    <td class="border border-slate-300 px-2 py-1.5 text-center">{{ \App\Support\ModuleStats::formatMinutes($utilization['total_minutes']) }}</td>
                    <td class="border border-slate-300 px-2 py-1.5 text-right">{{ $utilization['average'] }}%</td>
                </tr>
            </tfoot>
        </table>
    </section>

    <section class="mb-6 print-break">
        <h2 class="mb-2 text-sm font-bold uppercase tracking-wide">B. Kondisi Fasilitas</h2>
        <table class="w-full border-collapse text-xs">
            <thead>
                <tr class="bg-slate-100 text-left">
                    <th class="border border-slate-300 px-2 py-1.5 font-semibold">Kondisi</th>
                    <th class="border border-slate-300 px-2 py-1.5 text-center font-semibold">Penempatan</th>
                    <th class="border border-slate-300 px-2 py-1.5 text-center font-semibold">Total Unit</th>
                    <th class="border border-slate-300 px-2 py-1.5 text-right font-semibold">Proporsi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($conditions as $condition)
                    <tr>
                        <td class="border border-slate-300 px-2 py-1.5">{{ $condition['label'] }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ $condition['count'] }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ $condition['units'] }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-right">{{ round($condition['count'] / $conditionTotal * 100, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="mt-1.5 text-[10px] text-slate-500">Proporsi dihitung dari jumlah penempatan fasilitas pada seluruh ruang, bukan jumlah unit.</p>
    </section>

    <section class="mb-8 print-break">
        <h2 class="mb-2 text-sm font-bold uppercase tracking-wide">C. Inventaris Fasilitas</h2>
        <table class="w-full border-collapse text-xs">
            <thead>
                <tr class="bg-slate-100 text-left">
                    <th class="border border-slate-300 px-2 py-1.5 font-semibold">Kode</th>
                    <th class="border border-slate-300 px-2 py-1.5 font-semibold">Fasilitas</th>
                    <th class="border border-slate-300 px-2 py-1.5 font-semibold">Kategori</th>
                    <th class="border border-slate-300 px-2 py-1.5 text-center font-semibold">Ruang</th>
                    <th class="border border-slate-300 px-2 py-1.5 text-center font-semibold">Unit</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($inventory as $facility)
                    <tr>
                        <td class="border border-slate-300 px-2 py-1.5 font-mono text-[10px]">{{ $facility->code }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ $facility->name }}</td>
                        <td class="border border-slate-300 px-2 py-1.5">{{ $facility->category_label }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ $facility->rooms_count }}</td>
                        <td class="border border-slate-300 px-2 py-1.5 text-center">{{ $facility->total_units }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="border border-slate-300 px-2 py-3 text-center text-slate-500">Belum ada data fasilitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="mt-10 flex justify-end print-break">
        <div class="w-64 text-center text-xs">
            <p>{{ now()->translatedFormat('d F Y') }}</p>
            <p class="mt-1">Pengelola Modul Ruang & Fasilitas</p>
            <div class="h-16"></div>
            <p class="border-t border-slate-400 pt-1 font-semibold">{{ auth()->user()->name }}</p>
            <p class="text-[10px] text-slate-500">{{ auth()->user()->role_label }}</p>
        </div>
    </div>
@endsection
