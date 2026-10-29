<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Providers\HanjinProvider;
use Cable8mm\Waybill\Support\Faker;
use Cable8mm\Waybill\Waybill;
use PHPUnit\Framework\TestCase;
use Picqer\Barcode\Exceptions\InvalidCharacterException;

final class HanjinWaybillTest extends TestCase
{
    private const KEYS = [
        'tracking_number', 'tracking_barcode', 'terminal_code', 'terminal_name', 'terminal_barcode', 'hub_code', 'hub_number',
        'middle_code', 'center_code', 'center_name', 'group_rank', 'worker_name', 'worker_code', 'sender', 'receiver',
        'item_name', 'item_quantity', 'order_number', 'print_date', 'freight_type', 'delivery_fee', 'delivery_request', 'region_name',
    ];

    public function test_it_exports_every_hanjin_field(): void
    {
        $this->assertSame(self::KEYS, array_keys($this->export()));
    }

    public function test_each_export_generates_new_defaults(): void
    {
        $waybill = Waybill::make(new HanjinProvider);

        $this->assertNotSame($waybill->toArray()['tracking_number'], $waybill->toArray()['tracking_number']);
    }

    public function test_default_value_formats(): void
    {
        $data = $this->export();

        $this->assertMatchesRegularExpression('/^\d{12}$/', $data['tracking_number']);
        $this->assertMatchesRegularExpression('/^\d{4}$/', $data['terminal_code']);
        $this->assertMatchesRegularExpression('/^TEST-\d{6}$/', $data['order_number']);
        $this->assertMatchesRegularExpression('/\p{Hangul}/u', $data['receiver']['name']);
    }

    public function test_receiver_state_merges_with_defaults(): void
    {
        $receiver = $this->export(['receiver' => ['name' => 'X']])['receiver'];

        $this->assertSame(['name', 'phone', 'address'], array_keys($receiver));
    }

    public function test_sender_state_merges_with_defaults(): void
    {
        $sender = $this->export(['sender' => ['phone' => '02-000-0000']])['sender'];

        $this->assertSame(['name', 'phone', 'address'], array_keys($sender));
        $this->assertSame('02-000-****', $sender['phone']);
    }

    public function test_tracking_barcode_encodes_the_tracking_number(): void
    {
        $data = $this->export(['tracking_number' => '123456789012']);

        $this->assertSame(Faker::make()->interleaved2of5('123456789012'), $data['tracking_barcode']);
    }

    public function test_terminal_barcode_encodes_the_terminal_code(): void
    {
        $data = $this->export(['terminal_code' => '1234']);

        $this->assertSame(Faker::make()->barcode('1234'), $data['terminal_barcode']);
    }

    public function test_given_barcodes_are_kept(): void
    {
        $data = $this->export(['tracking_barcode' => 'given tracking', 'terminal_barcode' => 'given terminal']);

        $this->assertSame('given tracking', $data['tracking_barcode']);
        $this->assertSame('given terminal', $data['terminal_barcode']);
    }

    public function test_non_numeric_tracking_number_is_rejected(): void
    {
        $this->expectException(InvalidCharacterException::class);

        $this->export(['tracking_number' => 'AB12']);
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private function export(array $state = []): array
    {
        return Waybill::make(new HanjinProvider)->state($state)->toArray();
    }
}
