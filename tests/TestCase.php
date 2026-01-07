<?php

declare(strict_types=1);

namespace Igeek\PdfService\Tests;

use Igeek\PdfService\PdfServiceServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PdfServiceServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('pdfservice.url', 'https://test.gotenberg.local');
        $app['config']->set('pdfservice.key', 'test-api-key');
    }
}
