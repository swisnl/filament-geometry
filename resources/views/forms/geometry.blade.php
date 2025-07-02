<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-ignore
        x-load
        x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-geometry-styles', 'swisnl/filament-geometry'))]"
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-geometry-scripts', 'swisnl/filament-geometry') }}"
        x-data="filamentGeometry($wire, {{ $getMapConfig() }})"
        wire:ignore
        x-init="$nextTick(() => { attach($refs.map) })"
    >
        <x-filament::input.wrapper class="overflow-hidden">
            <div x-ref="map" class="aspect-[1/1] sm:aspect-[4/3] md:aspect-[16/9] 2xl:aspect-[16/5] z-0"></div>
        </x-filament::input.wrapper>
        <input
            {{
                $attributes
                    ->merge([
                        'id' => $getId(),
                        'type' => 'hidden',
                        $applyStateBindingModifiers('wire:model') => $getStatePath(),
                    ], escape: FALSE)
            }}
        />
    </div>
</x-dynamic-component>
