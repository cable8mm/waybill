<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Collections\Waybills;
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Waybill;
use Cable8mm\Waybill\WaybillCollection;
use PHPUnit\Framework\TestCase;

final class CollectionExportTest extends TestCase
{
    public function test_repeat_count_multiplies_label_count(): void
    {
        $this->assertCount(5, WaybillCollection::make(Waybills::make([$this->label('A')]), 5)->toArray());
        $this->assertCount(15, WaybillCollection::make(Waybills::make([$this->label('A'), $this->label('B'), $this->label('C')]), 5)->toArray());
    }

    public function test_adding_a_list_to_an_empty_collection(): void
    {
        $collection = WaybillCollection::make(count: 5)->add([$this->label('A'), $this->label('B'), $this->label('C')]);

        $this->assertCount(15, $collection->toArray());
    }

    public function test_records_follow_insertion_order_in_each_pass(): void
    {
        $records = WaybillCollection::make(count: 2)->add($this->label('A'))->add($this->label('B'))->toArray();

        $this->assertSame(['A', 'B', 'A', 'B'], array_column($records, 'site_order_no'));
    }

    public function test_empty_collection_exports_nothing(): void
    {
        $this->assertSame([], WaybillCollection::make()->toArray());
    }

    private function label(string $order): Waybill
    {
        return Waybill::make(ParcelService::Cj)->state(['site_order_no' => $order]);
    }
}
