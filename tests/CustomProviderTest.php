<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Slicer;
use Cable8mm\Waybill\Support\LabelFormat;
use Cable8mm\Waybill\Support\Mpdf;
use Cable8mm\Waybill\Tests\Fakes\ExampleProvider;
use Cable8mm\Waybill\Tests\Fakes\PdfInspector;
use Cable8mm\Waybill\Tests\Fakes\RecordingMpdf;
use Cable8mm\Waybill\Waybill;
use Cable8mm\Waybill\WaybillCollection;
use PHPUnit\Framework\TestCase;

final class CustomProviderTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'waybill-custom-'.uniqid();
        mkdir($this->path);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->path.DIRECTORY_SEPARATOR.'*') ?: []);
        rmdir($this->path);
    }

    public function test_it_renders_the_provider_data_with_its_template(): void
    {
        $provider = new ExampleProvider;
        $mpdf = new RecordingMpdf($provider->defaultLabelFormat());

        Waybill::make($provider)->mpdf($mpdf)->path($this->path)->save('custom.pdf');

        $this->assertFileExists($this->file('custom.pdf'));
        $this->assertStringContainsString('EXAMPLE-TRACKING', $mpdf->writtenText());
        $this->assertStringContainsString('EXAMPLE-RECIPIENT', $mpdf->writtenText());
    }

    public function test_saved_page_uses_the_provider_default_format(): void
    {
        Waybill::of(new ExampleProvider)->path($this->path)->save('custom.pdf');

        $pages = PdfInspector::pages($this->file('custom.pdf'));
        $this->assertEqualsWithDelta(90, $pages[0]['width'], 0.01);
        $this->assertEqualsWithDelta(60, $pages[0]['height'], 0.01);
    }

    public function test_string_conversion_returns_the_provider_name(): void
    {
        $this->assertSame('Example Carrier', (string) Waybill::make(new ExampleProvider));
    }

    public function test_data_export_merges_state(): void
    {
        $data = Waybill::make(new ExampleProvider)->state(['recipient' => 'GIVEN'])->toArray();

        $this->assertSame(['tracking_number' => 'EXAMPLE-TRACKING', 'recipient' => 'GIVEN'], $data);
    }

    public function test_collection_saves_one_page_per_label(): void
    {
        $provider = new ExampleProvider;
        $mpdf = Mpdf::instance($provider->defaultLabelFormat());

        WaybillCollection::of(mpdf: $mpdf)
            ->add([Waybill::of($provider, mpdf: $mpdf), Waybill::of($provider, mpdf: $mpdf)])
            ->path($this->path)
            ->save('collection.pdf');

        $this->assertCount(2, PdfInspector::pages($this->file('collection.pdf')));
    }

    public function test_extraction_uses_the_provider_template_area(): void
    {
        $provider = new ExampleProvider;
        Waybill::of($provider)->path($this->path)->save('source.pdf');

        Slicer::of($provider, 1)->source($this->file('source.pdf'))->save($this->file('sliced.pdf'));

        $this->assertEqualsWithDelta([80 / 90, 50 / 60, 5, 5], PdfInspector::templatePlacement($this->file('sliced.pdf')), 0.001);
    }

    public function test_unsupported_format_raises_the_provider_error(): void
    {
        try {
            Waybill::of(new ExampleProvider, format: new LabelFormat('other', 90, 60))->path($this->path)->save('rejected.pdf');
            $this->fail('An unsupported format must be rejected.');
        } catch (\DomainException $e) {
            $this->assertFileDoesNotExist($this->file('rejected.pdf'));
        }
    }

    private function file(string $name): string
    {
        return $this->path.DIRECTORY_SEPARATOR.$name;
    }
}
