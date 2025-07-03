<?php

namespace Swis\Filament\Geometry\TileLayers;

use Swis\Filament\Geometry\Contracts\TileLayer;

final class OpenStreetMap implements TileLayer
{
    public static function make(): self
    {
        return new self;
    }

    public function url(): string
    {
        return 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';
    }

    public function options(): array
    {
        return [
            'attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            'maxZoom' => 19,
            'minZoom' => 0,
            'noWrap' => true,
        ];
    }
}
