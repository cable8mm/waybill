<?php

namespace Cable8mm\Waybill;

use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Support\Mpdf;
use Mpdf\Output\Destination;

/**
 * Slicer for catching one waybill on the waybills
 */
class Slicer
{
    /**
     * The source path to slice the waybills
     */
    private string $source;

    /**
     * Constructor
     */
    private function __construct(
        private ParcelService $parcelService,
        /**
         * The page to slice the waybills
         */
        private int $page
    ) {
        //
    }

    /**
     * Setter for $source
     *
     * @param  string  $source  The source to save the waybills
     * @return static The method returns self instance
     *
     * @example $slicer = Slicer::of(ParcelService::Cj)->source(realpath(__DIR__.'/../dist'))->...
     */
    public function source(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Setter for $page
     *
     * @param  int  $page  The page to save the waybills
     * @return static The method returns self instance
     *
     * @example $slicer = Slicer::of(ParcelService::Cj)->page(1)->...
     */
    public function page(int $page): static
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Save the page of waybills
     *
     * @param  string  $path  The path to save the waybills
     * @param  \Mpdf\Output\Destination  $destination  The destination
     * @return mixed The method returns
     */
    public function save(string $path, $destination = Destination::FILE): mixed
    {
        $mpdf = Mpdf::instance();

        $mpdf->SetSourceFile($this->source);

        $tplId = $mpdf->ImportPage($this->page);

        $mpdf->UseTemplate(
            $tplId,
            $this->parcelService->templateArea()[0],
            $this->parcelService->templateArea()[1],
            $this->parcelService->templateArea()[2],
            $this->parcelService->templateArea()[3]
        );

        return $destination == Destination::FILE
            ? $mpdf->Output($path, $destination)
            : $mpdf->Output(basename($path), $destination);
    }

    /**
     * Download the page of waybills
     *
     * @param  string  $path  The path to save the waybills
     * @return mixed The method returns
     */
    public function download(string $path): mixed
    {
        return $this->save($path, Destination::DOWNLOAD);
    }

    /**
     * Factory method to create an instance of Slicer
     *
     * @param  ParcelService  $parcelService  The waybills type
     * @param  int  $page  The page to save the waybills
     * @return static The method returns the Slicer instance
     *
     * @example Slicer::of(ParcelService::Cj)->...
     */
    public static function of(ParcelService $parcelService, int $page): static
    {
        return new static($parcelService, $page);
    }
}
