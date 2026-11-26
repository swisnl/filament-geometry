@props([
    'description' => null,
    'footer' => null,
    'heading' => null,
])

@php
    use function Filament\Support\is_slot_empty;
@endphp

<div {{ $attributes->class(['grid gap-6']) }}>
    @if (filled($heading) || filled($description))
        <div>
            @if (filled($heading))
                <h2 class="text-base leading-6 font-semibold text-gray-950 dark:text-white">
                    {{ $heading }}
                </h2>
            @endif

            @if (filled($description))
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ $description }}
                </p>
            @endif
        </div>
    @endif

    {{ $slot }}

    @if (! is_slot_empty($footer))
        <div class="flex flex-wrap gap-3">
            {{ $footer }}
        </div>
    @endif
</div>
