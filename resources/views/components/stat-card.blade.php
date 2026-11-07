@props(['label', 'value', 'icon' => 'dashboard', 'tone' => 'brand', 'hint' => null, 'href' => null])

@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-100',
        'sky' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-100',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'rose' => 'bg-rose-50 text-rose-700 ring-rose-100',
        'emerald' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'slate' => 'bg-slate-100 text-slate-700 ring-slate-200',
    ];

    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif class="group card card-pad transition duration-200 @if ($href) hover:-translate-y-0.5 hover:shadow-md @endif">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
            @endif
        </div>
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1 {{ $tones[$tone] ?? $tones['brand'] }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
    </div>
</{{ $tag }}>
