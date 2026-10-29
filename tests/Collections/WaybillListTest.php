<?php

namespace Cable8mm\Waybill\Tests\Collections;

use Cable8mm\Waybill\Collections\Waybills;
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Waybill;
use PHPUnit\Framework\TestCase;

final class WaybillListTest extends TestCase
{
    public function test_it_counts_the_given_labels(): void
    {
        $this->assertCount(3, Waybills::make([$this->label(), $this->label(), $this->label()]));
        $this->assertCount(2, new Waybills([$this->label(), $this->label()]));
    }

    public function test_adding_one_label_places_it_last(): void
    {
        $label = $this->label();
        $waybills = Waybills::make([$this->label()])->add($label);

        $this->assertCount(2, $waybills);
        $this->assertSame($label, $waybills[1]);
    }

    public function test_adding_a_list_appends_in_order(): void
    {
        $first = $this->label();
        $second = $this->label();
        $waybills = Waybills::make([$this->label()])->add([$first, $second]);

        $this->assertSame([$first, $second], [$waybills[1], $waybills[2]]);
    }

    public function test_indexed_read_write_append_exists_and_remove(): void
    {
        $first = $this->label();
        $replacement = $this->label();
        $appended = $this->label();
        $waybills = Waybills::make([$first]);

        $this->assertSame($first, $waybills[0]);
        $this->assertNull($waybills[5]);

        $waybills[0] = $replacement;
        $this->assertSame($replacement, $waybills[0]);

        $waybills[] = $appended;
        $this->assertSame($appended, $waybills[1]);

        $this->assertTrue(isset($waybills[1]));
        $this->assertFalse(isset($waybills[5]));

        unset($waybills[1]);
        $this->assertFalse(isset($waybills[1]));
    }

    private function label(): Waybill
    {
        return Waybill::make(ParcelService::Cj);
    }
}
