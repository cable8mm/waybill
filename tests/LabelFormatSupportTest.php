<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Providers\HanjinProvider;
use Cable8mm\Waybill\Slicer;
use Cable8mm\Waybill\Support\LabelFormat;
use Cable8mm\Waybill\Waybill;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class LabelFormatSupportTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'waybill-format-'.uniqid();
        mkdir($this->path);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->path.DIRECTORY_SEPARATOR.'*') ?: []);
        rmdir($this->path);
    }

    public function test_cj_supports_only_5_inch(): void
    {
        $this->assertSame(['5-inch'], array_keys(ParcelService::Cj->labelFormats()));
        $this->assertSame('5-inch', ParcelService::Cj->defaultLabelFormat()->name);
    }

    public function test_hanjin_supports_ns_nl_and_fs(): void
    {
        $provider = new HanjinProvider;
        $formats = $provider->labelFormats();

        $this->assertSame(['NS', 'NL', 'FS'], array_keys($formats));
        $this->assertSame([200.0, 101.5], [$formats['NS']->width, $formats['NS']->height]);
        $this->assertSame([101.5, 100.0], [$formats['NL']->width, $formats['NL']->height]);
        $this->assertSame([123.0, 100.0], [$formats['FS']->width, $formats['FS']->height]);
        $this->assertSame('FS', $provider->defaultLabelFormat()->name);
    }

    public function test_labels_and_slicers_use_the_provider_default_format(): void
    {
        $slicer = Slicer::of(new HanjinProvider, 1);
        $format = (new ReflectionClass($slicer))->getProperty('format');
        $format->setAccessible(true);

        $this->assertSame('5-inch', Waybill::of(ParcelService::Cj)->format()->name);
        $this->assertSame('FS', Waybill::make(new HanjinProvider)->format()->name);
        $this->assertSame('FS', $format->getValue($slicer)->name);
    }

    public function test_support_is_decided_by_name_only(): void
    {
        $provider = new HanjinProvider;
        $format = new LabelFormat('NS', 10, 20);

        $this->assertFileExists($provider->stub($format));
        $this->assertSame([0, 0, 10.0, 20.0], $provider->templateArea($format));
    }

    public function test_cj_rejects_an_unsupported_format_on_save(): void
    {
        $this->assertSaveRejected(Waybill::of(ParcelService::Cj, format: new LabelFormat('8-inch', 100, 150)));
    }

    public function test_hanjin_rejects_the_cj_format_on_save(): void
    {
        $this->assertSaveRejected(Waybill::of(new HanjinProvider, format: LabelFormat::cj5Inch()));
    }

    public function test_unsupported_format_is_not_rejected_on_creation(): void
    {
        $waybill = Waybill::of(ParcelService::Cj, format: new LabelFormat('8-inch', 100, 150));

        $this->assertSame('8-inch', $waybill->format()->name);
    }

    public function test_missing_template_for_a_supported_format_fails_on_save(): void
    {
        $provider = new HanjinProvider;
        $stub = $provider->stub($provider->labelFormats()['NL']);
        $moved = $stub.'.moved';
        rename($stub, $moved);

        try {
            $this->assertSaveRejected(Waybill::of($provider, format: $provider->labelFormats()['NL']));
        } finally {
            rename($moved, $stub);
        }
    }

    private function assertSaveRejected(Waybill $waybill): void
    {
        try {
            $waybill->path($this->path)->save('rejected.pdf');
            $this->fail('Saving with an unsupported or missing template must fail.');
        } catch (\InvalidArgumentException $e) {
            $this->assertFileDoesNotExist($this->path.DIRECTORY_SEPARATOR.'rejected.pdf');
        }
    }
}
