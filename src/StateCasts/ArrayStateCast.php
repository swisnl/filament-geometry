<?php

namespace Swis\Filament\Geometry\StateCasts;

use Filament\Schemas\Components\StateCasts\Contracts\StateCast;

class ArrayStateCast implements StateCast
{
    /**
     * @return array<string, mixed>|null
     */
    public function get(mixed $state): ?array
    {
        return self::decode($state);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function decode(mixed $state): ?array
    {
        if (blank($state)) {
            return null;
        }

        try {
            return json_decode($state, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
    }

    public function set(mixed $state): ?string
    {
        if (! is_array($state)) {
            return $state;
        }

        return json_encode($state, JSON_THROW_ON_ERROR);
    }
}
