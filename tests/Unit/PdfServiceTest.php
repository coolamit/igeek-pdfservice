<?php

declare(strict_types=1);

use Igeek\PdfService\Enums\Format;
use Igeek\PdfService\Exceptions\InvalidCredentialsException;
use Igeek\PdfService\Exceptions\InvalidDiskException;
use Igeek\PdfService\Exceptions\InvalidFormatException;
use Igeek\PdfService\Exceptions\InvalidSizeException;
use Igeek\PdfService\PdfService;
use Igeek\PdfService\Support\Directives;

uses()->beforeEach(function () {
    $this->testService = new class('https://example.com', 'key') extends PdfService
    {
        public function testProcessHtml(string $html): string
        {
            return $this->processHtml($html);
        }

        public function testSanitizeFilename(string $filename): string
        {
            return $this->sanitizeFilename($filename);
        }
    };
});

it('can be instantiated with explicit url and key', function () {
    $service = new PdfService('https://example.com', 'my-api-key');

    expect($service)->toBeInstanceOf(PdfService::class);
});

it('throws exception when url is empty', function () {
    new PdfService('', 'my-api-key');
})->throws(
    InvalidCredentialsException::class,
    'Cannot instantiate PdfService without an API Key and API URL'
);

it('throws exception when key is empty', function () {
    new PdfService('https://example.com', '');
})->throws(
    InvalidCredentialsException::class,
    'Cannot instantiate PdfService without an API Key and API URL'
);

it('can be created via make() with explicit params', function () {
    $service = PdfService::make('https://custom.url', 'custom-key');

    expect($service)->toBeInstanceOf(PdfService::class);
});

it('can be created via make() with config fallback', function () {
    config(['pdfservice.url' => 'https://config.url']);
    config(['pdfservice.key' => 'config-key']);

    $service = PdfService::make();

    expect($service)->toBeInstanceOf(PdfService::class);
});

it('throws exception when config returns non-string values', function () {
    config(['pdfservice.url' => null]);
    config(['pdfservice.key' => 123]);

    PdfService::make();
})->throws(
    InvalidCredentialsException::class,
    'Cannot instantiate PdfService without an API Key and API URL'
);

it('sets format using Format enum', function () {
    $service = PdfService::make('https://example.com', 'key');

    $result = $service->format(Format::Letter);

    expect($result)->toBeInstanceOf(PdfService::class);
});

it('sets format using string', function () {
    $service = PdfService::make('https://example.com', 'key');

    $result = $service->format('legal');

    expect($result)->toBeInstanceOf(PdfService::class);
});

it('throws exception for invalid format string', function () {
    $service = PdfService::make('https://example.com', 'key');

    $service->format('invalid');
})->throws(
    InvalidFormatException::class,
    'Invalid format specified'
);

it('sets landscape orientation', function () {
    $service = PdfService::make('https://example.com', 'key');

    $result = $service->landscape();

    expect($result)->toBeInstanceOf(PdfService::class);
});

it('sets portrait orientation', function () {
    $service = PdfService::make('https://example.com', 'key');

    $result = $service->portrait();

    expect($result)->toBeInstanceOf(PdfService::class);
});

it('sets margins', function () {
    $service = PdfService::make('https://example.com', 'key');

    $result = $service->margins(1, 1, 1, 1);

    expect($result)->toBeInstanceOf(PdfService::class);
});

it('sets wait delay', function () {
    $service = PdfService::make('https://example.com', 'key');

    $result = $service->waitDelay('1s');

    expect($result)->toBeInstanceOf(PdfService::class);
});

it('sets name', function () {
    $service = PdfService::make('https://example.com', 'key');

    $result = $service->name('my-document.pdf');

    expect($result)->toBeInstanceOf(PdfService::class);
});

it('sets disk', function () {
    $service = PdfService::make('https://example.com', 'key');

    $result = $service->disk('local');

    expect($result)->toBeInstanceOf(PdfService::class);
});

it('throws exception for undefined disk', function () {
    $service = PdfService::make('https://example.com', 'key');

    $service->disk('non-existent-disk');
})->throws(
    InvalidDiskException::class,
    'Disk "non-existent-disk" is not defined in filesystems config'
);

it('sets custom size', function () {
    $service = PdfService::make('https://example.com', 'key');

    $result = $service->size([10, 12]);

    expect($result)->toBeInstanceOf(PdfService::class);
});

it('throws exception when size array has less than 2 values', function () {
    $service = PdfService::make('https://example.com', 'key');

    $service->size([10]);
})->throws(
    InvalidSizeException::class,
    'Size array must contain exactly 2 values [width, height]'
);

it('throws exception when size array has more than 2 values', function () {
    $service = PdfService::make('https://example.com', 'key');

    $service->size([10, 12, 14]);
})->throws(
    InvalidSizeException::class,
    'Size array must contain exactly 2 values [width, height]'
);

it('throws exception when size array is empty', function () {
    $service = PdfService::make('https://example.com', 'key');

    $service->size([]);
})->throws(
    InvalidSizeException::class,
    'Size array must contain exactly 2 values [width, height]'
);

it('throws exception when size values are not numeric', function () {
    $service = PdfService::make('https://example.com', 'key');

    $service->size(['invalid', 12]);
})->throws(
    InvalidSizeException::class,
    'Size values must be numeric'
);

it('throws exception when second size value is not numeric', function () {
    $service = PdfService::make('https://example.com', 'key');

    $service->size([10, 'invalid']);
})->throws(
    InvalidSizeException::class,
    'Size values must be numeric'
);

it('processes page number directive in html', function () {
    $result = $this->testService->testProcessHtml('<p>Page @pageNumber</p>');

    expect($result)->toBe('<p>Page '.Directives::PAGE_NUMBER.'</p>');
});

it('processes total pages directive in html', function () {
    $result = $this->testService->testProcessHtml('<p>Total: @totalPages</p>');

    expect($result)->toBe('<p>Total: '.Directives::TOTAL_PAGES.'</p>');
});

it('processes page break directive in html', function () {
    $result = $this->testService->testProcessHtml('<p>Before</p>@pageBreak<p>After</p>');

    expect($result)->toBe('<p>Before</p>'.Directives::PAGE_BREAK.'<p>After</p>');
});

it('supports fluent interface', function () {
    $service = PdfService::make('https://example.com', 'key')
        ->format(Format::A4)
        ->landscape()
        ->margins(1, 1, 1, 1)
        ->waitDelay('500ms')
        ->name('test.pdf')
        ->disk('local');

    expect($service)->toBeInstanceOf(PdfService::class);
});

it('sanitizes filename removing path traversal', function () {
    expect($this->testService->testSanitizeFilename('../../../etc/passwd'))
        ->toBe('passwd');

    expect($this->testService->testSanitizeFilename('/var/www/document.pdf'))
        ->toBe('document.pdf');
});

it('sanitizes filename removing dangerous characters', function () {
    expect($this->testService->testSanitizeFilename('doc<script>.pdf'))
        ->toBe('docscript.pdf');

    expect($this->testService->testSanitizeFilename('file"name.pdf'))
        ->toBe('filename.pdf');

    expect($this->testService->testSanitizeFilename("file\nname.pdf"))
        ->toBe('filename.pdf');
});

it('sanitizes filename preserving valid characters', function () {
    expect($this->testService->testSanitizeFilename('my-document_v2.pdf'))
        ->toBe('my-document_v2.pdf');

    expect($this->testService->testSanitizeFilename('Report 2024.pdf'))
        ->toBe('Report 2024.pdf');
});

it('sanitizes filename returning default for empty result', function () {
    expect($this->testService->testSanitizeFilename('###'))
        ->toBe('document.pdf');

    expect($this->testService->testSanitizeFilename(''))
        ->toBe('document.pdf');
});
