<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Slicer;
use Cable8mm\Waybill\Support\Mpdf;
use Cable8mm\Waybill\Waybill;
use Cable8mm\Waybill\WaybillCollection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class SlicerTest extends TestCase
{
    public function test_path_method(): void
    {
        $slicer = Slicer::of(ParcelService::Cj, 1)
            ->source(realpath(__DIR__.'/../dist'));

        $reflection = new ReflectionClass($slicer);

        $source = $reflection->getProperty('source');

        $source->setAccessible(true);

        $this->assertStringContainsString(DIRECTORY_SEPARATOR.'dist', $source->getValue($slicer));
    }

    public function test_save_method(): void
    {
        $mpdf = Mpdf::instance();

        WaybillCollection::of(mpdf: $mpdf)
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->path(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'dist'))
            ->save('order_sheet_invoice.pdf');

        Slicer::of(ParcelService::Cj, 3)
            ->source(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'dist'.DIRECTORY_SEPARATOR.'order_sheet_invoice.pdf')
            ->save(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'dist'.DIRECTORY_SEPARATOR.'order_sheet_invoice_one_page.pdf');

        $this->assertFileExists(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'order_sheet_invoice_one_page.pdf');

        unlink(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'order_sheet_invoice.pdf');
        unlink(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'order_sheet_invoice_one_page.pdf');
    }

    public function test_download_method(): void
    {
        $mpdf = Mpdf::instance();

        WaybillCollection::of(mpdf: $mpdf)
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->path(realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'dist'))
            ->save('order_sheet_invoice.pdf');

        ob_start();

        Slicer::of(ParcelService::Cj, 3)
            ->source(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'dist'.DIRECTORY_SEPARATOR.'order_sheet_invoice.pdf')
            ->download(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'dist'.DIRECTORY_SEPARATOR.'order_sheet_invoice_one_page.pdf');

        $content = ob_get_contents();

        ob_end_clean();

        $this->assertNotEmpty($content);

        unlink(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'order_sheet_invoice.pdf');
    }
}
