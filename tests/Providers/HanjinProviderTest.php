<?php

namespace Cable8mm\Waybill\Tests\Providers;

use Cable8mm\Waybill\Providers\HanjinProvider;
use Cable8mm\Waybill\Support\LabelFormat;
use Cable8mm\Waybill\Support\Mpdf;
use PHPUnit\Framework\TestCase;

final class HanjinProviderTest extends TestCase
{
    public function test_it_provides_the_three_print_formats(): void
    {
        $provider = new HanjinProvider;
        $formats = $provider->labelFormats();

        $this->assertSame([HanjinProvider::NS, HanjinProvider::NL, HanjinProvider::FS], array_keys($formats));
        $this->assertSame([200.0, 101.5], [$formats[HanjinProvider::NS]->width, $formats[HanjinProvider::NS]->height]);
        $this->assertSame([101.5, 100.0], [$formats[HanjinProvider::NL]->width, $formats[HanjinProvider::NL]->height]);
        $this->assertSame([123.0, 100.0], [$formats[HanjinProvider::FS]->width, $formats[HanjinProvider::FS]->height]);
        $this->assertSame(HanjinProvider::FS, $provider->defaultLabelFormat()->name);
    }

    public function test_it_resolves_templates_and_crop_areas_for_each_format(): void
    {
        $provider = new HanjinProvider;

        foreach ($provider->labelFormats() as $format) {
            $this->assertFileExists($provider->stub($format));
            $this->assertSame([0, 0, $format->width, $format->height], $provider->templateArea($format));
        }
    }

    public function test_mpdf_uses_each_format_page_dimensions(): void
    {
        $provider = new HanjinProvider;

        foreach ($provider->labelFormats() as $format) {
            $mpdf = Mpdf::instance($format);

            $this->assertEqualsWithDelta($format->width, $mpdf->w, 0.01);
            $this->assertEqualsWithDelta($format->height, $mpdf->h, 0.01);
        }
    }

    public function test_it_rejects_unsupported_formats(): void
    {
        $provider = new HanjinProvider;

        $this->expectException(\InvalidArgumentException::class);

        $provider->stub(new LabelFormat('unsupported', 100, 100));
    }
}
