<?php

declare(strict_types=1);

use Igeek\PdfService\Facades\Pdf;
use Igeek\PdfService\PdfService;

it('creates PdfService instance with custom credentials via using()', function () {
    $service = Pdf::using('https://custom-api.com', 'custom-key');

    expect($service)->toBeInstanceOf(PdfService::class);
});
