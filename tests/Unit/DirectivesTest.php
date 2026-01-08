<?php

declare(strict_types=1);

use Igeek\PdfService\Support\Directives;

it('has correct page number constant', function () {
    expect(Directives::PAGE_NUMBER)->toBe('<span class="pageNumber"></span>');
});

it('has correct total pages constant', function () {
    expect(Directives::TOTAL_PAGES)->toBe('<span class="totalPages"></span>');
});

it('has correct page break constant', function () {
    expect(Directives::PAGE_BREAK)->toBe('<div style="page-break-after: always;"></div>');
});
