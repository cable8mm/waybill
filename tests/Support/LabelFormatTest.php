<?php

namespace Cable8mm\Waybill\Tests\Support;

use Cable8mm\Waybill\Support\LabelFormat;
use PHPUnit\Framework\TestCase;

final class LabelFormatTest extends TestCase
{
    public function test_it_exposes_the_given_dimensions_and_margins(): void
    {
        $format = new LabelFormat('custom', 120, 80, 1, 2, 3, 4);

        $this->assertSame('custom', $format->name);
        $this->assertSame(120.0, $format->width);
        $this->assertSame(80.0, $format->height);
        $this->assertSame([1.0, 2.0, 3.0, 4.0], [$format->marginLeft, $format->marginRight, $format->marginTop, $format->marginBottom]);
    }

    public function test_omitted_margins_are_zero(): void
    {
        $format = new LabelFormat('custom', 120, 80);

        $this->assertSame([0.0, 0.0, 0.0, 0.0], [$format->marginLeft, $format->marginRight, $format->marginTop, $format->marginBottom]);
    }

    public function test_cj_5_inch_format(): void
    {
        $format = LabelFormat::cj5Inch();

        $this->assertSame('5-inch', $format->name);
        $this->assertSame([123.0, 100.0], [$format->width, $format->height]);
        $this->assertSame([0.0, 0.0, 0.0, 0.0], [$format->marginLeft, $format->marginRight, $format->marginTop, $format->marginBottom]);
    }

    public function test_landscape_format_options(): void
    {
        $this->assertSame(['format' => [100.0, 123.0], 'orientation' => 'L'], (new LabelFormat('l', 123, 100))->mpdfOptions());
    }

    public function test_portrait_format_options(): void
    {
        $this->assertSame(['format' => [100.0, 150.0], 'orientation' => 'P'], (new LabelFormat('p', 100, 150))->mpdfOptions());
    }

    public function test_square_format_is_portrait(): void
    {
        $this->assertSame('P', (new LabelFormat('square', 100, 100))->mpdfOptions()['orientation']);
    }

    public function test_it_rejects_an_empty_name(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new LabelFormat('', 100, 100);
    }

    public function test_it_rejects_non_positive_dimensions(): void
    {
        foreach ([[0, 100], [-1, 100], [100, 0], [100, -1]] as [$width, $height]) {
            try {
                new LabelFormat('invalid', $width, $height);
                $this->fail(sprintf('Expected %s x %s to be rejected.', $width, $height));
            } catch (\InvalidArgumentException $e) {
                $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            }
        }
    }

    public function test_it_cannot_be_changed_after_creation(): void
    {
        $format = LabelFormat::cj5Inch();

        $this->expectException(\Error::class);

        $format->width = 200; // @phpstan-ignore-line
    }
}
