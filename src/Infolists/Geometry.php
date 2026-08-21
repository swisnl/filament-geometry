<?php

namespace Swis\Filament\Geometry\Infolists;

use Filament\Infolists\Components\Entry;
use MatanYadaev\EloquentSpatial\Objects\Geometry as EloquentSpatialGeometry;
use Swis\Filament\Geometry\Concerns\HasMapOptions;
use Swis\Filament\Geometry\Icons\Marker;
use Swis\Filament\Geometry\StateCasts\ArrayStateCast;
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
     * Create configuration array
     *
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
     * Normalizes the resolved state into a plain GeoJSON geometry array,
     * regardless of the shape the model attribute cast resolves it to.
     *
     * Unlike the form field, this reads already-resolved display state
     * rather than the raw pre-cast model attribute, so it can't delegate
     * to the form's StateCast classes directly; it shares only the
     * blank-check-then-decode step with ArrayStateCast::decode().
     *
     * @return array<string, mixed>|null
     */
    private function getGeoJson(): ?array
    {
        $state = $this->getState();

        try {
            $geoJson = match (true) {
                $state === null => null,
                $state instanceof EloquentSpatialGeometry => $state->toArray(),
                is_string($state) => ArrayStateCast::decode($state),
                is_array($state) => $state,
                is_object($state) => ArrayStateCast::decode(json_encode($state, JSON_THROW_ON_ERROR)),
                default => null,
            };
        } catch (\JsonException) {
            return null;
        }

        return blank($geoJson) ? null : $geoJson;
    }
}
