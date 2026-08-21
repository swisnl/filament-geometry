<?php

namespace Swis\Filament\Geometry\Forms;

use Filament\Forms\Components\Field;
use Filament\Schemas\Components\StateCasts\Contracts\StateCast;
use MatanYadaev\EloquentSpatial\Objects\Geometry as EloquentSpatialGeometry;
use MatanYadaev\EloquentSpatial\Objects\GeometryCollection;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\MultiLineString;
use MatanYadaev\EloquentSpatial\Objects\MultiPoint;
use MatanYadaev\EloquentSpatial\Objects\MultiPolygon;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Swis\Filament\Geometry\Concerns\HasMapOptions;
use Swis\Filament\Geometry\Contracts\GeoSearchProvider;
use Swis\Filament\Geometry\Enums\ControlPosition;
use Swis\Filament\Geometry\Enums\DrawMode;
use Swis\Filament\Geometry\GeoSearchProviders\OpenStreetMap as OpenStreetMapGeoSearchProvider;
use Swis\Filament\Geometry\Icons\Marker;
use Swis\Filament\Geometry\StateCasts\ArrayStateCast;
use Swis\Filament\Geometry\StateCasts\EloquentSpatialStateCast;
use Swis\Filament\Geometry\StateCasts\ObjectStateCast;
use Swis\Filament\Geometry\StateCasts\StringStateCast;
use Swis\Filament\Geometry\TileLayers\OpenStreetMap as OpenStreetMapTileLayer;

class Geometry extends Field
{
    use HasMapOptions;

    protected const CAST_TYPES_STRING = [
        'string',
        'encrypted',
    ];

    protected const CAST_TYPES_ARRAY = [
        'array',
        'encrypted:array',
    ];

    protected const CAST_TYPES_OBJECT = [
        'object',
        'encrypted:object',
    ];

    protected const CAST_TYPES_ELOQUENT_SPATIAL = [
        Point::class,
        MultiPoint::class,
        LineString::class,
        MultiLineString::class,
        Polygon::class,
        MultiPolygon::class,
        EloquentSpatialGeometry::class,
        GeometryCollection::class,
    ];

    protected string $view = 'filament-geometry::forms.geometry';

    private string $locale = 'en';

    private bool $multipart = false;

    private ?GeoSearchProvider $geoSearchProvider = null;

    /**
     * @var array<string, mixed>
     */
    private array $geoSearchOptions = [
        'style' => 'bar',
        'showMarker' => false,
    ];

    /**
     * @var array<string, mixed>
     */
    private array $geomanOptions = [
        'customControls' => false,
        'cutPolygon' => false,
        'dragMode' => false,
        'drawCircle' => false,
        'drawCircleMarker' => false,
        'drawControls' => true,
        'drawMarker' => true,
        'drawPolygon' => true,
        'drawPolyline' => true,
        'drawRectangle' => true,
        'drawText' => false,
        'editControls' => true,
        'editMode' => false,
        'oneBlock' => true,
        'optionsControls' => false,
        'position' => ControlPosition::TopLeft->value,
        'removalMode' => true,
        'rotateMode' => false,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull()
            ->tileLayer(OpenStreetMapTileLayer::make())
            ->geoSearch(OpenStreetMapGeoSearchProvider::make())
            ->markerIcon(Marker::make())
            ->locale(config('app.locale', $this->locale));
    }

    /**
     * @return array<StateCast>
     */
    public function getDefaultStateCasts(): array
    {
        if ($this->hasCustomStateCasts() || ! $model = $this->getModelInstance()) {
            return parent::getDefaultStateCasts();
        }

        $cast = match (true) {
            $model->hasCast($this->getName(), self::CAST_TYPES_STRING) => StringStateCast::class,
            $model->hasCast($this->getName(), self::CAST_TYPES_ARRAY) => ArrayStateCast::class,
            $model->hasCast($this->getName(), self::CAST_TYPES_OBJECT) => ObjectStateCast::class,
            $model->hasCast($this->getName(), self::CAST_TYPES_ELOQUENT_SPATIAL) => EloquentSpatialStateCast::class,
            default => StringStateCast::class,
        };

        return [app($cast)];
    }

    public function asString(): self
    {
        return $this->stateCast(app(StringStateCast::class));
    }

    public function asArray(): self
    {
        return $this->stateCast(app(ArrayStateCast::class));
    }

    public function asObject(): self
    {
        return $this->stateCast(app(ObjectStateCast::class));
    }

    public function asEloquentSpatial(): self
    {
        return $this->stateCast(app(EloquentSpatialStateCast::class));
    }

    /**
     * @param  DrawMode[]  $drawModes
     */
    public function drawModes(array $drawModes = []): self
    {
        foreach ($drawModes as $mode) {
            assert($mode instanceof DrawMode, 'Each drawMode must be an instance of DrawMode enum');
        }

        foreach (DrawMode::cases() as $mode) {
            $this->geomanOptions['draw'.$mode->name] = in_array($mode, $drawModes, true);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function multipart(bool $multipart = true): self
    {
        $this->multipart = $multipart;

        return $this;
    }

    /**
     * Create configuration array
     *
     * @return array<string, mixed>
     */
    public function getMapConfig(): array
    {
        return [
            'statePath' => $this->getStatePath(),
            'lang' => trans('filament-geometry::geometry'),
            'bounds' => $this->bounds?->toArray(),
            'map' => $this->mapOptions,
            'geoSearch' => [
                ...$this->geoSearchOptions,
                'provider' => $this->geoSearchProvider ? [
                    'name' => $this->geoSearchProvider->name(),
                    'options' => $this->geoSearchProvider->options(),
                ] : null,
            ],
            'geoman' => $this->geomanOptions,
            'multipart' => $this->multipart,
            'locale' => $this->locale,
            'markerIcon' => $this->markerIcon->options(),
            'tileLayer' => [
                'url' => $this->tileLayer->url(),
                'options' => $this->tileLayer->options(),
            ],
        ];
    }

    /**
     * @return $this
     */
    public function geoSearch(?GeoSearchProvider $provider): self
    {
        $this->geoSearchProvider = $provider;

        return $this;
    }

    /**
     * @return $this
     */
    public function drawControlPosition(ControlPosition $position): self
    {
        $this->geomanOptions['position'] = $position->value;

        return $this;
    }

    /**
     * @return $this
     */
    public function locale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Set extra Geoman options. Please note, this will be merged with the existing options!
     *
     * @see https://geoman.io/docs/leaflet/toolbar for all available options
     *
     * @param  array<string, mixed>  $geomanOptions
     * @return $this
     */
    public function geomanOptions(array $geomanOptions): self
    {
        $this->geomanOptions = array_merge($this->geomanOptions, $geomanOptions);

        return $this;
    }

    /**
     * Set extra GeoSearch options. Please note, this will be merged with the existing options!
     *
     * @see https://leaflet-geosearch.meijer.works/usage for all available options
     *
     * @param  array<string, mixed>  $geoSearchOptions
     * @return $this
     */
    public function geoSearchOptions(array $geoSearchOptions): self
    {
        $this->geoSearchOptions = array_merge($this->geoSearchOptions, $geoSearchOptions);

        return $this;
    }
}
