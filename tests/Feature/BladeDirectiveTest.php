<?php

declare(strict_types=1);

use Igeek\PdfService\Support\Directives;
use Illuminate\Support\Facades\Blade;

it('compiles page number directive', function () {
    $compiled = Blade::compileString('@pageNumber');

    expect($compiled)->toBe(Directives::PAGE_NUMBER);
});

it('compiles total pages directive', function () {
    $compiled = Blade::compileString('@totalPages');

    expect($compiled)->toBe(Directives::TOTAL_PAGES);
});

it('compiles page break directive', function () {
    $compiled = Blade::compileString('@pageBreak');

    expect($compiled)->toBe(Directives::PAGE_BREAK);
});

it('compiles inlined image directive', function () {
    $compiled = Blade::compileString("@inlinedImage('path/to/image.png')");

    expect($compiled)->toContain('Directives::inlineImage');
    expect($compiled)->toContain("'path/to/image.png'");
});

it('compiles inlined image directive with double quotes', function () {
    $compiled = Blade::compileString('@inlinedImage("path/to/image.png")');

    expect($compiled)->toContain('Directives::inlineImage');
    expect($compiled)->toContain('"path/to/image.png"');
});

it('publishes config file', function () {
    $this->artisan('vendor:publish', ['--tag' => 'pdfservice-config'])
        ->assertSuccessful();
});
