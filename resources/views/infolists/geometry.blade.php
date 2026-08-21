@php
    $extraAttributeBag = $getExtraAttributeBag();
    $entryWrapperView = $getEntryWrapperView();
    $mapConfig = $getMapConfig();
@endphp

<x-dynamic-component :component="$entryWrapperView" :entry="$entry">
    @if ($mapConfig['value'] !== null)
        <div
            {{ $extraAttributeBag->class(['overflow-hidden', 'rounded-xl']) }}
            x-ignore
            x-load
            x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-geometry-styles', 'swisnl/filament-geometry'))]"
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-geometry-scripts', 'swisnl/filament-geometry') }}"
            x-data="filamentGeometry($wire, $watch, @js([...$mapConfig, 'disabled' => true]))"
            wire:ignore
            wire:key="{{ md5(json_encode($mapConfig['value'])) }}"
            x-intersect.once="create($refs.map)"
        >
            <div x-ref="map" class="h-[40dvh] z-0"></div>
        </div>
    @else
        <x-filament::empty-state icon="heroicon-o-map-pin" icon-color="info" class="flex h-[40dvh] items-center justify-center">
            <x-slot name="heading">
                {{ $getPlaceholder() ?? __('filament-geometry::geometry.no_geometry', ['label' => $entry->getLabel()]) }}
            </x-slot>
        </x-filament::empty-state>
    @endif
</x-dynamic-component>
