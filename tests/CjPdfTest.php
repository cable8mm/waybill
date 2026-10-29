<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Support\Mpdf;
use Cable8mm\Waybill\Tests\Fakes\PdfInspector;
use Cable8mm\Waybill\Tests\Fakes\RecordingMpdf;
use Cable8mm\Waybill\Waybill;
use PHPUnit\Framework\TestCase;

final class CjPdfTest extends TestCase
{
    private const STATE = [
        'city' => ['code' => 'CITYCODE1', 'name' => 'CITYNAME1'],
        'region' => ['code' => 'REGIONCODE1', 'name' => 'REGIONNAME1'],
        'line_items' => ['ITEMONE x 1', 'ITEMTWO x 2'],
        'seller' => ['name' => 'SELLERNAME', 'phone' => '02-111-1111', 'site' => 'SELLERSITE', 'address' => 'SELLERADDRESS'],
        'receiver' => ['name' => 'RECEIVERNAME', 'phone' => '02-222-2222', 'cell_phone' => '010-3333-3333', 'address' => 'RECEIVERADDRESS'],
        'printed' => '2026-01-02',
        'total_printed_count' => 4321,
        'site_order_no' => 'ORDER98765',
        'tracking_number' => '1111-2222-3333',
        'delivery_worker' => 'WORKERNAME',
        'settlement_type' => 'SETTLEMENT',
        'print_date' => '2026-03-04',
        'box_quantity' => 9,
        'freight_type' => 'FREIGHT',
    ];

    private string $path;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'waybill-cj-'.uniqid();
        mkdir($this->path);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->path.DIRECTORY_SEPARATOR.'*') ?: []);
        rmdir($this->path);
    }

    public function test_it_saves_to_the_directory_path(): void
    {
        Waybill::of(ParcelService::Cj)->path($this->path)->save('cj.pdf');

        $this->assertFileExists($this->path.DIRECTORY_SEPARATOR.'cj.pdf');
    }

    public function test_it_saves_to_the_filename_without_a_directory_path(): void
    {
        $cwd = getcwd();
        chdir($this->path);

        try {
            Waybill::of(ParcelService::Cj)->save('relative.pdf');
        } finally {
            chdir($cwd);
        }

        $this->assertFileExists($this->path.DIRECTORY_SEPARATOR.'relative.pdf');
    }

    public function test_default_filename(): void
    {
        Waybill::of(ParcelService::Cj)->path($this->path)->save();

        $this->assertFileExists($this->path.DIRECTORY_SEPARATOR.'waybills.pdf');
    }

    public function test_saved_pdf_has_one_5_inch_landscape_page(): void
    {
        Waybill::of(ParcelService::Cj)->path($this->path)->save('cj.pdf');

        $pages = PdfInspector::pages($this->path.DIRECTORY_SEPARATOR.'cj.pdf');

        $this->assertCount(1, $pages);
        $this->assertEqualsWithDelta(123, $pages[0]['width'], 0.01);
        $this->assertEqualsWithDelta(100, $pages[0]['height'], 0.01);
        $this->assertSame('L', $pages[0]['orientation']);
    }

    public function test_download_writes_pdf_bytes(): void
    {
        ob_start();
        Waybill::of(ParcelService::Cj)->download('cj.pdf');
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertStringStartsWith('%PDF', $content);
    }

    public function test_every_field_slot_renders_its_value(): void
    {
        $mpdf = new RecordingMpdf;

        Waybill::make(ParcelService::Cj)->mpdf($mpdf)->state(self::STATE)->path($this->path)->save('cj.pdf');

        $text = $mpdf->writtenText();
        $values = self::STATE;
        array_walk_recursive($values, function ($value) use ($text): void {
            $this->assertStringContainsString((string) $value, $text);
        });
    }

    public function test_each_line_item_is_on_its_own_line(): void
    {
        $mpdf = new RecordingMpdf;

        Waybill::make(ParcelService::Cj)->mpdf($mpdf)->state(self::STATE)->write();

        $this->assertStringContainsString('ITEMONE x 1<br>ITEMTWO x 2<br>', implode('', $mpdf->written));
    }

    public function test_routing_marker_is_rendered(): void
    {
        $mpdf = new RecordingMpdf;

        Waybill::make(ParcelService::Cj)->mpdf($mpdf)->write();

        $this->assertStringContainsString('◆', $mpdf->writtenText());
    }

    public function test_each_render_appends_one_page(): void
    {
        $mpdf = Mpdf::instance();
        $waybill = Waybill::make(ParcelService::Cj)->mpdf($mpdf);

        $waybill->write();
        $waybill->path($this->path)->save('two.pdf');

        $this->assertCount(2, PdfInspector::pages($this->path.DIRECTORY_SEPARATOR.'two.pdf'));
    }

    public function test_direct_record_bypasses_defaults_and_state(): void
    {
        $mpdf = new RecordingMpdf;
        $record = self::STATE + ['barcode' => ''];

        Waybill::make(ParcelService::Cj)->mpdf($mpdf)->state(['site_order_no' => 'STATEORDER'])->write($record);

        $this->assertStringContainsString('ORDER98765', $mpdf->writtenText());
        $this->assertStringNotContainsString('STATEORDER', $mpdf->writtenText());
    }

    public function test_string_conversion_returns_the_provider_name(): void
    {
        $this->assertSame('CJ택배', (string) Waybill::make(ParcelService::Cj));
    }

    public function test_made_label_with_an_attached_document_saves(): void
    {
        Waybill::make(ParcelService::Cj)->mpdf(Mpdf::instance())->path($this->path)->save('made.pdf');

        $this->assertFileExists($this->path.DIRECTORY_SEPARATOR.'made.pdf');
    }

    public function test_made_label_without_a_document_fails_to_save(): void
    {
        try {
            Waybill::make(ParcelService::Cj)->path($this->path)->save('missing.pdf');
            $this->fail('Saving without a document must fail.');
        } catch (\Error $e) {
            $this->assertFileDoesNotExist($this->path.DIRECTORY_SEPARATOR.'missing.pdf');
        }
    }
}
