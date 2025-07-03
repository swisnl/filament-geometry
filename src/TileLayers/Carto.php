<?php

namespace Swis\Filament\Geometry\TileLayers;

use Swis\Filament\Geometry\Contracts\TileLayer;

final class Carto implements TileLayer
{
    public static function make(): self
    {
        return new self;
    }

    public function url(): string
    {
        return 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png';
    }

    public function options(): array
    {
        return [
            'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attribution">CARTO</a>',
            'maxZoom' => 19,
            'minZoom' => 0,
            'noWrap' => true,
        ];
    }
}
