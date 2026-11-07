@props(['items' => []])

<nav aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-1.5 text-sm">
        <li>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-slate-500 transition hover:text-slate-900">
                <x-icon name="home" class="h-3.5 w-3.5" />
                <span>Dashboard</span>
            </a>
        </li>
        @foreach ($items as $item)
            <li class="text-slate-300">
                <x-icon name="chevron-right" class="h-3.5 w-3.5" />
            </li>
            <li>
                @if (! empty($item['url']))
                    <a href="{{ $item['url'] }}" class="text-slate-500 transition hover:text-slate-900">{{ $item['label'] }}</a>
                @else
                    <span class="font-medium text-slate-800">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
