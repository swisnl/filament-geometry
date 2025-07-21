<?php

namespace Swis\Filament\Geometry;

final readonly class Bounds
{
    public function __construct(
        public float $southWestLat,
        public float $southWestLng,
        public float $northEastLat,
        public float $northEastLng
    ) {}

    public static function make(
        float $southWestLat,
        float $southWestLng,
        float $northEastLat,
        float $northEastLng
    ): self {
        return new self($southWestLat, $southWestLng, $northEastLat, $northEastLng);
    }

    /**
     * @return array{'sw': array{'lat': float, 'lng': float}, 'ne': array{'lat': float, 'lng': float}}
     */
    public function toArray(): array
    {
        return [
            'sw' => ['lat' => $this->southWestLat, 'lng' => $this->southWestLng],
            'ne' => ['lat' => $this->northEastLat, 'lng' => $this->northEastLng],
        ];
    }

    /**
     * @return array{'lat': float, 'lng': float}
     */
    public function center(): array
    {
        return [
            'lat' => ($this->southWestLat + $this->northEastLat) / 2.0,
            'lng' => ($this->southWestLng + $this->northEastLng) / 2.0,
        ];
    }
}
