<?php

declare(strict_types=1);

namespace Igeek\PdfService\Enums;

/**
 * Page format enum with dimensions in inches [width, height]
 */
enum Format: string
{
    case Letter = 'letter';
    case Legal = 'legal';
    case Tabloid = 'tabloid';
    case A0 = 'a0';
    case A1 = 'a1';
    case A2 = 'a2';
    case A3 = 'a3';
    case A4 = 'a4';
    case A5 = 'a5';
    case A6 = 'a6';

    /**
     * Get dimensions in inches [width, height]
     *
     * @return array{0: float, 1: float}
     */
    public function dimensions(): array
    {
        return match ($this) {
            self::Letter => [8.5, 11],
            self::Legal => [8.5, 14],
            self::Tabloid => [11, 17],
            self::A0 => [33.11, 46.81],
            self::A1 => [23.39, 33.11],
            self::A2 => [16.54, 23.39],
            self::A3 => [11.7, 16.54],
            self::A4 => [8.27, 11.69],
            self::A5 => [5.83, 8.27],
            self::A6 => [4.13, 5.83],
        };
    }
}
