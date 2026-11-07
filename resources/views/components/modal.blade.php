@props(['id', 'title' => null, 'subtitle' => null, 'maxWidth' => 'max-w-lg'])

<div id="{{ $id }}" data-modal class="fixed inset-0 z-50 hidden items-center justify-center overflow-y-auto bg-slate-950/50 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title">
    <div class="relative w-full {{ $maxWidth }} animate-scale-in">
        <div class="overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5">
            @if ($title)
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 id="{{ $id }}-title" class="text-base font-semibold text-slate-900">{{ $title }}</h2>
                        @if ($subtitle)
                            <p class="mt-1 text-xs text-slate-500">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <button type="button" data-modal-close class="btn-icon text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup dialog">
                        <x-icon name="x" class="h-4 w-4" />
                    </button>
                </div>
            @endif

            {{ $slot }}

            @if (isset($footer))
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-5 py-3.5 sm:px-6">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
