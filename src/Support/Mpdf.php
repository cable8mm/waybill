<?php

namespace Cable8mm\Waybill\Support;

class Mpdf
{
    public static function instance(): \Mpdf\Mpdf
    {
        $config = include __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.php';

        $defaultConfig = (new \Mpdf\Config\ConfigVariables)->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $tempDir = $defaultConfig['tempDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables)->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $configGlobal = [
            'mode' => $config['mode'],
            'format' => $config['format'],
            'orientation' => $config['orientation'],
            'default_font_size' => $config['default_font_size'],
            'default_font' => $config['default_font'],
            'margin_left' => $config['margin_left'],
            'margin_right' => $config['margin_right'],
            'margin_top' => $config['margin_top'],
            'margin_bottom' => $config['margin_bottom'],
            'margin_header' => $config['margin_header'],
            'margin_footer' => $config['margin_footer'],
            'fontDir' => array_merge($fontDirs, [
                $config['custom_font_dir'],
            ]),
            'fontdata' => array_merge($fontData, $config['custom_font_data']),
            'autoScriptToLang' => $config['auto_language_detection'],
            'autoLangToFont' => $config['auto_language_detection'],
            'tempDir' => ($config['temp_dir']) ?: $tempDir,
        ];

        $configMerge = array_merge($configGlobal, $config);

        $mpdf = new \Mpdf\Mpdf(array_merge($defaultConfig, $configMerge));

        $mpdf->SetTitle($config['title']);
        $mpdf->SetSubject($config['subject']);
        $mpdf->SetAuthor($config['author']);
        $mpdf->SetWatermarkText($config['watermark']);
        $mpdf->SetWatermarkImage(
            $config['watermark_image_path'],
            $config['watermark_image_alpha'],
            $config['watermark_image_size'],
            $config['watermark_image_position']
        );
        $mpdf->SetDisplayMode($config['display_mode']);

        $mpdf->PDFA = $config['pdfa'] ?: false;
        $mpdf->PDFAauto = $config['pdfaauto'] ?: false;
        $mpdf->showWatermarkText = $config['show_watermark'];
        $mpdf->showWatermarkImage = $config['show_watermark_image'];
        $mpdf->watermark_font = $config['watermark_font'];
        $mpdf->watermarkTextAlpha = $config['watermark_text_alpha'];
        // use active forms
        $mpdf->useActiveForms = $config['use_active_forms'];

        return $mpdf;
    }
}
