# PDF Waybill Generator

![Coding Style Actions](https://github.com/cable8mm/waybill/actions/workflows/code-style.yml/badge.svg)
![Run Tests Actions](https://github.com/cable8mm/waybill/actions/workflows/run-tests.yml/badge.svg)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/cable8mm/waybill.svg)](https://packagist.org/packages/cable8mm/waybill)
[![Packagist Dependency Version](https://img.shields.io/packagist/dependency-v/cable8mm/waybill/php?logo=PHP&logoColor=white&color=777BB4)](https://packagist.org/packages/cable8mm/waybill)
[![Total Downloads](https://img.shields.io/packagist/dt/cable8mm/waybill.svg)](https://packagist.org/packages/cable8mm/waybill)
[![Packagist Stars](https://img.shields.io/packagist/stars/cable8mm/waybill)](https://github.com/cable8mm/waybill/stargazers)

A lightweight PHP library for generating PDF waybills with ease. This package allows developers to create and customize waybills in PDF format for courier and logistics services. It supports barcode generation, sender/receiver details, and customizable layouts. Perfect for automating shipping label creation in your e-commerce or logistics applications.

## Installation

You can install the package via composer:

```bash
composer require cable8mm/waybill
```

## Usage

Save a waybill for pdf format:

```php
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Waybill;

Waybill::of(ParcelService::Cj)
    ->path(realpath(__DIR__.'/../dist'))
    ->save('test.pdf');
```

Get a waybill array:

```php
$waybill = Waybill::of(ParcelService::Cj)
            ->toArray()
```

Save multiple waybills for pdf format:

```php
$mpdf = Mpdf::instance();

WaybillCollection::of(mpdf: $mpdf)
    ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
    ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
    ->path(realpath(__DIR__.'/../dist'))
    ->save('collection.pdf');

// or

WaybillCollection::of(mpdf: $mpdf)
    ->add([
      Waybill::of(ParcelService::Cj, mpdf: $mpdf),
      Waybill::of(ParcelService::Cj, mpdf: $mpdf),      
      ])
    ->path(realpath(__DIR__.'/../dist'))
    ->save('collection.pdf');

```

Slice the page of the waybills:

```php
Slicer::of(ParcelService::Cj, 1)
    ->source('source.pdf')
    ->save('one_page.pdf'); // or `->download('one_page.pdf')`
```

### How to customize

If you want to add another parcel service like UPS, you would need to make `Enums` and `Factory` class, for example:

1. Make `UpsFactory.php` into `src/Factories' folder.
2. Make `Enum` element into `src/Enums` folder.

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email <cable8mm@gmail.com> instead of using the issue tracker.

## Credits

- [Samgu Lee](https://github.com/cable8mm)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
