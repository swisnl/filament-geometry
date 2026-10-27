<?php

namespace Swis\Filament\Geometry\Infolists;

use Filament\Infolists\Components\Entry;
use MatanYadaev\EloquentSpatial\Objects\Geometry as EloquentSpatialGeometry;
use Swis\Filament\Geometry\Concerns\HasMapOptions;
use Swis\Filament\Geometry\Icons\Marker;
use Swis\Filament\Geometry\TileLayers\OpenStreetMap as OpenStreetMapTileLayer;

class Geometry extends Entry
{
    use HasMapOptions;

    protected string $view = 'filament-geometry::infolists.geometry';

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull()
            ->tileLayer(OpenStreetMapTileLayer::make())
            ->markerIcon(Marker::make());
    }

    /**
     * @return array<string, mixed>
     */
    public function getMapConfig(): array
    {
        return [
            'value' => $this->getGeoJson(),
            'bounds' => $this->bounds?->toArray(),
            'map' => $this->mapOptions,
            'markerIcon' => $this->markerIcon->options(),
            'tileLayer' => [
                'url' => $this->tileLayer->url(),
                'options' => $this->tileLayer->options(),
            ],
        ];
    }

    /**
     * Unlike the form field, this reads already-resolved display state, so it can't reuse the form's StateCast classes.
     *
     * @return array<string, mixed>|null
     */
    private function getGeoJson(): ?array
    {
        $state = $this->getState();

        $geoJson = match (true) {
            $state instanceof EloquentSpatialGeometry => $state->toArray(),
            is_string($state) => json_decode($state, true),
            is_array($state) => $state,
            is_object($state) => json_decode(json_encode($state) ?: '', true),
            default => null,
        };

        return is_array($geoJson) && filled($geoJson) ? $geoJson : null;
    }
}
