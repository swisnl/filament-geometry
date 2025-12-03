@php
    $extraAttributeBag = $getExtraAttributeBag();
    $fieldWrapperView = $getFieldWrapperView();
    $id = $getId();
    $isDisabled = $isDisabled();
    $statePath = $getStatePath();
    $mapConfig = $getMapConfig();
@endphp

<x-dynamic-component :component="$fieldWrapperView" :field="$field">
    <x-filament::input.wrapper
        :disabled="$isDisabled"
        :valid="!$errors->has($statePath)"
        :attributes="
            \Filament\Support\prepare_inherited_attributes($extraAttributeBag)
                ->class(['overflow-hidden'])
        "
    >
        <div
            x-ignore
            x-load
            x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-geometry-styles', 'swisnl/filament-geometry'))]"
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-geometry-scripts', 'swisnl/filament-geometry') }}"
            x-data="filamentGeometry($wire, $watch, @js([...$mapConfig, 'disabled' => $isDisabled]))"
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
                    'id' => $id,
                    'type' => 'hidden',
                    'disabled' => $isDisabled,
                    $applyStateBindingModifiers('wire:model') => $statePath,
                ], escape: FALSE)
        }}
    />
</x-dynamic-component>
