<?php

namespace Swis\Filament\Geometry\Contracts;

interface Bounds
{
    /**
     * @return array{'sw': array{'lat': float, 'lng': float}, 'ne': array{'lat': float, 'lng': float}}
     */
    public function toArray(): array;

    /**
     * @return array{'lat': float, 'lng': float}
     */
    public function center(): array;
}
