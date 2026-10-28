<?php

use Swis\Filament\Geometry\Icons\Marker;

it('places the tip of the marker on the location and opens popups above the marker', function () {
    $options = Marker::make('#ff0000')->options();

    [$width, $height] = $options['iconSize'];

    expect($options['iconAnchor'])->toBe([intdiv($width, 2), $height])
        ->and($options['popupAnchor'])->toBe([0, -$height])
        ->and($options['html'])->toContain("width=\"{$width}\" height=\"{$height}\"")
        ->and($options['html'])->toContain('fill="#ff0000"');
});
