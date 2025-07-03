<?php

namespace Swis\Filament\Geometry\Forms;

use Closure;
use Filament\Forms\Components\Field;
use Swis\Filament\Geometry\Contracts\TileLayer;
use Swis\Filament\Geometry\Enums\ControlPosition;
use Swis\Filament\Geometry\Enums\DrawMode;
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
    private array $mapConfig = [
        'bounds' => false,
        'markerColor' => '#3b82f6',
        'markerIconClassName' => '',
    ];

    private TileLayer $tileLayer;

    private ControlPosition $drawControlPosition = ControlPosition::TopLeft;

    /**
     * @var array<string, mixed>
     */
    private array $controls = [
        'attributionControl' => true,
        'doubleClickZoom' => 'center',
        'fullscreenControl' => true,
        'maxZoom' => 19,
        'minZoom' => 1,
        'scrollWheelZoom' => 'center',
        'touchZoom' => 'center',
        'center' => [0, 0],
        'zoom' => 15,
        'zoomControl' => true,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull()
            ->tileLayer(OpenStreetMap::make());
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
                'controls' => $this->controls,
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
            $this->mapConfig['boundaries'] = false;

            return $this;
        }

        $this->mapConfig['bounds']['sw'] = ['lat' => $southWestLat, 'lng' => $southWestLng];
        $this->mapConfig['bounds']['ne'] = ['lat' => $northEastLat, 'lng' => $northEastLng];

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
    public function markerColor(string $color): self
    {
        $this->mapConfig['markerColor'] = $color;

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
}
