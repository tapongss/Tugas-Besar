@props(['title', 'subtitle' => null, 'icon' => null])

<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="flex items-start gap-3.5">
        @if ($icon)
            <span class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 ring-1 ring-brand-100 sm:flex">
                <x-icon :name="$icon" class="h-5 w-5" />
            </span>
        @endif
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if (isset($actions) && ! $actions->isEmpty())
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
