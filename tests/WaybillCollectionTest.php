<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Collections\Waybills;
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Support\Mpdf;
use Cable8mm\Waybill\Waybill;
use Cable8mm\Waybill\WaybillCollection;
use PHPUnit\Framework\TestCase;

final class WaybillCollectionTest extends TestCase
{
    public function test_to_array_method(): void
    {
        $mpdf = Mpdf::instance();

        $waybills = Waybills::make([
            Waybill::of(ParcelService::Cj, $mpdf),
        ]);

        $waybillCollection = WaybillCollection::of(
            $waybills,
            5,
            $mpdf
        )->toArray();

        $this->assertEquals(5, count($waybillCollection));
    }

    public function test_to_array_method_on_another_way(): void
    {
        $mpdf = Mpdf::instance();

        $waybillCollection = WaybillCollection::of(
            Waybills::make()
                ->add(Waybill::of(ParcelService::Cj, $mpdf))
                ->add(Waybill::of(ParcelService::Cj, $mpdf))
                ->add(Waybill::of(ParcelService::Cj, $mpdf)),
            5,
            $mpdf
        )->toArray();

        $this->assertEquals(15, count($waybillCollection));
    }

    public function test_save_method(): void
    {
        $mpdf = Mpdf::instance();

        $waybills = Waybills::make([
            Waybill::of(ParcelService::Cj, mpdf: $mpdf),
            Waybill::of(ParcelService::Cj, mpdf: $mpdf),
            Waybill::of(ParcelService::Cj, mpdf: $mpdf),
            Waybill::of(ParcelService::Cj, mpdf: $mpdf),
            Waybill::of(ParcelService::Cj, mpdf: $mpdf),
        ]);

        WaybillCollection::of(
            $waybills,
            mpdf: $mpdf
        )->path(realpath(__DIR__.'/../dist'))->save('collection.pdf');

        $this->assertFileExists(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'collection.pdf');

        unlink(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'collection.pdf');
    }

    public function test_save_with_chaining(): void
    {
        $mpdf = Mpdf::instance();

        WaybillCollection::of(mpdf: $mpdf)
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->path(realpath(__DIR__.'/../dist'))->save('collection.pdf');

        $this->assertFileExists(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'collection.pdf');

        unlink(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'collection.pdf');
    }
}
