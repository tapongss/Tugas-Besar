@if ($errors->any())
    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4">
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                <x-icon name="alert" class="h-4 w-4" />
            </span>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-rose-800">Terdapat {{ $errors->count() }} kesalahan pada formulir.</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-xs text-rose-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
