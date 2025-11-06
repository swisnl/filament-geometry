<?php

namespace Swis\Filament\Geometry\GeoSearchProviders;

use Swis\Filament\Geometry\Contracts\GeoSearchProvider;

class Generic implements GeoSearchProvider
{
    private string $name;

    /**
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(string $name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * @param  array<string, mixed>  $options
     *
     * @see https://leaflet-geosearch.meijer.works/providers/openstreetmap for all available providers and their options
     */
    public static function make(string $name, array $options = []): self
    {
        return new self($name, $options);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function options(): array
    {
        return $this->options;
    }
}
