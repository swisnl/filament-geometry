<?php

use Illuminate\Support\Facades\Blade;

it('renders a heading, a description and a footer', function () {
    $html = Blade::render(<<<'BLADE'
        <x-filament-geometry::popup heading="Leiden" description="Breestraat 103">
            <x-slot name="footer"><a href="/addresses/1">View</a></x-slot>
        </x-filament-geometry::popup>
        BLADE);

    expect($html)
        ->toMatch('/<h2[^>]*>\s*Leiden\s*<\/h2>/')
        ->toMatch('/<p[^>]*>\s*Breestraat 103\s*<\/p>/')
        ->toContain('<a href="/addresses/1">View</a>');
});

it('escapes a string description', function () {
    $html = Blade::render('<x-filament-geometry::popup :description="$description" />', [
        'description' => '<script>alert(1)</script>',
    ]);

    expect($html)
        ->not->toContain('<script>')
        ->toContain('&lt;script&gt;');
});

it('leaves out the heading, description and footer when they are empty', function () {
    $html = Blade::render('<x-filament-geometry::popup>Content</x-filament-geometry::popup>');

    expect($html)
        ->toContain('Content')
        ->not->toContain('<h2')
        ->not->toContain('<p')
        ->not->toContain('flex-wrap');
});
