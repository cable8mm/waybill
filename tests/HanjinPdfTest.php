<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Providers\HanjinProvider;
use Cable8mm\Waybill\Tests\Fakes\PdfInspector;
use Cable8mm\Waybill\Tests\Fakes\RecordingMpdf;
use Cable8mm\Waybill\Waybill;
use PHPUnit\Framework\TestCase;

final class HanjinPdfTest extends TestCase
{
    private const STATE = [
        'tracking_number' => '135792468024',
        'terminal_code' => '4321',
        'terminal_name' => 'TERMINALNAME',
        'hub_code' => 'HUBCODE',
        'hub_number' => 'HUBNUMBER',
        'middle_code' => 'MIDDLECODE',
        'center_code' => 'CENTERCODE',
        'center_name' => 'CENTERNAME',
        'group_rank' => 'GROUPRANK',
        'worker_name' => 'WORKERNAME',
        'worker_code' => 'WORKERCODE',
        'sender' => ['name' => 'Sender', 'phone' => '02-111-2222', 'address' => '서울시 중구 남대문로 63 한진빌딩'],
        'receiver' => ['name' => 'Receiver', 'phone' => '010-3333-4444', 'address' => '서울시 중구 소공동 51 10층'],
        'item_name' => 'ITEMNAME',
        'item_quantity' => 7,
        'order_number' => 'ORDERNUMBER',
        'print_date' => '2026-05-06',
        'freight_type' => 'FREIGHTTYPE',
        'delivery_fee' => '3,500',
        'delivery_request' => 'DELIVERYREQUEST',
        'region_name' => 'REGIONNAME',
    ];

    private const NOT_RENDERED = [
        HanjinProvider::NS => ['worker_name', 'worker_code'],
        HanjinProvider::NL => ['group_rank'],
        HanjinProvider::FS => [],
    ];

    public function test_each_format_saves_its_page_size(): void
    {
        $provider = new HanjinProvider;
        $path = sys_get_temp_dir();
        $expected = [HanjinProvider::NS => [200, 101.5], HanjinProvider::NL => [101.5, 100], HanjinProvider::FS => [123, 100]];

        foreach ($provider->labelFormats() as $name => $format) {
            $filename = 'hanjin-'.$name.'.pdf';
            Waybill::of($provider, format: $format)->path($path)->save($filename);

            $pages = PdfInspector::pages($path.DIRECTORY_SEPARATOR.$filename);
            unlink($path.DIRECTORY_SEPARATOR.$filename);

            $this->assertCount(1, $pages, $name);
            $this->assertEqualsWithDelta($expected[$name][0], $pages[0]['width'], 0.01, $name);
            $this->assertEqualsWithDelta($expected[$name][1], $pages[0]['height'], 0.01, $name);
        }
    }

    public function test_each_format_renders_its_field_slots(): void
    {
        $provider = new HanjinProvider;
        $masked = Waybill::make($provider)->state(self::STATE)->toArray();

        foreach ($provider->labelFormats() as $name => $format) {
            $mpdf = new RecordingMpdf($format);
            Waybill::make($provider, $format)->mpdf($mpdf)->state(self::STATE)->write();
            $text = $mpdf->writtenText();

            foreach (array_keys(self::STATE) as $key) {
                $values = (array) $masked[$key];

                foreach ($values as $value) {
                    if (in_array($key, self::NOT_RENDERED[$name], true)) {
                        $this->assertStringNotContainsString((string) $value, $text, $name.' '.$key);
                    } else {
                        $this->assertStringContainsString((string) $value, $text, $name.' '.$key);
                    }
                }
            }
        }
    }

    public function test_each_format_renders_two_barcodes(): void
    {
        $provider = new HanjinProvider;

        foreach ($provider->labelFormats() as $name => $format) {
            $mpdf = new RecordingMpdf($format);
            Waybill::make($provider, $format)->mpdf($mpdf)->write();

            $this->assertSame(2, substr_count(implode('', $mpdf->written), 'data:image/png;base64,'), $name);
        }
    }

    public function test_string_conversion_returns_the_provider_name(): void
    {
        $this->assertSame('한진택배', (string) Waybill::make(new HanjinProvider));
    }
}
