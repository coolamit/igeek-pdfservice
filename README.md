# iGeek PdfService

A Laravel package for generating PDFs using [Gotenberg](https://gotenberg.dev/). Convert Blade views or raw HTML to PDF with support for headers, footers, custom page sizes, and more.

If you do not have Gotenberg set up, you can quickly set it up using Docker.
See [coolamit/pdfservice](https://github.com/coolamit/pdfservice) to get started.

---

[![GitHub Tests Action Status][icon-tests]][href-tests]
[![GitHub PHPStan Action Status][icon-phpstantest]][href-phpstantest]
[![GitHub Code Style Action Status][icon-style]][href-style]

![License][icon-license]
![PHP][icon-php]
[![Latest Version on Packagist][icon-version]][href-version]

---

## Minimum Requirements

- PHP 8.3
- Laravel 11.x
- A running [Gotenberg](https://gotenberg.dev/) server

## Installation

Install the package via Composer:

```bash
composer require igeek/pdfservice
```

Publish the config file:

```bash
php artisan vendor:publish --tag="pdfservice-config"
```

## Configuration

Add these environment variables to your `.env` file:

```env
PDFSERVICE_URL=http://localhost:3000
PDFSERVICE_API_KEY=your-api-key
```

Here the `PDFSERVICE_URL` is the URL where you can access your Gotenberg server and `PDFSERVICE_API_KEY` is the API key if you have authentication enabled.

The published config file (`config/pdfservice.php`):

```php
return [
    'url' => env('PDFSERVICE_URL'),
    'key' => env('PDFSERVICE_API_KEY'),
];
```

## Usage

### Basic Usage with Blade Views

```php
use Igeek\PdfService\Facades\Pdf;

// Generate and save a PDF
Pdf::view('pdf.invoice', ['invoice' => $invoice])
    ->save('invoices/invoice-001.pdf');

// Generate and download
return Pdf::view('pdf.invoice', ['invoice' => $invoice])->download('invoice.pdf');

// Display inline in browser
return Pdf::view('pdf.invoice', ['invoice' => $invoice])->inline('invoice.pdf');
```

### Using Raw HTML

```php
Pdf::html('<h1>Hello World</h1><p>This is a PDF.</p>')
    ->save('hello.pdf');
```

### Headers and Footers

```php
Pdf::view('pdf.content', $data)
    ->headerView('pdf.header', ['title' => 'My Report'])
    ->footerView('pdf.footer')
    ->save('report.pdf');

// Or with raw HTML
Pdf::view('pdf.content', $data)
    ->headerHtml('<div style="text-align: center;">Header</div>')
    ->footerHtml('<div style="text-align: center;">Page @pageNumber of @totalPages</div>')
    ->save('report.pdf');
```

### Page Formats

Available formats: `a0`, `a1`, `a2`, `a3`, `a4`, `a5`, `a6`, `letter`, `legal`, `tabloid`

```php
use Igeek\PdfService\Enums\Format;

// Using enum
Pdf::view('pdf.content', $data)
    ->format(Format::Letter)
    ->save('letter.pdf');

// Using string
Pdf::view('pdf.content', $data)
    ->format('legal')
    ->save('legal.pdf');
```

### Custom Page Size

Set custom dimensions in inches `[width, height]`:

```php
Pdf::view('pdf.content', $data)
    ->size([8.5, 14])  // Custom size in inches
    ->save('custom.pdf');
```

### Orientation

```php
Pdf::view('pdf.content', $data)
    ->landscape()
    ->save('landscape.pdf');

Pdf::view('pdf.content', $data)
    ->portrait()  // Default
    ->save('portrait.pdf');
```

### Margins

Set margins in inches (top, right, bottom, left):

```php
Pdf::view('pdf.content', $data)
    ->margins(1, 0.5, 1, 0.5)
    ->save('with-margins.pdf');
```

**Note:** When headers or footers are set and margins aren't explicitly defined, top/bottom margins automatically adjust to 1 inch.

### Wait Delay

Control how long Chromium waits before capturing the PDF (useful for JavaScript-heavy content):

```php
Pdf::view('pdf.content', $data)
    ->waitDelay('1s')  // Default: 500ms
    ->save('delayed.pdf');
```

### Storage Disk

Specify which Laravel filesystem disk to use for saving:

```php
Pdf::view('pdf.content', $data)
    ->disk('s3')
    ->save('reports/monthly.pdf');
```

## Blade Directives

Use these directives in your PDF Blade views:

### Page Number

```blade
<footer>
    Page @pageNumber of @totalPages
</footer>
```

### Page Break

```blade
<div>First page content</div>
@pageBreak
<div>Second page content</div>
```

### Inline Images

Embed images as base64 from storage paths:

```blade
@inlinedImage('logos/company.png')
```

External URLs are passed through as is.

## Output Methods

### Save to Storage

```php
$success = Pdf::view('pdf.content', $data)->save('path/to/file.pdf');
```

### Download Response

```php
return Pdf::view('pdf.content', $data)->download('filename.pdf');
```

### Inline Response

Display PDF in browser:

```php
return Pdf::view('pdf.content', $data)->inline('filename.pdf');
```

### Get Raw Content

```php
$pdfContent = Pdf::view('pdf.content', $data)->content();
```

## Custom API Credentials

Use different credentials to access Gotenberg API for specific operations:

```php
use Igeek\PdfService\Facades\Pdf;

Pdf::using('https://other-gotenberg.example.com', 'other-api-key')
    ->view('pdf.content', $data)
    ->save('document.pdf');
```

## Fluent Interface

All methods support chaining:

```php
Pdf::view('pdf.invoice', $data)
    ->headerView('pdf.header')
    ->footerView('pdf.footer')
    ->format(Format::A4)
    ->landscape()
    ->margins(1, 0.75, 1, 0.75)
    ->waitDelay('500ms')
    ->disk('local')
    ->name('invoice.pdf')
    ->save('invoices/2024/invoice-001.pdf');
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

This package is released under MIT License (MIT). Please see [License](LICENSE.md) for more information.

[href-phpstantest]: https://github.com/coolamit/igeek-pdfservice/actions/workflows/phpstan.yml
[href-style]: https://github.com/coolamit/igeek-pdfservice/actions/workflows/code-style.yml
[href-tests]: https://github.com/coolamit/igeek-pdfservice/actions/workflows/tests.yml
[href-version]: https://packagist.org/packages/igeek/pdfservice
[icon-license]: https://img.shields.io/github/license/coolamit/igeek-pdfservice?color=blue&label=License
[icon-phpstantest]: https://img.shields.io/github/actions/workflow/status/coolamit/igeek-pdfservice/phpstan.yml?branch=master&label=PHPStan
[icon-php]: https://img.shields.io/packagist/php-v/igeek/pdfservice?color=blue&label=PHP
[icon-style]: https://img.shields.io/github/actions/workflow/status/coolamit/igeek-pdfservice/code-style.yml?branch=master&label=Code%20Style
[icon-tests]: https://img.shields.io/github/actions/workflow/status/coolamit/igeek-pdfservice/tests.yml?branch=master&label=Tests
[icon-version]: https://img.shields.io/packagist/v/igeek/pdfservice.svg?label=Packagist
