<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Support\Mpdf;
use Cable8mm\Waybill\Waybill;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class WaybillTest extends TestCase
{
    public function test_path_exists(): void
    {
        $waybill = Waybill::of(ParcelService::Cj)
            ->path(realpath(__DIR__.'/../dist'));

        $reflection = new ReflectionClass($waybill);

        $path = $reflection->getProperty('path');

        $path->setAccessible(true);

        $this->assertStringContainsString(DIRECTORY_SEPARATOR.'dist', $path->getValue($waybill));
    }

    public function test_to_array(): void
    {
        $waybill = Waybill::of(ParcelService::Cj)
            ->toArray();

        $this->assertIsArray($waybill);
    }

    public function test_make(): void
    {
        $mpdf = Mpdf::instance();

        $waybill = Waybill::make(ParcelService::Cj)
            ->mpdf($mpdf)
            ->toArray();

        $this->assertIsArray($waybill);
    }

    public function test_state(): void
    {
        $waybill = Waybill::of(ParcelService::Cj)
            ->state(['6' => 'new value'])
            ->toArray();

        $this->assertSame('new value', $waybill['6']);
    }

    public function test_save(): void
    {
        $waybill = Waybill::of(ParcelService::Cj)
            ->path(realpath(__DIR__.'/../dist'))
            ->save('test.pdf');

        $this->assertFileExists(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'test.pdf');

        unlink(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'test.pdf');
    }

    public function test_save_on_another_way(): void
    {
        $waybill = Waybill::make(ParcelService::Cj)
            ->mpdf(Mpdf::instance())
            ->path(realpath(__DIR__.'/../dist'))
            ->save('test.pdf');

        $this->assertFileExists(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'test.pdf');

        unlink(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'test.pdf');
    }
}
