<?php

use Illuminate\Support\HtmlString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Swis\Filament\Geometry\GeoJson\Feature;
use Swis\Filament\Geometry\GeoJson\FeatureCollection;
use Swis\Filament\Geometry\Infolists\Geometry;

it('converts an eloquent-spatial geometry to a feature without properties', function () {
    expect(Feature::make(new Point(52.16, 4.49))->toArray())->toBe([
        'type' => 'Feature',
        'geometry' => ['type' => 'Point', 'coordinates' => [4.49, 52.16]],
        'properties' => null,
    ]);
});

it('escapes a string popup', function () {
    $feature = Feature::make(['type' => 'Point', 'coordinates' => [4.49, 52.16]])
        ->popup('<script>alert(1)</script>');

    expect($feature->toArray()['properties'])->toBe(['popup' => '&lt;script&gt;alert(1)&lt;/script&gt;']);
});

it('keeps an htmlable popup as html', function () {
    $feature = Feature::make(['type' => 'Point', 'coordinates' => [4.49, 52.16]])
        ->popup(new HtmlString('<strong>Leiden</strong>'));

    expect($feature->toArray()['properties'])->toBe(['popup' => '<strong>Leiden</strong>']);
});

it('can be used as the state of the infolist entry, with popups enabled', function () {
    $config = Geometry::make('locations')
        ->state(FeatureCollection::make(collect([
            Feature::make(new Point(52.16, 4.49))->popup('Leiden'),
        ])))
        ->getMapConfig();

    expect($config['popups'])->toBeTrue();
    expect($config['value'])->toBe([
        'type' => 'FeatureCollection',
        'features' => [
            [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [4.49, 52.16]],
                'properties' => ['popup' => 'Leiden'],
            ],
        ],
    ]);
});
