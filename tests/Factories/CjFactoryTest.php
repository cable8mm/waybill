<?php

namespace Cable8mm\Waybill\Tests\Factories;

use Cable8mm\Waybill\Factories\CjFactory;
use PHPUnit\Framework\TestCase;

final class CjFactoryTest extends TestCase
{
    public function test_it_must_have_all_fields(): void
    {
        $factoryFields = CjFactory::make()->definition();

        $this->assertEquals(14, count($factoryFields));
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
}
