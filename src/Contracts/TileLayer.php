<?php

namespace Swis\Filament\Geometry\Contracts;

interface TileLayer
{
    public function url(): string;

    /**
     * @return array<string, mixed>
     */
    public function options(): array;
}
