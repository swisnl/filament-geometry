<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <x-filament::input.wrapper :valid="!$errors->has($getStatePath())" class="overflow-hidden">
        <div
            x-ignore
            x-load
            x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-geometry-styles', 'swisnl/filament-geometry'))]"
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-geometry-scripts', 'swisnl/filament-geometry') }}"
            x-data="filamentGeometry($wire, $watch, {{ $getMapConfig() }})"
            wire:ignore
            x-intersect.once="create($refs.map)"
        >
            <div x-ref="map" class="h-[40dvh] z-0"></div>
        </div>
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
</x-dynamic-component>
