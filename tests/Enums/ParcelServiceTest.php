<?php

namespace Cable8mm\Waybill\Tests\Enums;

use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Factories\CjFactory;
use PHPUnit\Framework\TestCase;

final class ParcelServiceTest extends TestCase
{
    public function test_name(): void
    {
        $this->assertEquals('Cj', ParcelService::Cj->name);
    }

    public function test_value(): void
    {
        $this->assertEquals('CJ택배', ParcelService::Cj->value);
    }

    public function test_factory_class(): void
    {
        $this->assertEquals(CjFactory::class, ParcelService::Cj->factoryClass());
    }

    public function test_stub(): void
    {
        $this->assertIsString(ParcelService::Cj->stub());
    }

    public function test_template_area(): void
    {
        $area = ParcelService::Cj->templateArea();

        $this->assertCount(4, $area);
        $this->assertIsInt($area[0]);
        $this->assertIsInt($area[1]);
        $this->assertIsInt($area[2]);
        $this->assertIsInt($area[3]);
    }
}
