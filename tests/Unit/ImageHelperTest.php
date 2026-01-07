<?php

declare(strict_types=1);

use Igeek\PdfService\Support\ImageHelper;
use Illuminate\Support\Facades\Storage;

it('returns empty string for non-existent storage path', function () {
    Storage::fake('local');

    $result = ImageHelper::inline('non-existent/image.png');

    expect($result)->toBe('');
});

it('returns base64 image for valid storage path', function () {
    Storage::fake('local');
    $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    Storage::put('test-image.png', $imageContent);

    $result = ImageHelper::inline('test-image.png');

    expect($result)->toContain('<img src="data:');
    expect($result)->toContain(';base64,');
});

it('returns img tag with URL for external URLs - no server-side fetch', function () {
    $result = ImageHelper::inline('https://example.com/image.png');

    expect($result)->toBe('<img src="https://example.com/image.png" />');
});

it('escapes special characters in external URLs', function () {
    $result = ImageHelper::inline('https://example.com/image.png?foo=bar&baz=qux');

    expect($result)->toBe('<img src="https://example.com/image.png?foo=bar&amp;baz=qux" />');
});

it('handles URLs with various protocols', function () {
    $httpsResult = ImageHelper::inline('https://example.com/image.png');
    $httpResult = ImageHelper::inline('http://example.com/image.png');

    expect($httpsResult)->toBe('<img src="https://example.com/image.png" />');
    expect($httpResult)->toBe('<img src="http://example.com/image.png" />');
});

it('detects mime type from storage file', function () {
    Storage::fake('local');

    $pngContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    Storage::put('test.png', $pngContent);

    $result = ImageHelper::inline('test.png');

    expect($result)->toContain('data:image/png;base64,');
});
