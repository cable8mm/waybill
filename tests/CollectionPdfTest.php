<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Support\LabelFormat;
use Cable8mm\Waybill\Support\Mpdf;
use Cable8mm\Waybill\Tests\Fakes\PdfInspector;
use Cable8mm\Waybill\Tests\Fakes\RecordingMpdf;
use Cable8mm\Waybill\Waybill;
use Cable8mm\Waybill\WaybillCollection;
use PHPUnit\Framework\TestCase;

final class CollectionPdfTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'waybill-collection-'.uniqid();
        mkdir($this->path);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->path.DIRECTORY_SEPARATOR.'*') ?: []);
        rmdir($this->path);
    }

    public function test_shared_document_saves_one_page_per_label(): void
    {
        $mpdf = Mpdf::instance();
        $collection = WaybillCollection::of(mpdf: $mpdf);

        for ($i = 0; $i < 5; $i++) {
            $collection->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf));
        }

        $collection->path($this->path)->save('five.pdf');

        $this->assertCount(5, PdfInspector::pages($this->path.DIRECTORY_SEPARATOR.'five.pdf'));
    }

    public function test_pages_follow_insertion_order(): void
    {
        $mpdf = new RecordingMpdf;
        $collection = WaybillCollection::of(mpdf: $mpdf);

        foreach (['1111-0000-0001', '1111-0000-0002', '1111-0000-0003'] as $trackingNumber) {
            $collection->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf)->state(['tracking_number' => $trackingNumber]));
        }

        $collection->path($this->path)->save('ordered.pdf');

        $this->assertMatchesRegularExpression('/0001.*0002.*0003/s', $mpdf->writtenText());
    }

    public function test_download_writes_pdf_bytes(): void
    {
        $mpdf = Mpdf::instance();

        ob_start();
        WaybillCollection::of(mpdf: $mpdf)->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))->download('collection.pdf');
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertStringStartsWith('%PDF', $content);
    }

    public function test_repeat_count_does_not_repeat_pages(): void
    {
        $mpdf = Mpdf::instance();

        WaybillCollection::of(count: 5, mpdf: $mpdf)
            ->add([Waybill::of(ParcelService::Cj, mpdf: $mpdf), Waybill::of(ParcelService::Cj, mpdf: $mpdf)])
            ->path($this->path)
            ->save('repeat.pdf');

        $this->assertCount(2, PdfInspector::pages($this->path.DIRECTORY_SEPARATOR.'repeat.pdf'));
    }

    public function test_label_with_another_document_is_not_in_the_output(): void
    {
        $mpdf = Mpdf::instance();

        WaybillCollection::of(mpdf: $mpdf)
            ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
            ->add(Waybill::of(ParcelService::Cj))
            ->path($this->path)
            ->save('shared-only.pdf');

        $this->assertCount(1, PdfInspector::pages($this->path.DIRECTORY_SEPARATOR.'shared-only.pdf'));
    }

    public function test_collection_without_a_document_uses_the_default_page(): void
    {
        $collection = WaybillCollection::of();
        $mpdf = (new \ReflectionClass($collection))->getProperty('mpdf');
        $mpdf->setAccessible(true);

        $collection->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf->getValue($collection)))->path($this->path)->save('default.pdf');

        $pages = PdfInspector::pages($this->path.DIRECTORY_SEPARATOR.'default.pdf');
        $this->assertEqualsWithDelta(123, $pages[0]['width'], 0.01);
        $this->assertEqualsWithDelta(100, $pages[0]['height'], 0.01);
        $this->assertSame('L', $pages[0]['orientation']);
    }

    public function test_made_collection_cannot_save_or_download(): void
    {
        foreach (['save', 'download'] as $method) {
            try {
                WaybillCollection::make()->add(Waybill::of(ParcelService::Cj))->path($this->path)->{$method}('made.pdf');
                $this->fail($method.' without a document must fail.');
            } catch (\Error $e) {
                $this->assertFileDoesNotExist($this->path.DIRECTORY_SEPARATOR.'made.pdf');
            }
        }
    }

    public function test_label_failure_stops_the_save(): void
    {
        $mpdf = Mpdf::instance();

        try {
            WaybillCollection::of(mpdf: $mpdf)
                ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
                ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf, format: new LabelFormat('8-inch', 100, 150)))
                ->path($this->path)
                ->save('failed.pdf');
            $this->fail('An unsupported label must stop the collection save.');
        } catch (\InvalidArgumentException $e) {
            $this->assertFileDoesNotExist($this->path.DIRECTORY_SEPARATOR.'failed.pdf');
        }
    }
}
