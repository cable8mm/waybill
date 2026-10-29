# Waybill — AI Agent Guide

This document helps AI agents understand the structure and architecture of the `cable8mm/waybill` package for accurate code generation and modification.

## Project Overview

A PHP library that generates PDF waybills. Carrier-specific data and templates are supplied by `WaybillProvider`; physical page dimensions are supplied separately by `LabelFormat`. Built-in formats are CJ Logistics 5-inch and Hanjin NS, NL, and FS.

- **Namespace root**: `Cable8mm\Waybill`
- **PHP version**: ^8.2
- **PDF engine**: mpdf/mpdf ^8.0
- **Template engine**: cable8mm/stub-template (Twig-based)
- **Faker**: fakerphp/faker + mbezhanov/faker-provider-collection
- **Barcode**: picqer/php-barcode-generator

## Directory Structure

```
waybill/
├── src/
│   ├── Waybill.php              # Main entry: create single waybill
│   ├── WaybillCollection.php    # Manage multiple waybills
│   ├── Slicer.php               # Extract a single waybill from a PDF page
│   ├── Collections/
│   │   └── Waybills.php         # Waybill object container (ArrayAccess, Countable, IteratorAggregate)
│   ├── Contracts/
│   │   └── WaybillProvider.php  # Carrier-specific data/template contract
│   ├── Enums/
│   │   └── ParcelService.php    # Built-in providers (currently CJ)
│   ├── Factories/
│   │   ├── Factory.php          # Abstract factory (definition + state + create pattern)
│   │   ├── CjFactory.php        # CJ Logistics implementation
│   │   └── HanjinFactory.php    # Hanjin sample shipment data and barcodes
│   ├── Providers/
│   │   └── HanjinProvider.php   # Hanjin NS/NL/FS provider
│   ├── Support/
│   │   ├── Faker.php            # Faker wrapper for Korean locale data
│   │   ├── LabelFormat.php      # Physical label dimensions in mm
│   │   └── Mpdf.php             # mPDF instance factory (includes Korean font config)
│   └── ...
├── stubs/
│   └── Cj.stub                  # CJ waybill Twig template
├── fonts/
│   ├── NanumBarunGothic.ttf     # CJ Korean font
│   ├── NanumGothic-Regular.ttf  # Hanjin-specified Korean font
│   ├── NanumGothic-Bold.ttf     # Hanjin-specified bold font
│   ├── NanumGothic-README.md    # Font source and attribution
│   └── NanumGothic-OFL.txt      # Nanum Gothic license
├── config.php                   # mPDF settings (font, paper, margins, watermark)
└── tests/
    └── fixtures/
        ├── cj/                    # CJ official 5-inch reference guide
        └── hanjin/                # Hanjin NS/NL/FS guide images + source notes
```

## Architecture & Key Patterns

### 1. Static Factory Method (`of()` / `make()`)

All major classes use static factory methods:

```php
// of() — auto-creates all dependencies
Waybill::of(ParcelService::Cj);
Waybill::of(ParcelService::Cj, format: LabelFormat::cj5Inch());

// make() — create instance without mpdf, inject later
Waybill::make(ParcelService::Cj)->mpdf($mpdf);
```

### 2. Fluent API (Method Chaining)

All setters return `$this;` to support chaining:

```php
Waybill::of(ParcelService::Cj)
    ->state(['seller' => ['name' => '회사명']])
    ->path('/dist')
    ->save('waybill.pdf');
```

### 3. Provider + Factory Extension Pattern

To add a new carrier, implement `WaybillProvider` in the application or package. Do not add carrier cases to `ParcelService` unless the provider is intended to ship as a built-in. Carrier and label size are separate: each provider lists supported `LabelFormat` values and selects a template for a requested format.

| Step | File                              | Description                                    |
| ---- | --------------------------------- | ---------------------------------------------- |
| 1    | `{Name}Factory.php` | Extend `Factory`, implement `definition()` |
| 2    | `{Name}Provider.php` | Implement `WaybillProvider`, list only verified formats, resolve factory/template/crop area |
| 3    | `{Name}-{format}.stub` | Create a template for each supported carrier/format combination |

### 4. State Pattern

`Factory::state()` overrides specific fields from `definition()`:

```php
// Factory.php
public function create(): array
{
    $record = $this->definition();
    if (! empty($this->state)) {
        $record = array_replace($record, $this->state);
    }
    return $record;
}
```

Both `Waybill::toArray()` and `Waybill::write()` internally call `Factory::create()`.

### 5. mPDF Instances Must Be Created via Support\Mpdf Only

```php
// CORRECT — Korean font config is applied
use Cable8mm\Waybill\Support\Mpdf;
$mpdf = Mpdf::instance();

// WRONG — bypasses config.php settings (no Korean font support)
$mpdf = new \Mpdf\Mpdf(); // ❌
```

## Class Details

### Waybill (`src/Waybill.php`)

| Method                     | Description                         |
| -------------------------- | ----------------------------------- |
| `of(WaybillProvider, ?Mpdf, ?LabelFormat)` | Static factory (auto-creates Mpdf for the chosen format) |
| `make(WaybillProvider, ?LabelFormat)` | Create instance without Mpdf |
| `mpdf(Mpdf)`               | Inject Mpdf instance                |
| `state(array)`             | Set custom data                     |
| `path(string)`             | Set save path                       |
| `toArray(): array`         | Return data as array                |
| `save(string): mixed`      | Save as PDF file                    |
| `download(string): mixed`  | Output PDF as download stream       |
| `write(?array)`            | Render HTML and write to Mpdf       |
| `__toString(): string`     | Return service name (e.g. "CJ택배") |

### WaybillCollection (`src/WaybillCollection.php`)

| Method                      | Description                      |
| --------------------------- | -------------------------------- |
| `of(?Waybills, int, ?Mpdf)` | Static factory                   |
| `make(?Waybills, int)`      | Create without Mpdf              |
| `add(Waybill\|array)`       | Add waybill(s)                   |
| `path(string)`              | Set save path                    |
| `toArray(): array`          | Return all waybill data as array |
| `save(string): mixed`       | Save as PDF                      |
| `download(string): mixed`   | Download as PDF                  |

### Slicer (`src/Slicer.php`)

| Method                             | Description                       |
| ---------------------------------- | --------------------------------- |
| `of(WaybillProvider, int, ?LabelFormat)` | Static factory (with page number and format) |
| `source(string)`                   | Set source PDF path               |
| `page(int)`                        | Set page number to extract        |
| `save(string, Destination): mixed` | Save page                         |
| `download(string): mixed`          | Download page                     |

### WaybillProvider (`src/Contracts/WaybillProvider.php`)

Methods to implement when adding a new service:

```php
interface WaybillProvider
{
    public function factoryClass(): string;
    public function labelFormats(): array;
    public function defaultLabelFormat(): LabelFormat;
    public function stub(LabelFormat $format): string;
    public function templateArea(LabelFormat $format): array;
    public function name(): string;
}
```

`LabelFormat` stores physical width, height, and margins in millimetres and produces the corresponding mPDF page options. A provider must reject formats it does not support. Do not infer carrier formats from a generic inch name; use a carrier guide or approved sample.

### Factory Abstract Class (`src/Factories/Factory.php`)

```php
abstract class Factory
{
    abstract public function definition(): array;
    public static function make(?array $state = []): static;
    public function state(array $state): static;
    public function create(): array;  // definition() + state merge
}
```

### CjFactory (`src/Factories/CjFactory.php`)

Fields returned by `definition()`:

| Key                   | Type                                      | Description          |
| --------------------- | ----------------------------------------- | -------------------- |
| `city`                | `array{code, name}`                       | City code/name       |
| `region`              | `array{code, name}`                       | Region code/name     |
| `line_items`          | `string[]`                                | Product list         |
| `seller`              | `array{name, phone, site, address}`       | Seller info          |
| `receiver`            | `array{name, phone, cell_phone, address}` | Receiver info        |
| `printed`             | `string (Y-m-d)`                          | Print date           |
| `total_printed_count` | `int`                                     | Total print count    |
| `site_order_no`       | `string`                                  | Order number         |
| `tracking_number`     | `string`                                  | Tracking number      |
| `delivery_worker`     | `string`                                  | Delivery worker name |
| `settlement_type`     | `string`                                  | Settlement type      |
| `print_date`          | `string (Y-m-d)`                          | Print date           |
| `box_quantity`        | `int`                                     | Box quantity         |
| `freight_type`        | `string`                                  | Freight type         |
| `barcode`             | `string (base64 img)`                     | Barcode image        |

### Mpdf Factory (`src/Support/Mpdf.php`)

- Reads all settings from `config.php` to create mPDF instance
- Automatically registers Korean font (NanumBarunGothic)
- Applies watermark, font, paper settings
- The bundled CJ preview uses a 123 × 100 mm landscape 5-inch `LabelFormat` with zero margins.
- Hanjin NS/NL/FS official guide images and masking instructions are preserved under `tests/fixtures/hanjin/`.
- Hanjin media settings are 101.5 × 200 mm (NS), 101.5 × 100 mm (NL), and 100 × 123 mm (FS), ±5 mm. Templates use the orientation in Hanjin's layout examples (NS 200 × 101.5 mm; NL 101.5 × 100 mm; FS 123 × 100 mm); printer drivers may require swapping width and height.
- `HanjinFactory` masks sender and receiver names, phone numbers, and detailed addresses per Hanjin's published masking guide. It generates illustrative sample tracking and routing codes only.
- Hanjin templates use bundled Nanum Gothic Regular/Bold as required by the carrier guide; preserve its SIL Open Font License file.
- Factory-generated tracking, routing, and recipient data is illustrative; it is not issued or validated by CJ Logistics.

### CJ 5-inch Template Scope

- `stubs/Cj.stub` follows the 123 × 100 mm 5-inch new-form example in `tests/fixtures/cj/cj-5inch-guide-2025-11-05.pdf`.
- The guide is a 2025 reference. CJ changed routing-code output for 5-inch and 8-inch labels effective 2026-08-05; keep carrier-issued routing data dynamic and preserve the current `◆` marker.
- A supplied `tracking_number` is encoded into the barcode unless the caller explicitly supplies a `barcode` image.
- Real dispatch requires a CJ-issued tracking number and contract-specific routing data. The package does not reserve shipments or obtain those values.

### Faker (`src/Support/Faker.php`)

- Generates Korean data (names, addresses, phone numbers, company names)
- Generates barcodes via `picqer/php-barcode-generator`
- Generates product names via `mbezhanov/faker-provider-collection`

## Adding a New Parcel Service (Step-by-Step)

Example: Adding "UPS"

### 1. Create factory class

```php
// src/Factories/UpsFactory.php
namespace Cable8mm\Waybill\Factories;

use Cable8mm\Waybill\Support\Faker;

class UpsFactory extends Factory
{
    public function definition(): array
    {
        return [
            // Fields tailored to UPS
            'sender' => [
                'name' => Faker::shared()->company(),
                'phone' => Faker::shared()->phoneNumber(),
                'address' => Faker::shared()->address(),
            ],
            'receiver' => [
                'name' => Faker::shared()->name(),
                'phone' => Faker::shared()->phoneNumber(),
                'address' => Faker::shared()->address(),
            ],
            'tracking_number' => Faker::shared()->bothify('1Z-????-????-????'),
            // ...
        ];
    }
}
```

### 2. Implement a provider

Implement `WaybillProvider` in the package or consuming application. It owns the carrier factory and chooses a carrier/format-specific stub and crop area. Add verified formats to `labelFormats()`; do not infer support from a generic inch label.

### 3. Create a stub template for each supported carrier/format combination

```twig
{# stubs/Ups.stub #}
<html>
<head><meta charset="utf-8"><title>UPS Waybill</title></head>
<body>
    <h1>{{ sender.name }}</h1>
    <p>{{ receiver.name }} - {{ tracking_number }}</p>
</body>
</html>
```

## Testing Conventions

- Test classes must be `final class`
- Test method names use snake_case (e.g. `test_it_create_with_state`)
- PDF generation tests must `unlink()` files after assertion
- Output tests must use `ob_start()` / `ob_end_clean()`
- Reflection for private property access requires `setAccessible(true)`

```php
final class WaybillTest extends TestCase
{
    public function test_path_exists(): void
    {
        $reflection = new ReflectionClass($waybill);
        $path = $reflection->getProperty('path');
        $path->setAccessible(true);
        $this->assertStringContainsString('dist', $path->getValue($waybill));
    }
}
```

## Common Pitfalls

1. **NEVER use `array_values()` in `Factory::create()`** — This destroys associative array keys and breaks state merging. Use `array_replace()` instead.
2. **NEVER call `definition()` in `Waybill::write()`** — State won't be applied. Always use `create()` instead.
3. **NEVER instantiate `new \Mpdf\Mpdf()` directly in `Slicer`** — Always use `Support\Mpdf::instance()` to get Korean font config.
4. **ALWAYS set default value for `$path` in `Waybill`/`WaybillCollection`** — `private string $path = ''` is required to avoid uninitialized property warnings.

## Composer Scripts

| Script                   | Command                       |
| ------------------------ | ----------------------------- |
| `composer test`          | `vendor/bin/phpunit`          |
| `composer test-coverage` | Generate HTML coverage report |
| `composer lint`          | Laravel Pint code style check |
| `composer apidoc`        | Doctum API documentation      |

## CI/CD

GitHub Actions workflows:

- `.github/workflows/code-style.yml` — Laravel Pint check
- `.github/workflows/run-tests.yml` — PHPUnit test suite

## License

MIT
