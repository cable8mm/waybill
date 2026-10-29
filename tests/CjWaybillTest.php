<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Support\Faker;
use Cable8mm\Waybill\Waybill;
use PHPUnit\Framework\TestCase;

final class CjWaybillTest extends TestCase
{
    private const KEYS = [
        'city', 'region', 'line_items', 'seller', 'receiver', 'printed', 'total_printed_count', 'site_order_no',
        'tracking_number', 'delivery_worker', 'settlement_type', 'print_date', 'box_quantity', 'freight_type', 'barcode',
    ];

    public function test_it_exports_every_cj_field(): void
    {
        $data = Waybill::make(ParcelService::Cj)->toArray();

        $this->assertSame(self::KEYS, array_keys($data));
    }

    public function test_each_export_generates_new_defaults(): void
    {
        $waybill = Waybill::make(ParcelService::Cj);

        $this->assertNotSame($waybill->toArray()['tracking_number'], $waybill->toArray()['tracking_number']);
    }

    public function test_default_tracking_number_format(): void
    {
        $this->assertMatchesRegularExpression('/^\d{4}-\d{4}-\d{4}$/', Waybill::make(ParcelService::Cj)->toArray()['tracking_number']);
    }

    public function test_default_recipient_name_is_korean(): void
    {
        $this->assertMatchesRegularExpression('/\p{Hangul}/u', Waybill::make(ParcelService::Cj)->toArray()['receiver']['name']);
    }

    public function test_state_keys_replace_and_extend_defaults(): void
    {
        $data = Waybill::make(ParcelService::Cj)->state(['6' => 'new value', 'box_quantity' => 7])->toArray();

        $this->assertSame('new value', $data['6']);
        $this->assertSame(7, $data['box_quantity']);
    }

    public function test_nested_state_keys_merge_with_defaults(): void
    {
        $data = Waybill::make(ParcelService::Cj)->state([
            'seller' => ['name' => 'X'],
            'receiver' => ['phone' => '02-000-0000'],
            'city' => ['code' => 'A1'],
            'region' => ['name' => 'R'],
        ])->toArray();

        $this->assertSame('X', $data['seller']['name']);
        $this->assertSame(['name', 'phone', 'site', 'address'], array_keys($data['seller']));
        $this->assertSame('02-000-0000', $data['receiver']['phone']);
        $this->assertSame(['name', 'phone', 'cell_phone', 'address'], array_keys($data['receiver']));
        $this->assertSame('A1', $data['city']['code']);
        $this->assertArrayHasKey('name', $data['city']);
        $this->assertSame('R', $data['region']['name']);
        $this->assertArrayHasKey('code', $data['region']);
    }

    public function test_partial_nested_state_renders_a_pdf(): void
    {
        $path = sys_get_temp_dir();
        $filename = 'cj-partial-state.pdf';

        Waybill::of(ParcelService::Cj)->state(['receiver' => ['name' => 'X']])->path($path)->save($filename);

        $this->assertFileExists($path.DIRECTORY_SEPARATOR.$filename);

        unlink($path.DIRECTORY_SEPARATOR.$filename);
    }

    public function test_barcode_encodes_the_given_tracking_number(): void
    {
        $data = Waybill::make(ParcelService::Cj)->state(['tracking_number' => '5856-0852-8903'])->toArray();

        $this->assertSame(Faker::make()->barcode('5856-0852-8903'), $data['barcode']);
    }

    public function test_given_barcode_is_kept(): void
    {
        $data = Waybill::make(ParcelService::Cj)->state(['tracking_number' => '5856-0852-8903', 'barcode' => 'given'])->toArray();

        $this->assertSame('given', $data['barcode']);
    }

    public function test_setting_state_again_replaces_the_previous_state(): void
    {
        $data = Waybill::make(ParcelService::Cj)
            ->state(['site_order_no' => 'first', 'freight_type' => 'B'])
            ->state(['site_order_no' => 'second'])
            ->toArray();

        $this->assertSame('second', $data['site_order_no']);
        $this->assertSame('A', $data['freight_type']);
    }

    public function test_default_format_is_5_inch(): void
    {
        $this->assertSame('5-inch', Waybill::make(ParcelService::Cj)->format()->name);
    }
}
