<?php

namespace Swis\Filament\Geometry\GeoSearchProviders;

use Swis\Filament\Geometry\Contracts\GeoSearchProvider;

class OpenStreetMap implements GeoSearchProvider
{
    public static function make(): self
    {
        return new self;
    }

    public function name(): string
    {
        return 'OpenStreetMapProvider';
    }

    public function options(): array
    {
        return [];
    }
}
