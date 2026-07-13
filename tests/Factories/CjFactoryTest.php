<?php

namespace Cable8mm\Waybill\Tests\Factories;

use Cable8mm\Waybill\Factories\CjFactory;
use PHPUnit\Framework\TestCase;

final class CjFactoryTest extends TestCase
{
    public function test_it_must_have_all_fields(): void
    {
        $factoryFields = CjFactory::make()->definition();

        $this->assertEquals(15, count($factoryFields));
    }

    public function test_it_must_be_fit_with_a_definition_type(): void
    {
        $factoryFields = CjFactory::make()->definition();

        $this->assertIsArray($factoryFields);
    }

    public function test_it_create_with_state(): void
    {
        $cjFactory = CjFactory::make()->state(['city' => ['code' => '10293', 'name' => 'new']])->create();

        $this->assertEquals('10293', $cjFactory['city']['code']);
        $this->assertEquals('new', $cjFactory['city']['name']);
    }

    public function test_it_create_with_empty_state_returns_original_definition(): void
    {
        $cjFactory = CjFactory::make()->state([])->create();

        $this->assertArrayHasKey('city', $cjFactory);
        $this->assertArrayHasKey('seller', $cjFactory);
        $this->assertArrayHasKey('receiver', $cjFactory);
        $this->assertArrayHasKey('barcode', $cjFactory);
    }

    public function test_it_create_with_partial_state_override(): void
    {
        $cjFactory = CjFactory::make()->state(['tracking_number' => 'OVERRIDE-1234'])->create();

        $this->assertEquals('OVERRIDE-1234', $cjFactory['tracking_number']);
        // Other fields should remain from the original definition
        $this->assertArrayHasKey('city', $cjFactory);
        $this->assertArrayHasKey('seller', $cjFactory);
    }

    public function test_it_create_without_state_returns_full_definition(): void
    {
        $cjFactory = CjFactory::make()->create();

        $this->assertCount(15, $cjFactory);
        $this->assertArrayHasKey('city', $cjFactory);
        $this->assertArrayHasKey('region', $cjFactory);
        $this->assertArrayHasKey('line_items', $cjFactory);
        $this->assertArrayHasKey('seller', $cjFactory);
        $this->assertArrayHasKey('receiver', $cjFactory);
        $this->assertArrayHasKey('printed', $cjFactory);
        $this->assertArrayHasKey('total_printed_count', $cjFactory);
        $this->assertArrayHasKey('site_order_no', $cjFactory);
        $this->assertArrayHasKey('tracking_number', $cjFactory);
        $this->assertArrayHasKey('delivery_worker', $cjFactory);
        $this->assertArrayHasKey('settlement_type', $cjFactory);
        $this->assertArrayHasKey('print_date', $cjFactory);
        $this->assertArrayHasKey('box_quantity', $cjFactory);
        $this->assertArrayHasKey('freight_type', $cjFactory);
        $this->assertArrayHasKey('barcode', $cjFactory);
    }
}
