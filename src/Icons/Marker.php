<?php

namespace Swis\Filament\Geometry\Icons;

use Swis\Filament\Geometry\Contracts\Icon;

final class Marker implements Icon
{
    private const MARKER_HTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="%s" width="36" height="36" viewBox="0 0 24 24"><path d="M12 0c-4.198 0-8 3.403-8 7.602 0 4.198 3.469 9.21 8 16.398 4.531-7.188 8-12.2 8-16.398 0-4.199-3.801-7.602-8-7.602zm0 11c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3z"/></svg>';

    public function __construct(private readonly string $color) {}

    public static function make(string $color = '#3b82f6'): self
    {
        return new self($color);
    }

    public function options(): array
    {
        return [
            'html' => sprintf(self::MARKER_HTML, $this->color),
            'className' => '',
            'iconAnchor' => [18, 36],
            'iconSize' => [36, 36],
        ];
    }
}
