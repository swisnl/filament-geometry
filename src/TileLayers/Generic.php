<?php

namespace Swis\Filament\Geometry\TileLayers;

use Swis\Filament\Geometry\Contracts\TileLayer;

final class Generic implements TileLayer
{
    private string $url;

    /**
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(string $url, array $options = [])
    {
        $this->url = $url;
        $this->options = $options;
    }

    /**
     * @param  array<string, mixed>  $options
     *
     * @see https://leafletjs.com/reference.html#tilelayer-option for all available options
     */
    public static function make(string $url, array $options = []): self
    {
        return new self($url, $options);
    }

    public function url(): string
    {
        return $this->url;
    }

    public function options(): array
    {
        return $this->options;
    }
}
