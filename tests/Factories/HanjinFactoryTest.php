<?php

namespace Cable8mm\Waybill\Tests\Factories;

use Cable8mm\Waybill\Factories\HanjinFactory;
use PHPUnit\Framework\TestCase;

final class HanjinFactoryTest extends TestCase
{
    public function test_it_generates_hanjin_barcode_types_and_masks_shipment_data(): void
    {
        $waybill = HanjinFactory::make([
            'tracking_number' => '123456789012',
            'terminal_code' => '5432',
            'sender' => [
                'name' => '김한진',
                'phone' => '02-728-1234',
                'address' => '서울시 중구 남대문로 63 한진빌딩',
            ],
            'receiver' => [
                'name' => '홍길동',
                'phone' => '010-1234-5678',
                'address' => '서울시 중구 소공동 51 한진빌딩 10층',
            ],
        ])->create();

        $this->assertSame('123456789012', $waybill['tracking_number']);
        $this->assertSame('5432', $waybill['terminal_code']);
        $this->assertStringStartsWith('data:image/png;base64,', $waybill['tracking_barcode']);
        $this->assertStringStartsWith('data:image/png;base64,', $waybill['terminal_barcode']);
        $this->assertSame('김*진', $waybill['sender']['name']);
        $this->assertSame('02-728-****', $waybill['sender']['phone']);
        $this->assertSame('서울시 중구 남대문로 63 ****', $waybill['sender']['address']);
        $this->assertSame('홍*동', $waybill['receiver']['name']);
        $this->assertSame('010-1234-****', $waybill['receiver']['phone']);
        $this->assertSame('서울시 중구 소공동 ****', $waybill['receiver']['address']);
    }
}
