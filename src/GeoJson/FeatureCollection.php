<?php

namespace Swis\Filament\Geometry\GeoJson;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @phpstan-import-type FeatureArray from Feature
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class FeatureCollection implements Arrayable
{
    /**
     * @param  list<Feature>  $features
     */
    public function __construct(private array $features) {}

    /**
     * @param  iterable<Feature>  $features
     */
    public static function make(iterable $features): self
    {
        return new self(iterator_to_array($features, false));
    }

    /**
     * @return array{type: 'FeatureCollection', features: list<FeatureArray>}
     */
    public function toArray(): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => array_map(static fn (Feature $feature): array => $feature->toArray(), $this->features),
        ];
    }
}
