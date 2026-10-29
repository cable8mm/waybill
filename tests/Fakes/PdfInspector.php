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

    /**
     * Return the placement of the first imported page as [scaleX, scaleY, offsetX, offsetY].
     * Offsets are in millimetres from the bottom-left corner of the page.
     *
     * @return array{float, float, float, float}
     */
    public static function templatePlacement(string $path): array
    {
        preg_match_all('#stream\r?\n(.*?)\r?\nendstream#s', (string) file_get_contents($path), $streams);

        foreach ($streams[1] as $stream) {
            $content = @gzuncompress($stream);

            if ($content !== false && preg_match('#([\d.-]+) 0 0 ([\d.-]+) ([\d.-]+) ([\d.-]+) cm /TPL\d+ Do#', $content, $matrix) === 1) {
                $pointsPerMillimetre = 72 / 25.4;

                return [(float) $matrix[1], (float) $matrix[2], $matrix[3] / $pointsPerMillimetre, $matrix[4] / $pointsPerMillimetre];
            }
        }

        throw new \RuntimeException('No imported page found in '.$path);
    }
}
