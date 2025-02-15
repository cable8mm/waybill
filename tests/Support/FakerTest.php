<?php

namespace Cable8mm\Waybill\Tests\Support;

use Cable8mm\Waybill\Support\Faker;
use PHPUnit\Framework\TestCase;

final class FakerTest extends TestCase
{
    public function test_product_name(): void
    {
        $value = Faker::shared()->productName();

        $this->assertIsString($value);
    }

    public function test_is_name_korean(): void
    {
        $value = Faker::shared()->name();

        $this->assertMatchesRegularExpression('/[\x{3130}-\x{318F}\x{AC00}-\x{D7AF}]/u', $value);
    }

    public function test_barcode(): void
    {
        $value = Faker::make()->barcode();

        $this->assertIsString($value);
    }
}
