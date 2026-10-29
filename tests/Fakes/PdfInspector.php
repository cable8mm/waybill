<?php

namespace Cable8mm\Waybill\Tests\Fakes;

use Cable8mm\Waybill\Support\Mpdf;

/**
 * Reads page count and page sizes of a saved PDF file.
 */
final class PdfInspector
{
    /** @return array<int, array{width: float, height: float, orientation: string}> */
    public static function pages(string $path): array
    {
        $mpdf = Mpdf::instance();
        $count = $mpdf->setSourceFile($path);
        $pages = [];

        for ($page = 1; $page <= $count; $page++) {
            $size = $mpdf->getTemplateSize($mpdf->importPage($page));
            $pages[] = ['width' => (float) $size['width'], 'height' => (float) $size['height'], 'orientation' => $size['orientation']];
        }

        return $pages;
    }
}
