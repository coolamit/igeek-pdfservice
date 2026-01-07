<?php

declare(strict_types=1);

namespace Igeek\PdfService;

use Gotenberg\Exceptions\GotenbergApiErrored;
use Gotenberg\Gotenberg;
use Gotenberg\Stream;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Igeek\PdfService\Enums\Format;
use Igeek\PdfService\Exceptions\InvalidContentException;
use Igeek\PdfService\Exceptions\InvalidCredentialsException;
use Igeek\PdfService\Exceptions\InvalidDiskException;
use Igeek\PdfService\Exceptions\InvalidFormatException;
use Igeek\PdfService\Exceptions\InvalidSizeException;
use Igeek\PdfService\Support\Directives;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ValueError;

class PdfService
{
    protected bool $landscape = false;

    /** @var array{0: float, 1: float} */
    protected array $size;

    protected ?string $headerHtml = null;

    protected ?string $footerHtml = null;

    protected ?string $contentHtml = null;

    protected float $marginTop = 0.4;

    protected float $marginRight = 0.4;

    protected float $marginBottom = 0.4;

    protected float $marginLeft = 0.4;

    protected bool $marginsExplicitlySet = false;

    protected ?string $name = null;

    protected ?string $disk = null;

    protected string $waitDelay = '500ms';

    /**
     * @throws InvalidCredentialsException
     */
    public function __construct(
        protected readonly string $apiUrl,
        protected readonly string $apiKey,
    ) {
        if (empty($this->apiKey) || empty($this->apiUrl)) {
            throw new InvalidCredentialsException('Cannot instantiate PdfService without an API Key and API URL');
        }

        $this->size = Format::A4->dimensions();
    }

    /**
     * Create a new PdfService instance.
     * Uses config values as fallback when parameters are not provided.
     *
     * @throws InvalidCredentialsException
     */
    public static function make(?string $apiUrl = null, ?string $apiKey = null): self
    {
        $apiKeyToUse = $apiKey ?? config('pdfservice.key', '');
        $apiUrlToUse = $apiUrl ?? config('pdfservice.url', '');

        if (! is_string($apiKeyToUse) || ! is_string($apiUrlToUse)) {
            throw new InvalidCredentialsException('Cannot instantiate PdfService without an API Key and API URL');
        }

        return new self($apiUrlToUse, $apiKeyToUse);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return $this
     */
    public function view(string $view, array $data = []): static
    {
        $this->contentHtml = $this->renderView($view, $data);

        return $this;
    }

    /**
     * @return $this
     */
    public function html(string $html): static
    {
        $this->contentHtml = $this->processHtml($html);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return $this
     */
    public function headerView(string $view, array $data = []): static
    {
        $this->headerHtml = $this->renderView($view, $data);

        return $this;
    }

    /**
     * @return $this
     */
    public function headerHtml(string $html): static
    {
        $this->headerHtml = $this->processHtml($html);

        return $this;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return $this
     */
    public function footerView(string $view, array $data = []): static
    {
        $this->footerHtml = $this->renderView($view, $data);

        return $this;
    }

    /**
     * @return $this
     */
    public function footerHtml(string $html): static
    {
        $this->footerHtml = $this->processHtml($html);

        return $this;
    }

    /**
     * Set page size using raw dimensions [width, height] in inches.
     *
     * @param  array<int, mixed>  $size
     *
     * @throws InvalidSizeException
     */
    public function size(array $size): static
    {
        if (count($size) !== 2) {
            throw new InvalidSizeException('Size array must contain exactly 2 values [width, height]');
        }

        if (! is_numeric($size[0]) || ! is_numeric($size[1])) {
            throw new InvalidSizeException('Size values must be numeric');
        }

        $this->size = array_map(fn ($v) => (float) $v, $size);  // @phpstan-ignore-line

        return $this;
    }

    /**
     * Set page format using Format enum or string.
     *
     * @throws InvalidFormatException
     */
    public function format(Format|string $format): static
    {
        if (is_string($format)) {
            try {
                $format = Format::from($format);
            } catch (ValueError) {
                throw new InvalidFormatException('Invalid format specified');
            }
        }

        $this->size = $format->dimensions();

        return $this;
    }

    /**
     * @return $this
     */
    public function portrait(): static
    {
        $this->landscape = false;

        return $this;
    }

    /**
     * @return $this
     */
    public function landscape(): static
    {
        $this->landscape = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function margins(float $top, float $right, float $bottom, float $left): static
    {
        $this->marginTop = $top;
        $this->marginRight = $right;
        $this->marginBottom = $bottom;
        $this->marginLeft = $left;
        $this->marginsExplicitlySet = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function waitDelay(string $delay): static
    {
        $this->waitDelay = $delay;

        return $this;
    }

    /**
     * @return $this
     */
    public function name(string $name): static
    {
        $this->name = $this->sanitizeFilename($name);

        return $this;
    }

    /**
     * @return $this
     *
     * @throws InvalidDiskException
     */
    public function disk(string $disk): static
    {
        if (empty($disk) || config(sprintf('filesystems.disks.%s', $disk)) === null) {
            throw new InvalidDiskException(sprintf('Disk "%s" is not defined in filesystems config', $disk));
        }

        $this->disk = $disk;

        return $this;
    }

    /**
     * @throws GotenbergApiErrored
     * @throws InvalidContentException
     */
    public function save(string $path): bool
    {
        $pdf = $this->generate();

        $disk = $this->disk ? Storage::disk($this->disk) : Storage::disk();

        return (bool) $disk->put($path, $pdf);
    }

    /**
     * @throws GotenbergApiErrored
     * @throws InvalidContentException
     */
    public function content(): string
    {
        return $this->generate();
    }

    /**
     * @throws GotenbergApiErrored
     * @throws InvalidContentException
     */
    public function download(?string $filename = null): StreamedResponse
    {
        $pdf = $this->generate();
        $filename = $this->sanitizeFilename($filename ?? $this->name ?? 'document.pdf');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * @throws GotenbergApiErrored
     * @throws InvalidContentException
     */
    public function inline(?string $filename = null): Response
    {
        $filename = $this->sanitizeFilename($filename ?? $this->name ?? 'document.pdf');

        return response($this->generate(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('inline; filename="%s"', $filename),
        ]);
    }

    /**
     * @throws GotenbergApiErrored
     * @throws InvalidContentException
     */
    protected function generate(): string
    {
        if (empty($this->contentHtml)) {
            throw new InvalidContentException('No content set. Use view() or html() to set content.');
        }

        // Auto-adjust margins if header/footer are set and margins weren't explicitly set
        $marginTop = $this->marginTop;
        $marginBottom = $this->marginBottom;

        if (! $this->marginsExplicitlySet) {
            if ($this->headerHtml) {
                $marginTop = 1;
            }

            if ($this->footerHtml) {
                $marginBottom = 1;
            }
        }

        $builder = Gotenberg::chromium($this->apiUrl)
            ->pdf()
            ->paperSize($this->size[0], $this->size[1])
            ->margins($marginTop, $marginBottom, $this->marginLeft, $this->marginRight)
            ->waitDelay($this->waitDelay)
            ->printBackground();

        if ($this->landscape) {
            $builder = $builder->landscape();
        }

        if ($this->headerHtml) {
            $builder = $builder->header(Stream::string('header.html', $this->headerHtml));
        }

        if ($this->footerHtml) {
            $builder = $builder->footer(Stream::string('footer.html', $this->footerHtml));
        }

        $request = $builder->html(Stream::string('index.html', $this->contentHtml));

        // Send with authenticated client
        $response = Gotenberg::send($request, $this->createAuthenticatedClient());

        return $response->getBody()->getContents();
    }

    protected function createAuthenticatedClient(): Client
    {
        $stack = HandlerStack::create();

        $stack->push(Middleware::mapRequest(
            fn (RequestInterface $request) => $request->withHeader('X-Api-Key', $this->apiKey)
        ));

        return new Client(['handler' => $stack]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function renderView(string $view, array $data = []): string
    {
        return $this->processHtml(
            View::make($view, $data)->render()
        );
    }

    /**
     * Handles @pageNumber, @totalPages, @pageBreak, and @inlinedImage directives.
     */
    protected function processHtml(string $html): string
    {
        return Str::of($html)
            ->replace('@pageNumber', Directives::PAGE_NUMBER)
            ->replace('@totalPages', Directives::TOTAL_PAGES)
            ->replace('@pageBreak', Directives::PAGE_BREAK)
            ->replaceMatches(
                '/@inlinedImage\([\'"](.+?)[\'"]\)/',
                fn (array $matches): string => Directives::inlineImage((string) $matches[1])  // @phpstan-ignore-line
            )
            ->value();
    }

    protected function sanitizeFilename(string $filename, string $default = 'document.pdf'): string
    {
        return Str::of($filename)
            ->basename()
            ->replaceMatches('/[^\w\-. ]/', '')
            ->whenEmpty(fn (Stringable $string) => $string->append($default))
            ->value();
    }
}
