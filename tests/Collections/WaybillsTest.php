<?php

namespace Cable8mm\Waybill\Tests\Collections;

use Cable8mm\Waybill\Collections\Waybills;
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Waybill;
use PHPUnit\Framework\TestCase;

final class WaybillsTest extends TestCase
{
    public function test_create(): void
    {
        $array = [
            Waybill::of(ParcelService::Cj),
        ];

        $waybills = Waybills::make($array);

        $this->assertSame(1, count($waybills));
    }

    public function test_getter(): void
    {
        $waybill = Waybill::of(ParcelService::Cj);

        $waybills = Waybills::make([$waybill]);

        $this->assertSame($waybill, $waybills[0]);
    }

    public function test_setter(): void
    {
        $waybill = Waybill::of(ParcelService::Cj);

        $waybills = Waybills::make();

        $waybills->add($waybill);

        $this->assertSame($waybill, $waybills[0]);
    }

    public function test_add_with_array_style(): void
    {
        $waybill1 = Waybill::of(ParcelService::Cj);
        $waybill2 = Waybill::of(ParcelService::Cj);

        $waybills = Waybills::make();

        $waybills[] = $waybill1;
        $waybills[] = $waybill2;

        $this->assertSame($waybill1, $waybills[0]);
        $this->assertSame($waybill2, $waybills[1]);
    }
}
