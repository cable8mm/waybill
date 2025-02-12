<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Enums\ParcelService;
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

    public function test_it_export_to_array(): void
    {
        $orderSheet = Waybill::of(ParcelService::Cj)
            ->toArray();

        $this->assertIsArray($orderSheet);
    }

    public function test_it_export_to_file(): void
    {
        $waybill = Waybill::of(ParcelService::Cj)
            ->path(realpath(__DIR__.'/../dist'))
            ->save('test.pdf');

        $this->assertFileExists(realpath(__DIR__.'/../dist').DIRECTORY_SEPARATOR.'test.pdf');
    }
}
