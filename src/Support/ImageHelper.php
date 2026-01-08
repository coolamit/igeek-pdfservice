<?php

declare(strict_types=1);

namespace Igeek\PdfService\Support;

use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Helper class for inlining images.
 */
class ImageHelper
{
    /**
     * Convert an image path or URL to an img tag.
     * Returns empty string on failure (graceful degradation).
     */
    public static function inline(string $path): string
    {
        if (Str::isUrl($path)) {
            return sprintf('<img src="%s" />', e($path));
        }

        return static::inlineFromStorage($path);
    }

    /**
     * Inline an image from Laravel storage as base64.
     */
    protected static function inlineFromStorage(string $path): string
    {
        try {
            if (! Storage::exists($path)) {
                return '';
            }

            $imageContent = Storage::get($path);

            if ($imageContent === null) {
                return '';
            }

            $mimeType = Storage::mimeType($path);

            return static::createBase64ImgTag($imageContent, $mimeType ?: 'image/png');
        } catch (Exception) {
            return '';
        }
    }

    /**
     * Create a base64-encoded img tag.
     */
    protected static function createBase64ImgTag(string $content, string $mimeType): string
    {
        $base64 = base64_encode($content);

        return sprintf('<img src="data:%s;base64,%s" />', $mimeType, $base64);
    }
}
