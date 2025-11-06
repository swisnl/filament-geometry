<?php

namespace Swis\Filament\Geometry\Contracts;

interface GeoSearchProvider
{
    public function name(): string;

    /**
     * @return array<string, mixed>
     */
    public function options(): array;
}
