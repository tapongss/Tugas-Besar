@props(['status', 'label' => null])

@php
    $map = [
        'tersedia' => ['bg-emerald-50 text-emerald-700 ring-emerald-200/70', 'bg-emerald-500'],
        'digunakan' => ['bg-sky-50 text-sky-700 ring-sky-200/70', 'bg-sky-500'],
        'perbaikan' => ['bg-amber-50 text-amber-700 ring-amber-200/70', 'bg-amber-500'],
        'nonaktif' => ['bg-slate-100 text-slate-600 ring-slate-200', 'bg-slate-400'],
        'aktif' => ['bg-emerald-50 text-emerald-700 ring-emerald-200/70', 'bg-emerald-500'],
        'dijadwalkan' => ['bg-sky-50 text-sky-700 ring-sky-200/70', 'bg-sky-500'],
        'berlangsung' => ['bg-violet-50 text-violet-700 ring-violet-200/70', 'bg-violet-500'],
        'selesai' => ['bg-emerald-50 text-emerald-700 ring-emerald-200/70', 'bg-emerald-500'],
        'batal' => ['bg-rose-50 text-rose-700 ring-rose-200/70', 'bg-rose-500'],
        'baik' => ['bg-emerald-50 text-emerald-700 ring-emerald-200/70', 'bg-emerald-500'],
        'rusak_ringan' => ['bg-amber-50 text-amber-700 ring-amber-200/70', 'bg-amber-500'],
        'rusak_berat' => ['bg-rose-50 text-rose-700 ring-rose-200/70', 'bg-rose-500'],
    ];

    [$badge, $dot] = $map[$status] ?? ['bg-slate-100 text-slate-600 ring-slate-200', 'bg-slate-400'];
    $text = $label ?? \Illuminate\Support\Str::of($status)->replace('_', ' ')->title();
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset {$badge}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>
    {{ $text }}
</span>
