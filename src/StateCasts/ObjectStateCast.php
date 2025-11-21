<?php

namespace Swis\Filament\Geometry\StateCasts;

use Filament\Schemas\Components\StateCasts\Contracts\StateCast;

class ObjectStateCast implements StateCast
{
    public function get(mixed $state): ?object
    {
        if (blank($state)) {
            return null;
        }

        return json_decode($state, false, flags: JSON_THROW_ON_ERROR);
    }

    public function set(mixed $state): ?string
    {
        if (! $state instanceof \stdClass) {
            return $state;
        }

        return json_encode($state, JSON_THROW_ON_ERROR);
    }
}
