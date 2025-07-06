<?php

namespace Swis\Filament\Geometry\Forms;

use Closure;
use Filament\Forms\Components\Field;
use Swis\Filament\Geometry\Contracts\Icon;
use Swis\Filament\Geometry\Contracts\TileLayer;
use Swis\Filament\Geometry\Enums\ControlPosition;
use Swis\Filament\Geometry\Enums\DrawMode;
use Swis\Filament\Geometry\Icons\Marker;
use Swis\Filament\Geometry\TileLayers\OpenStreetMap;

class Geometry extends Field
{
    protected string $view = 'filament-geometry::forms.geometry';

    /**
     * @var array<int, \Swis\Filament\Geometry\Enums\DrawMode>
     */
    private array $drawModes = [
        DrawMode::Marker,
        DrawMode::Polygon,
        DrawMode::Polyline,
        DrawMode::Rectangle,
    ];

    /**
     * @var array<string, mixed>
     */
    private array $mapConfig = [];

    /**
     * @var ?array{'sw': array{'lat': int|float, 'lng': int|float}, 'ne': array{'lat': int|float, 'lng': int|float}}
     */
    private ?array $bounds = null;

    private TileLayer $tileLayer;

    private ControlPosition $drawControlPosition = ControlPosition::TopLeft;

    private Icon $markerIcon;

    private string $locale = 'en';

    /**
     * @var array<string, mixed>
     */
    private array $controls = [
        'attributionControl' => true,
        'doubleClickZoom' => 'center',
        'fullscreenControl' => true,
        'gestureHandling' => true,
        'maxZoom' => 19,
        'minZoom' => 1,
        'center' => [0, 0],
        'zoom' => 15,
        'zoomControl' => true,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull()
            ->tileLayer(OpenStreetMap::make())
            ->markerIcon(Marker::make())
            ->locale(config('app.locale', $this->locale));
    }

    /**
     * @param  \Swis\Filament\Geometry\Enums\DrawMode[]  $drawModes
     */
    public function drawModes(array $drawModes = []): self
    {
        foreach ($drawModes as $mode) {
            assert($mode instanceof DrawMode, 'Each drawMode must be an instance of DrawMode enum');
        }
        $this->drawModes = $drawModes;

        return $this;
    }

    /**
     * Create json configuration string
     */
    public function getMapConfig(): string
    {
        $statePath = $this->getStatePath();
        $config = $this->mapConfig;

        $config['lang']['warning']['limit'] = __('filament-geometry::geometry.warning.limit');
        // Build config: key = DrawMode value, value = bool (selected)
        foreach (DrawMode::cases() as $mode) {
            $config['geoMan']['draw'.$mode->name] = in_array($mode, $this->drawModes, true);
            $config['geoMan']['edit'.$mode->name] = in_array($mode, $this->drawModes, true);
        }
        $config['geoMan']['position'] = $this->drawControlPosition->value;

        return json_encode(
            array_merge($config, [
                'statePath' => $statePath,
                'bounds' => $this->bounds,
                'controls' => $this->controls,
                'locale' => $this->locale,
                'markerIcon' => $this->markerIcon->options(),
                'tileLayer' => [
                    'url' => $this->tileLayer->url(),
                    'options' => $this->tileLayer->options(),
                ],
            ])
        );
    }

    /**
     * Prevents the map from panning outside the defined box, and sets
     * a default location in the center of the box. It makes sense to
     * use this with a minimum zoom that suits the size of your map and
     * the size of the box or the way it pans back to the bounding box
     * looks strange. You can call with $on set to false to undo this.
     */
    public function boundaries(Closure|bool $on, int|float $southWestLat = 0, int|float $southWestLng = 0, int|float $northEastLat = 0, int|float $northEastLng = 0): self
    {
        if (! $this->evaluate($on)) {
            $this->bounds = null;

            return $this;
        }

        $this->bounds = [
            'sw' => ['lat' => $southWestLat, 'lng' => $southWestLng],
            'ne' => ['lat' => $northEastLat, 'lng' => $northEastLng],
        ];
        $this->center(($southWestLat + $northEastLat) / 2.0, ($southWestLng + $northEastLng) / 2.0);

        return $this;
    }

    /**
     * @return $this
     */
    public function center(float $lat, float $lng): self
    {
        $this->controls['center'] = [$lat, $lng];

        return $this;
    }

    /**
     * @return $this
     */
    public function zoom(int $zoom): self
    {
        $this->controls['zoom'] = $zoom;

        return $this;
    }

    /**
     * @return $this
     */
    public function maxZoom(int $maxZoom): self
    {
        $this->controls['maxZoom'] = $maxZoom;

        return $this;
    }

    /**
     * @return $this
     */
    public function minZoom(int $minZoom): self
    {
        $this->controls['minZoom'] = $minZoom;

        return $this;
    }

    /**
     * @return $this
     */
    public function tileLayer(TileLayer $tileLayer): self
    {
        $this->tileLayer = $tileLayer;

        return $this;
    }

    /**
     * @return $this
     */
    public function showZoomControl(Closure|bool $show = true): self
    {
        $this->controls['zoomControl'] = $this->evaluate($show);

        return $this;
    }

    /**
     * @return $this
     */
    public function showFullscreenControl(Closure|bool $show = true): self
    {
        $this->controls['fullscreenControl'] = $this->evaluate($show);

        return $this;
    }

    /**
     * @return $this
     */
    public function showAttributionControl(Closure|bool $show = true): self
    {
        $this->controls['attributionControl'] = $this->evaluate($show);

        return $this;
    }

    /**
     * @return $this
     */
    public function markerIcon(Icon $icon): self
    {
        $this->markerIcon = $icon;

        return $this;
    }

    /**
     * @return $this
     */
    public function drawControlPosition(ControlPosition $position): self
    {
        $this->drawControlPosition = $position;

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
}
