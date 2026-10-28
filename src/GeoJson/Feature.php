<?php

namespace Swis\Filament\Geometry\GeoJson;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Htmlable;

/**
 * @phpstan-type FeatureArray array{type: 'Feature', geometry: array<string, mixed>, properties: array{popup: string}|null}
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class Feature implements Arrayable
{
    /**
     * @param  Arrayable<string, mixed>|array<string, mixed>  $geometry
     */
    public function __construct(
        private Arrayable|array $geometry,
        private Htmlable|string|null $popup = null,
    ) {}

    /**
     * @param  Arrayable<string, mixed>|array<string, mixed>  $geometry  A geometry (e.g. a `matanyadaev/laravel-eloquent-spatial` object) or a GeoJSON geometry
     */
    public static function make(Arrayable|array $geometry): self
    {
        return new self($geometry);
    }

    /**
     * The content of the popup that opens when the feature is clicked. A string is escaped, an `Htmlable` (e.g. a view) is not.
     */
    public function popup(Htmlable|string|null $popup): self
    {
        return new self($this->geometry, $popup);
    }

    /**
     * @return FeatureArray
     */
    public function toArray(): array
    {
        return [
            'type' => 'Feature',
            'geometry' => $this->geometry instanceof Arrayable ? $this->geometry->toArray() : $this->geometry,
            'properties' => $this->popup === null ? null : [
                'popup' => $this->popup instanceof Htmlable ? $this->popup->toHtml() : e($this->popup),
            ],
        ];
    }
}
