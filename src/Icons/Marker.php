<?php

namespace Swis\Filament\Geometry\Icons;

use Swis\Filament\Geometry\Contracts\Icon;

final class Marker implements Icon
{
    private const SIZE = 36;

    private const MARKER_HTML = '<svg xmlns="http://www.w3.org/2000/svg" width="%2$d" height="%2$d" viewBox="0 0 24 24"><path fill="%1$s" d="M 12,0 C 7.802,0 4,3.403 4,7.602 4,11.8 7.469,16.812 12,24 16.531,16.812 20,11.8 20,7.602 20,3.403 16.199,0 12,0 Z" /><path fill="#ffffff" d="M 12,11 C 10.343,11 9,9.657 9,8 9,6.343 10.343,5 12,5 c 1.657,0 3,1.343 3,3 0,1.657 -1.343,3 -3,3 z" /></svg>';

    public function __construct(private readonly string $color) {}

    public static function make(string $color = '#3b82f6'): self
    {
        return new self($color);
    }

    public function options(): array
    {
        return [
            'html' => sprintf(self::MARKER_HTML, $this->color, self::SIZE),
            'className' => '',
            'iconAnchor' => [intdiv(self::SIZE, 2), self::SIZE],
            'iconSize' => [self::SIZE, self::SIZE],
            'popupAnchor' => [0, -self::SIZE],
        ];
    }
}
