# Changelog

All notable changes to `swisnl/filament-geometry` will be documented in this file.

## 0.6.1 - 2025-11-13

### What's Changed

#### Fixed

* Watch for changes from Filament/Livewire by @JaZo in https://github.com/swisnl/filament-geometry/pull/22

**Full Changelog**: https://github.com/swisnl/filament-geometry/compare/0.6.0...0.6.1

## 0.6.0 - 2025-11-06

### What's Changed

#### Added

* Add GeoSearch by @JaZo in https://github.com/swisnl/filament-geometry/pull/20

**Full Changelog**: https://github.com/swisnl/filament-geometry/compare/0.5.0...0.6.0

## 0.5.0 - 2025-11-05

### What's Changed

#### Added

* Allow multipart geometries by @JaZo in https://github.com/swisnl/filament-geometry/pull/19

**Full Changelog**: https://github.com/swisnl/filament-geometry/compare/0.4.0...0.5.0

## 0.4.0 - 2025-09-19

### What's Changed

#### Changed

* Upgrade package to Filament 4.x compatibility by @Copilot in https://github.com/swisnl/filament-geometry/pull/16

### New Contributors

* @Copilot made their first contribution in https://github.com/swisnl/filament-geometry/pull/16

**Full Changelog**: https://github.com/swisnl/filament-geometry/compare/0.3.0...0.4.0

## 0.3.0 - 2025-07-21

### What's Changed

#### Changed

* boundaries function is renamed to bounds and now expects a bounds object

#### Fixed

* destroy map, drawItems and tile after closing filament form modal by @pjotrvdh in https://github.com/swisnl/filament-geometry/pull/12

**Full Changelog**: https://github.com/swisnl/filament-geometry/compare/0.2.3...0.3.0

## 0.2.3 - 2025-07-08

### What's Changed

#### Changed

* disable gesture handling in fullscreen mode

**Full Changelog**: https://github.com/swisnl/filament-geometry/compare/0.2.2...0.2.3

## 0.2.2 - 2025-07-07

### What's Changed

#### Fixed

* setting the locale was broken

**Full Changelog**: https://github.com/swisnl/filament-geometry/compare/0.2.0...0.2.2

## 0.2.0 - 2025-07-07

### What's Changed

#### Added

* add leaflet-gesture-handling by @pjotrvdh in https://github.com/swisnl/filament-geometry/pull/9
* add option to disable gesture handling
* add support for right click marker/vertex removal
* add method to set map options
* add method to set Geoman options

#### Changed

* set max height instead of aspect ratio
* center map in the middle of the boundaries

#### Removed

* drop PHP 8.1 support

#### Fixed

* properly disable self intersection for existing geometries
* properly disable boundaries

**Full Changelog**: https://github.com/swisnl/filament-geometry/compare/0.1.0...0.2.0

## 0.1.0 - 2025-07-01

* Initial release
