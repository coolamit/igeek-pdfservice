<?php

declare(strict_types=1);

namespace Igeek\PdfService\Facades;

use Igeek\PdfService\Exceptions\InvalidCredentialsException;
use Igeek\PdfService\PdfService;
use Illuminate\Support\Facades\Facade;

/**
 * @see PdfService
 */
class Pdf extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PdfService::class;
    }

    /**
     * Create a new PdfService instance with custom API credentials.
     * Bypasses the singleton to allow different credentials per call.
     *
     * @throws InvalidCredentialsException
     */
    public static function using(string $apiUrl, string $apiKey): PdfService
    {
        return PdfService::make($apiUrl, $apiKey);
    }
}
