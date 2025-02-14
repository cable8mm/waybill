<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Slicer;
use Cable8mm\Waybill\Waybill;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class SlicerTest extends TestCase
{
    public function test_path(): void
    {
        $slicer = Slicer::of(ParcelService::Cj, 1)
            ->source(realpath(__DIR__.'/../dist'));

        $reflection = new ReflectionClass($slicer);

        $source = $reflection->getProperty('source');

        $source->setAccessible(true);

        $this->assertStringContainsString(DIRECTORY_SEPARATOR.'dist', $source->getValue($slicer));
    }

    public function test_save(): void
    {
        Waybill::of(ParcelService::Cj)
            ->path(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'dist'))
            ->save('order_sheet_invoice.pdf');

        Slicer::of(ParcelService::Cj, 1)
            ->source(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'dist'.DIRECTORY_SEPARATOR.'order_sheet_invoice.pdf')
            ->save(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'dist'.DIRECTORY_SEPARATOR.'order_sheet_invoice_one_page.pdf');

        $this->assertFileExists(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'order_sheet_invoice_one_page.pdf');

        unlink(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'order_sheet_invoice.pdf');
        unlink(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'order_sheet_invoice_one_page.pdf');
    }
}
