@php
    $flashMap = [
        'success' => ['icon' => 'check-circle', 'wrap' => 'border-emerald-200 bg-white', 'badge' => 'bg-emerald-100 text-emerald-600', 'title' => 'Berhasil'],
        'error' => ['icon' => 'alert', 'wrap' => 'border-rose-200 bg-white', 'badge' => 'bg-rose-100 text-rose-600', 'title' => 'Gagal'],
        'warning' => ['icon' => 'alert', 'wrap' => 'border-amber-200 bg-white', 'badge' => 'bg-amber-100 text-amber-600', 'title' => 'Perhatian'],
        'info' => ['icon' => 'info', 'wrap' => 'border-sky-200 bg-white', 'badge' => 'bg-sky-100 text-sky-600', 'title' => 'Informasi'],
    ];
@endphp

<div class="pointer-events-none fixed inset-x-4 top-4 z-[70] flex flex-col items-end gap-2 sm:inset-x-auto sm:right-5 sm:w-full sm:max-w-sm">
    @foreach ($flashMap as $type => $style)
        @if (session($type))
            <div data-flash class="pointer-events-auto flex w-full items-start gap-3 rounded-xl border {{ $style['wrap'] }} p-3.5 shadow-lg shadow-slate-900/5 transition duration-200 animate-fade-up">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $style['badge'] }}">
                    <x-icon :name="$style['icon']" class="h-4 w-4" />
                </span>
                <div class="min-w-0 flex-1 pt-0.5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $style['title'] }}</p>
                    <p class="mt-0.5 text-sm leading-snug text-slate-700">{{ session($type) }}</p>
                </div>
                <button type="button" data-dismiss class="rounded-md p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup notifikasi">
                    <x-icon name="x" class="h-3.5 w-3.5" />
                </button>
            </div>
        @endif
    @endforeach
</div>
