<?php

namespace Swis\Filament\Geometry\Enums;

enum DrawMode: string
{
    case Marker = 'marker';
    case Polygon = 'polygon';
    case Polyline = 'polyline';
    case Rectangle = 'rectangle';
}
