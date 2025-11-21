<?php

namespace Swis\Filament\Geometry\StateCasts;

use Filament\Schemas\Components\StateCasts\Contracts\StateCast;

class StringStateCast implements StateCast
{
    public function get(mixed $state): mixed
    {
        return $state;
    }

    public function set(mixed $state): mixed
    {
        return $state;
    }
}
