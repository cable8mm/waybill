<?php

namespace Cable8mm\Waybill\Tests\Support;

use Cable8mm\Waybill\Support\LabelFormat;
use Cable8mm\Waybill\Support\Mpdf;
use PHPUnit\Framework\TestCase;

final class MpdfTest extends TestCase
{
    public function test_it_uses_the_configuration_page_without_a_label_format(): void
    {
        $mpdf = Mpdf::instance();

        $this->assertEqualsWithDelta(123, $mpdf->w, 0.01);
        $this->assertEqualsWithDelta(100, $mpdf->h, 0.01);
        $this->assertSame('L', $mpdf->DefOrientation);
    }

    public function test_it_uses_the_label_format_page_size_and_orientation(): void
    {
        $landscape = Mpdf::instance(new LabelFormat('landscape', 150, 100));
        $portrait = Mpdf::instance(new LabelFormat('portrait', 100, 150));

        $this->assertEqualsWithDelta(150, $landscape->w, 0.01);
        $this->assertEqualsWithDelta(100, $landscape->h, 0.01);
        $this->assertSame('L', $landscape->DefOrientation);
        $this->assertEqualsWithDelta(100, $portrait->w, 0.01);
        $this->assertEqualsWithDelta(150, $portrait->h, 0.01);
        $this->assertSame('P', $portrait->DefOrientation);
    }

    public function test_it_uses_the_label_format_margins_and_no_header_or_footer_margin(): void
    {
        $mpdf = Mpdf::instance(new LabelFormat('margins', 150, 100, 1, 2, 3, 4));

        $this->assertEqualsWithDelta(1, $mpdf->lMargin, 0.01);
        $this->assertEqualsWithDelta(2, $mpdf->rMargin, 0.01);
        $this->assertEqualsWithDelta(3, $mpdf->tMargin, 0.01);
        $this->assertEqualsWithDelta(4, $mpdf->bMargin, 0.01);
        $this->assertEquals(0, $mpdf->margin_header);
        $this->assertEquals(0, $mpdf->margin_footer);
    }

    public function test_it_embeds_the_bundled_korean_fonts(): void
    {
        foreach ([null, LabelFormat::cj5Inch()] as $format) {
            $mpdf = Mpdf::instance($format);
            $mpdf->WriteHTML('<p>한글</p><p style="font-family: nanumgothic">한진</p>');

            $pdf = $mpdf->Output('', 'S');

            $this->assertMatchesRegularExpression('#/BaseFont /[A-Z]+\+NanumBarunGothic#', $pdf);
            $this->assertMatchesRegularExpression('#/BaseFont /[A-Z]+\+NanumGothic#', $pdf);
        }
    }
}
