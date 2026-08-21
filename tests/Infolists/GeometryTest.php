<?php

use MatanYadaev\EloquentSpatial\Objects\Point;
use Swis\Filament\Geometry\Infolists\Geometry;

it('has the expected map config keys', function () {
    $config = Geometry::make('location')->state(null)->getMapConfig();

    expect($config)->toHaveKeys(['value', 'bounds', 'map', 'markerIcon', 'tileLayer']);
    expect($config['value'])->toBeNull();
});

it('decodes a json string state', function () {
    $config = Geometry::make('location')
        ->state('{"type":"Point","coordinates":[4.49,52.16]}')
        ->getMapConfig();

    expect($config['value'])->toBe([
        'type' => 'Point',
        'coordinates' => [4.49, 52.16],
    ]);
});

it('passes through an array state', function () {
    $config = Geometry::make('location')
        ->state(['type' => 'Point', 'coordinates' => [4.49, 52.16]])
        ->getMapConfig();

    expect($config['value'])->toBe([
        'type' => 'Point',
        'coordinates' => [4.49, 52.16],
    ]);
});

it('normalizes an eloquent-spatial geometry state', function () {
    $config = Geometry::make('location')
        ->state(new Point(52.16, 4.49))
        ->getMapConfig();

    expect($config['value'])->toBe([
        'type' => 'Point',
        'coordinates' => [4.49, 52.16],
    ]);
});

it('treats a blank state as no location', function () {
    $config = Geometry::make('location')
        ->state('')
        ->getMapConfig();

    expect($config['value'])->toBeNull();
});

it('treats a non-json string state as no location instead of throwing', function () {
    $config = Geometry::make('location')
        ->state('POINT(4.49 52.16)')
        ->getMapConfig();

    expect($config['value'])->toBeNull();
});

it('treats an object with no encodable data as no location', function () {
    $config = Geometry::make('location')
        ->state(new stdClass)
        ->getMapConfig();

    expect($config['value'])->toBeNull();
});

it('treats a scalar state as no location', function () {
    $config = Geometry::make('location')
        ->state(true)
        ->getMapConfig();

    expect($config['value'])->toBeNull();
});
