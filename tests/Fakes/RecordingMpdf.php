<?php

namespace Cable8mm\Waybill\Tests\Fakes;

use Cable8mm\Waybill\Support\LabelFormat;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;

/**
 * A PDF document that records every HTML fragment written to it.
 */
final class RecordingMpdf extends Mpdf
{
    /** @var array<int, string> */
    public array $written = [];

    public function __construct(?LabelFormat $format = null)
    {
        $config = include __DIR__.'/../../config.php';
        $format ??= LabelFormat::cj5Inch();

        parent::__construct($format->mpdfOptions() + [
            'mode' => 'utf-8',
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
            'default_font' => $config['default_font'],
            'fontDir' => array_merge((new ConfigVariables)->getDefaults()['fontDir'], [$config['custom_font_dir']]),
            'fontdata' => array_merge((new FontVariables)->getDefaults()['fontdata'], $config['custom_font_data']),
            'tempDir' => $config['temp_dir'],
        ]);
    }

    public function WriteHTML($html, $mode = HTMLParserMode::DEFAULT_MODE, $init = true, $close = true)
    {
        $this->written[] = (string) $html;

        return parent::WriteHTML($html, $mode, $init, $close);
    }

    /** Return the text content of every written fragment with markup removed. */
    public function writtenText(): string
    {
        return html_entity_decode(strip_tags(preg_replace('#<style.*?</style>#s', '', implode("\n", $this->written))), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
