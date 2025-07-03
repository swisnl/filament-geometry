# Filament geometry

<div class="filament-hidden">

[![Latest Version on Packagist](https://img.shields.io/packagist/v/swisnl/filament-geometry.svg?style=flat-square)](https://packagist.org/packages/swisnl/filament-geometry)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Buy us a tree](https://img.shields.io/badge/Treeware-%F0%9F%8C%B3-lightgreen.svg?style=flat-square)](https://plant.treeware.earth/swisnl/filament-geometry)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/swisnl/filament-geometry/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/swisnl/filament-geometry/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/swisnl/filament-geometry/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/swisnl/filament-geometry/actions?query=workflow%3A"Fix+PHP+Code+Styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/swisnl/filament-geometry.svg?style=flat-square)](https://packagist.org/packages/swisnl/filament-geometry)
[![Made by SWIS](https://img.shields.io/badge/%F0%9F%9A%80-made%20by%20SWIS-%230737A9.svg?style=flat-square)](https://www.swis.nl)

![Filament geometry screenshot](https://github.com/swisnl/filament-geometry/blob/main/art/screenshot.png)

</div>

> [!WARNING]
> Work in progress, not stable yet!
>

## Installation

You can install the package via composer:

```bash
composer require swisnl/filament-geometry
```

You can publish the views using

```bash
php artisan vendor:publish --tag="filament-geometry-views"
```

## Component

### Geometry Field

The **Geometry** field displays a leaflet map, with a set of configuration options.

![Map Field](art/screenshot.png)

## Usage

### Geometry Field

> **Note:** The Geometry field expects the value to be a valid [GeoJSON](https://geojson.org/) string. Make sure your model attribute stores and retrieves GeoJSON data as a string.

The form field can be used with no options, by simply adding this to your Filament
Form schema:

```php
use Swis\Filament\Geometry\Enums\DrawMode;
use Swis\Filament\Geometry\Forms\Geometry;
...
->schema[
    ...
    Geometry::make('location')
        ->drawModes([
            DrawMode::Marker,
            DrawMode::Polygon,
            DrawMode::Polyline,
            DrawMode::Rectangle,
        ]),
    ...
]
```
The name used for make() must be the one you set up as your model's computed geojson
property. Note that you can have multiple maps on a form, by adding a second computed
property referencing a second pair of geojson fields.

#### Full options

The full set of options is as follows.  All option methods support closures, as well as direct values.

```php
use Swis\Filament\Geometry\Enums\ControlPosition
use Swis\Filament\Geometry\Enums\DrawMode;
use Swis\Filament\Geometry\Forms\Geometry;
use Swis\Filament\Geometry\TileLayers\Carto;

...

Geometry::make('location')
    ->label(__('Location'))

    // Map configuration
    ->maxZoom(16)
    ->minZoom(4)
    ->center(52.164206390898904, 4.491920969490259)
    ->zoom(15)
    ->boundaries(true, 49.5, -11, 61, 2) // Example for British Isles
    ->tileLayer(Carto::make())

    // Marker configuration
    ->markerColor("#3b82f6")
    
    // Controls
    ->showFullscreenControl(true)
    ->showZoomControl(true)
    ->showAttributionControl(true)
    ->drawModes([
        DrawMode::Polygon,
        DrawMode::Rectangle,
    ])
    ->drawControlPosition(ControlPosition::TopRight)
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](https://github.com/swisnl/filament-geometry/blob/main/CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/swisnl/filament-geometry/blob/main/.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](https://github.com/swisnl/filament-geometry/security/policy) on how to report security vulnerabilities.

## Credits

- [Pjotr van der Horst](https://github.com/pjotrvdh)
- [All Contributors](https://github.com/swisnl/filament-geometry/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/swisnl/filament-geometry/blob/main/LICENSE.md) for more information.

## SWIS ❤️ Open Source

[SWIS](https://www.swis.nl) is a web agency from Leiden, the Netherlands. We love working with open source software.
