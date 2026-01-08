<?php

declare(strict_types=1);

use Igeek\PdfService\Enums\Format;

it('has correct letter dimensions', function () {
    expect(Format::Letter->dimensions())->toBe([8.5, 11]);
});

it('has correct legal dimensions', function () {
    expect(Format::Legal->dimensions())->toBe([8.5, 14]);
});

it('has correct tabloid dimensions', function () {
    expect(Format::Tabloid->dimensions())->toBe([11, 17]);
});

it('has correct A0 dimensions', function () {
    expect(Format::A0->dimensions())->toBe([33.11, 46.81]);
});

it('has correct A1 dimensions', function () {
    expect(Format::A1->dimensions())->toBe([23.39, 33.11]);
});

it('has correct A2 dimensions', function () {
    expect(Format::A2->dimensions())->toBe([16.54, 23.39]);
});

it('has correct A3 dimensions', function () {
    expect(Format::A3->dimensions())->toBe([11.7, 16.54]);
});

it('has correct A4 dimensions', function () {
    expect(Format::A4->dimensions())->toBe([8.27, 11.69]);
});

it('has correct A5 dimensions', function () {
    expect(Format::A5->dimensions())->toBe([5.83, 8.27]);
});

it('has correct A6 dimensions', function () {
    expect(Format::A6->dimensions())->toBe([4.13, 5.83]);
});

it('can be created from lowercase string', function () {
    expect(Format::tryFrom('a4'))->toBe(Format::A4);
    expect(Format::tryFrom('letter'))->toBe(Format::Letter);
});

it('returns null for invalid format string', function () {
    expect(Format::tryFrom('invalid'))->toBeNull();
});
