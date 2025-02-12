<?php

namespace Cable8mm\Waybill;

use Cable8mm\StubTemplate\Stub;
use Cable8mm\Waybill\Enums\ParcelService;
use Mpdf\Mpdf;

/**
 * Main entry point for creating new waybills
 */
class Waybill
{
    /**
     * The state of the waybills
     */
    private array $state = [];

    /**
     * The path to save the waybills
     */
    private string $path;

    /**
     * ParcelService instance
     */
    private ParcelService $parcelService;

    /**
     * Mpdf instance
     */
    private Mpdf $mpdf;

    /**
     * Constructor
     */
    private function __construct(
        ParcelService $parcelService
    ) {
        $this->parcelService = $parcelService;

        $config = include __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config.php';

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

        $this->mpdf = new Mpdf(array_merge($defaultConfig, $configMerge));

        $this->mpdf->SetTitle($config['title']);
        $this->mpdf->SetSubject($config['subject']);
        $this->mpdf->SetAuthor($config['author']);
        $this->mpdf->SetWatermarkText($config['watermark']);
        $this->mpdf->SetWatermarkImage(
            $config['watermark_image_path'],
            $config['watermark_image_alpha'],
            $config['watermark_image_size'],
            $config['watermark_image_position']
        );
        $this->mpdf->SetDisplayMode($config['display_mode']);

        $this->mpdf->PDFA = $config['pdfa'] ?: false;
        $this->mpdf->PDFAauto = $config['pdfaauto'] ?: false;
        $this->mpdf->showWatermarkText = $config['show_watermark'];
        $this->mpdf->showWatermarkImage = $config['show_watermark_image'];
        $this->mpdf->watermark_font = $config['watermark_font'];
        $this->mpdf->watermarkTextAlpha = $config['watermark_text_alpha'];
        // use active forms
        $this->mpdf->useActiveForms = $config['use_active_forms'];
    }

    /**
     * Setter for $state
     *
     * @param  array  $state  The state of the waybills
     * @return static The method returns self instance
     */
    public function state(array $state): static
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Setter for $path
     *
     * @param  string  $path  The path to save the waybills
     * @return static The method returns self instance
     */
    public function path(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Export the waybills data to array or CSV
     *
     * @return array The method returns the waybills data as array or CSV string
     */
    public function toArray(): array
    {
        return $this->parcelService->factoryClass()::make()->state($this->state)->create();
    }

    /**
     * Save the waybills data to PDF
     *
     * @param  string  $filename  A filename to create
     */
    public function save(string $filename = 'waybills.pdf'): mixed
    {
        $this->mpdf->WriteHTML(
            $html = Stub::of(
                $this->parcelService->stub(),
                $this->parcelService->factoryClass()::make()->definition()
            )->render()
        );

        $path = empty($this->path) ? '' : $this->path.DIRECTORY_SEPARATOR;

        return $this->mpdf->Output($path.$filename, 'F');
    }

    /**
     * Magic method to convert the waybills type to string
     *
     * @return string The method returns the waybills type as string
     */
    public function __toString()
    {
        return $this->parcelService->value;
    }

    /**
     * Factory method to create an instance of OrderSheet
     *
     * @param  ParcelService  $parcelService  The waybills type
     * @return static The method returns the OrderSheet instance
     */
    public static function of(ParcelService $parcelService): static
    {
        return new static($parcelService);
    }
}
