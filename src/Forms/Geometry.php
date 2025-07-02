<?php

namespace Swis\Filament\Geometry\Forms;

use Closure;
use Filament\Forms\Components\Field;
use Swis\Filament\Geometry\Enums\DrawMode;

class Geometry extends Field
{
    protected string $view = 'filament-geometry::forms.geometry';

    /**
     * @var array<int, \Swis\Filament\Geometry\Enums\DrawMode>
     */
    private array $drawModes = [];

    /**
     * @var array<string, mixed>
     */
    private array $mapConfig = [
        'bounds' => false,
        'detectRetina' => false,
        'geoMan' => [
            'position' => 'topleft',
        ],
        'markerColor' => '#3b82f6',
        'markerIconClassName' => '',
        'maxZoom' => 28,
        'minZoom' => 0,
        'statePath' => '',
        'tileSize' => 256,
        'tilesUrl' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
        'zoom' => 15,
        'zoomOffset' => 0,
    ];

    /**
     * @var array<string, mixed>
     */
    private array $controls = [
        'attributionControl' => false,
        'doubleClickZoom' => 'center',
        'fullscreenControl' => true,
        'maxZoom' => 28,
        'minZoom' => 1,
        'scrollWheelZoom' => 'center',
        'touchZoom' => 'center',
        'zoom' => 15,
        'zoomControl' => true,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpanFull();
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

        $this->mapConfig['lang']['warning']['limit'] = __('filament-geometry::geometry.warning.limit');
        // Build config: key = DrawMode value, value = bool (selected)
        foreach (DrawMode::cases() as $mode) {
            $this->mapConfig['geoMan']['draw'.$mode->name] = in_array($mode, $this->drawModes, true);
            $this->mapConfig['geoMan']['edit'.$mode->name] = in_array($mode, $this->drawModes, true);
        }

        return json_encode(
            array_merge($this->mapConfig, [
                'statePath' => $statePath,
                'controls' => $this->controls,
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
    public function tilesUrl(string $url): self
    {
        $this->mapConfig['tilesUrl'] = $url;

        return $this;
    }

    /**
     * @return $this
     */
    public function detectRetina(Closure|bool $detectRetina = true): self
    {
        $this->mapConfig['detectRetina'] = $this->evaluate($detectRetina);

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
    public function markerColor(string $color): self
    {
        $this->mapConfig['markerColor'] = $color;

        return $this;
    }

    /**
     * @return $this
     *
     * @note Valid values: 'topleft', 'topright', 'bottomleft', 'bottomright'
     */
    public function geoManPosition(string $position = 'topleft'): self
    {
        if (in_array($position, ['topleft', 'topright', 'bottomleft', 'bottomright'], true)) {
            $this->mapConfig['geoMan']['position'] = $position;
        }

        return $this;
    }
}
