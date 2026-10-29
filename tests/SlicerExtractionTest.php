<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Providers\HanjinProvider;
use Cable8mm\Waybill\Slicer;
use Cable8mm\Waybill\Support\LabelFormat;
use Cable8mm\Waybill\Support\Mpdf;
use Cable8mm\Waybill\Tests\Fakes\PdfInspector;
use Cable8mm\Waybill\Waybill;
use Cable8mm\Waybill\WaybillCollection;
use PHPUnit\Framework\TestCase;

final class SlicerExtractionTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'waybill-slicer-'.uniqid();
        mkdir($this->path);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->path.DIRECTORY_SEPARATOR.'*') ?: []);
        rmdir($this->path);
    }

    public function test_it_extracts_one_page_at_the_label_format_size(): void
    {
        $source = $this->cjSource(5);

        Slicer::of(ParcelService::Cj, 3)->source($source)->save($this->file('one.pdf'));

        $pages = PdfInspector::pages($this->file('one.pdf'));
        $this->assertCount(1, $pages);
        $this->assertEqualsWithDelta(123, $pages[0]['width'], 0.01);
        $this->assertEqualsWithDelta(100, $pages[0]['height'], 0.01);
    }

    public function test_cj_page_is_placed_at_the_origin_without_scaling(): void
    {
        Slicer::of(ParcelService::Cj, 1)->source($this->cjSource(1))->save($this->file('cj.pdf'));

        $this->assertEqualsWithDelta([1, 1, 0, 0], PdfInspector::templatePlacement($this->file('cj.pdf')), 0.001);
    }

    public function test_hanjin_page_is_placed_at_the_origin_without_scaling(): void
    {
        $provider = new HanjinProvider;
        $format = $provider->labelFormats()[HanjinProvider::NL];
        Waybill::of($provider, format: $format)->path($this->path)->save('hanjin-source.pdf');

        Slicer::of($provider, 1, $format)->source($this->file('hanjin-source.pdf'))->save($this->file('hanjin.pdf'));

        $pages = PdfInspector::pages($this->file('hanjin.pdf'));
        $this->assertEqualsWithDelta(101.5, $pages[0]['width'], 0.01);
        $this->assertEqualsWithDelta(100, $pages[0]['height'], 0.01);
        $this->assertEqualsWithDelta([1, 1, 0, 0], PdfInspector::templatePlacement($this->file('hanjin.pdf')), 0.001);
    }

    public function test_download_writes_pdf_bytes(): void
    {
        $source = $this->cjSource(1);

        ob_start();
        Slicer::of(ParcelService::Cj, 1)->source($source)->download($this->file('download.pdf'));
        $content = ob_get_contents();
        ob_end_clean();

        $this->assertStringStartsWith('%PDF', $content);
    }

    public function test_download_is_named_with_the_base_name(): void
    {
        $source = $this->cjSource(1);
        $script = $this->file('download.php');
        file_put_contents($script, sprintf(
            '<?php require %s; Cable8mm\Waybill\Slicer::of(Cable8mm\Waybill\Enums\ParcelService::Cj, 1)->source(%s)->download(%s);',
            var_export(dirname(__DIR__).'/vendor/autoload.php', true),
            var_export($source, true),
            var_export($this->path.'/nested/named.pdf', true),
        ));

        $headers = $this->serve($script);

        $this->assertMatchesRegularExpression('/^Content-Disposition: attachment; filename="named\.pdf"/mi', implode("\n", $headers));
    }

    public function test_selected_page_can_be_replaced(): void
    {
        $source = $this->cjSource(3);

        Slicer::of(ParcelService::Cj, 1)->page(3)->source($source)->save($this->file('third.pdf'));

        $this->assertCount(1, PdfInspector::pages($this->file('third.pdf')));
    }

    public function test_unsupported_format_is_rejected(): void
    {
        $source = $this->cjSource(1);

        $this->assertExtractionFails(
            fn () => Slicer::of(ParcelService::Cj, 1, new LabelFormat('8-inch', 100, 150))->source($source)->save($this->file('out.pdf')),
            \InvalidArgumentException::class,
        );
    }

    public function test_missing_source_is_rejected(): void
    {
        $this->assertExtractionFails(fn () => Slicer::of(ParcelService::Cj, 1)->save($this->file('out.pdf')), \Error::class);
    }

    public function test_nonexistent_source_is_rejected(): void
    {
        $this->assertExtractionFails(
            fn () => @Slicer::of(ParcelService::Cj, 1)->source($this->file('missing.pdf'))->save($this->file('out.pdf')),
            \Exception::class,
        );
    }

    public function test_page_beyond_the_source_is_rejected(): void
    {
        $source = $this->cjSource(2);

        $this->assertExtractionFails(
            fn () => Slicer::of(ParcelService::Cj, 3)->source($source)->save($this->file('out.pdf')),
            \Exception::class,
        );
    }

    private function cjSource(int $pages): string
    {
        $mpdf = Mpdf::instance();
        $collection = WaybillCollection::of(mpdf: $mpdf);

        for ($i = 0; $i < $pages; $i++) {
            $collection->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf));
        }

        $collection->path($this->path)->save('source.pdf');

        return $this->file('source.pdf');
    }

    private function file(string $name): string
    {
        return $this->path.DIRECTORY_SEPARATOR.$name;
    }

    /** @param class-string<\Throwable> $expected */
    private function assertExtractionFails(callable $extract, string $expected): void
    {
        try {
            $extract();
            $this->fail('Extraction must fail.');
        } catch (\Throwable $e) {
            $this->assertInstanceOf($expected, $e);
            $this->assertFileDoesNotExist($this->file('out.pdf'));
        }
    }

    /** @return array<int, string> */
    private function serve(string $script): array
    {
        $port = random_int(20000, 40000);
        $server = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:'.$port, $script],
            [1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
        );

        try {
            for ($attempt = 0; $attempt < 50; $attempt++) {
                $socket = @fsockopen('127.0.0.1', $port);

                if ($socket !== false) {
                    fwrite($socket, "GET / HTTP/1.0\r\nHost: 127.0.0.1\r\n\r\n");
                    $response = stream_get_contents($socket);
                    fclose($socket);

                    return explode("\r\n", strstr($response, "\r\n\r\n", true));
                }

                usleep(100000);
            }

            $this->fail('The download server did not respond.');
        } finally {
            proc_terminate($server);
            proc_close($server);
        }
    }
}
