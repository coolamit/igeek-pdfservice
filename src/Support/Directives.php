<?php

declare(strict_types=1);

namespace Igeek\PdfService\Support;

/**
 * Blade directive output definitions.
 * Single source of truth for both Blade directives and processHtml().
 */
class Directives
{
    public const string PAGE_NUMBER = '<span class="pageNumber"></span>';

    public const string TOTAL_PAGES = '<span class="totalPages"></span>';

    public const string PAGE_BREAK = '<div style="page-break-after: always;"></div>';

    public static function inlineImage(string $path): string
    {
        return ImageHelper::inline($path);
    }
}
