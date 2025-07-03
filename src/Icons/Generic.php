<?php

namespace Swis\Filament\Geometry\Icons;

use Swis\Filament\Geometry\Contracts\Icon;

final class Generic implements Icon
{
    /**
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public static function make(array $options = []): self
    {
        return new self($options);
    }

    public function options(): array
    {
        return $this->options;
    }
}
