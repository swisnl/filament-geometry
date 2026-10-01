<?php

namespace Swis\Filament\Geometry\Concerns;

use Closure;
use Swis\Filament\Geometry\Contracts\Bounds;
use Swis\Filament\Geometry\Contracts\Icon;
use Swis\Filament\Geometry\Contracts\TileLayer;

trait HasMapOptions
{
    private ?Bounds $bounds = null;

    private TileLayer $tileLayer;

    private Icon $markerIcon;

    /**
     * @var array<string, mixed>
     */
    private array $mapOptions = [
        'attributionControl' => true,
        'fullscreenControl' => true,
        'gestureHandling' => true,
        'maxZoom' => 19,
        'minZoom' => 1,
        'center' => [0, 0],
        'zoom' => 15,
        'zoomControl' => true,
    ];

    /**
     * Prevents the map from panning outside the defined box, and sets
     * a default location in the center of the box. It makes sense to
     * use this with a minimum zoom that suits the size of your map and
     * the size of the box or the way it pans back to the bounding box
     * looks strange. You can call with null to undo this.
     *
     * @return $this
     */
    public function bounds(Closure|Bounds|null $bounds): self
    {
        $this->bounds = $this->evaluate($bounds);

        if ($this->bounds) {
            $center = $this->bounds->center();
            $this->center($center['lat'], $center['lng']);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function center(float $lat, float $lng): self
    {
        $this->mapOptions['center'] = [$lat, $lng];

        return $this;
    }

    /**
     * @return $this
     */
    public function zoom(int $zoom): self
    {
        $this->mapOptions['zoom'] = $zoom;

        return $this;
    }

    /**
     * @return $this
     */
    public function maxZoom(int $maxZoom): self
    {
        $this->mapOptions['maxZoom'] = $maxZoom;

        return $this;
    }

    /**
     * @return $this
     */
    public function minZoom(int $minZoom): self
    {
        $this->mapOptions['minZoom'] = $minZoom;

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
        $this->mapOptions['zoomControl'] = $this->evaluate($show);

        return $this;
    }

    /**
     * @return $this
     */
    public function showFullscreenControl(Closure|bool $show = true): self
    {
        $this->mapOptions['fullscreenControl'] = $this->evaluate($show);

        return $this;
    }

    /**
     * @return $this
     */
    public function showAttributionControl(Closure|bool $show = true): self
    {
        $this->mapOptions['attributionControl'] = $this->evaluate($show);

        return $this;
    }

    /**
     * @return $this
     */
    public function useGestureHandling(Closure|bool $show = true): self
    {
        $this->mapOptions['gestureHandling'] = $this->evaluate($show);

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
     * Set extra map options. Please note, this will be merged with the existing options!
     *
     * @see https://leafletjs.com/reference.html#map-option for all available options
     *
     * @param  array<string, mixed>  $mapOptions
     * @return $this
     */
    public function mapOptions(array $mapOptions): self
    {
        $this->mapOptions = array_merge($this->mapOptions, $mapOptions);

        return $this;
    }
}
