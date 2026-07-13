# Waybill — AI Agent Guide

This document helps AI agents understand the structure and architecture of the `cable8mm/waybill` package for accurate code generation and modification.

## Project Overview

A PHP library that generates PDF waybills for Korean courier services (CJ Logistics). Built with Factory + Enum patterns for easy extension to new parcel services.

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
│   ├── Enums/
│   │   └── ParcelService.php    # Parcel service enum (core extension point)
│   ├── Factories/
│   │   ├── Factory.php          # Abstract factory (definition + state + create pattern)
│   │   └── CjFactory.php        # CJ Logistics implementation
│   ├── Support/
│   │   ├── Faker.php            # Faker wrapper for Korean locale data
│   │   └── Mpdf.php             # mPDF instance factory (includes Korean font config)
│   └── ...
├── stubs/
│   └── Cj.stub                  # CJ waybill Twig template
├── fonts/
│   └── NanumBarunGothic.ttf     # Korean font
├── config.php                   # mPDF settings (font, paper, margins, watermark)
└── tests/
```

## Architecture & Key Patterns

### 1. Static Factory Method (`of()` / `make()`)

All major classes use static factory methods:

```php
// of() — auto-creates all dependencies
Waybill::of(ParcelService::Cj);

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

### 3. Enum + Factory Extension Pattern

To add a new parcel service, modify 3 files:

| Step | File                              | Description                                    |
| ---- | --------------------------------- | ---------------------------------------------- |
| 1    | `src/Factories/{Name}Factory.php` | Extend `Factory`, implement `definition()`     |
| 2    | `src/Enums/ParcelService.php`     | Add `case Name = 'value'`, implement 3 methods |
| 3    | `stubs/{Name}.stub`               | Create Twig template                           |

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
| `of(ParcelService, ?Mpdf)` | Static factory (auto-creates Mpdf)  |
| `make(ParcelService)`      | Create instance without Mpdf        |
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
| `of(ParcelService, int)`           | Static factory (with page number) |
| `source(string)`                   | Set source PDF path               |
| `page(int)`                        | Set page number to extract        |
| `save(string, Destination): mixed` | Save page                         |
| `download(string): mixed`          | Download page                     |

### ParcelService Enum (`src/Enums/ParcelService.php`)

Methods to implement when adding a new service:

```php
enum ParcelService: string
{
    case Cj = 'CJ택배';

    // Returns full namespace of the Factory class
    public function factoryClass(): string;

    // Returns the stub file path
    public function stub(): string;

    // Returns crop region [offsetX, offsetY, width, height] for Slicer
    public function templateArea(): array;
}
```

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

### 2. Add enum case

```php
// src/Enums/ParcelService.php
enum ParcelService: string
{
    case Cj = 'CJ택배';
    case Ups = 'UPS';  // ADD

    public function factoryClass(): string
    {
        return match ($this) {
            self::Cj => \Cable8mm\Waybill\Factories\CjFactory::class,
            self::Ups => \Cable8mm\Waybill\Factories\UpsFactory::class,  // ADD
        };
    }

    public function stub(): string
    {
        $stubPath = match ($this) {
            self::Cj => realpath(...),
            self::Ups => realpath(...),  // ADD
        };
        // ...
    }

    public function templateArea(): array
    {
        return match ($this) {
            self::Cj => [-40, -46, 285, 196],
            self::Ups => [0, 0, 210, 297],  // ADD (A4 size)
        };
    }
}
```

### 3. Create stub template

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
