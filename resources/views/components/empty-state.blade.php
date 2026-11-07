@props(['icon' => 'sparkles', 'title', 'description' => null])

<div class="flex flex-col items-center justify-center px-6 py-14 text-center">
    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 ring-1 ring-slate-200">
        <x-icon :name="$icon" class="h-7 w-7" />
    </span>
    <h3 class="mt-4 text-base font-semibold text-slate-900">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1.5 max-w-sm text-sm leading-relaxed text-slate-500">{{ $description }}</p>
    @endif

    @if (isset($actions) && ! $actions->isEmpty())
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
