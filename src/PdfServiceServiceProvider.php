<?php

declare(strict_types=1);

namespace Igeek\PdfService;

use Igeek\PdfService\Support\Directives;
use Illuminate\Support\Facades\Blade;
use Override;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PdfServiceServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('pdfservice')
            ->hasConfigFile();
    }

    #[Override]
    public function register(): void
    {
        parent::register();

        $this->app->singleton(PdfService::class, fn () => PdfService::make());
    }

    public function bootingPackage(): void
    {
        $this->registerBladeDirectives();
    }

    protected function registerBladeDirectives(): void
    {
        Blade::directive('pageNumber', fn () => Directives::PAGE_NUMBER);
        Blade::directive('totalPages', fn () => Directives::TOTAL_PAGES);
        Blade::directive('pageBreak', fn () => Directives::PAGE_BREAK);
        Blade::directive(
            'inlinedImage',
            fn (string $expression) => "<?php echo \\Igeek\\PdfService\\Support\\Directives::inlineImage({$expression}); ?>"
        );
    }
}
