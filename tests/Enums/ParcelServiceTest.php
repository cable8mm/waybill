<?php

namespace Cable8mm\Waybill\Tests\Enums;

use Cable8mm\Waybill\Enums\ParcelService;
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

    public function test_stub(): void
    {
        $this->assertIsString(ParcelService::Cj->stub());
    }
}
