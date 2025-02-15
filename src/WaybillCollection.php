<?php

namespace Cable8mm\Waybill;

use Cable8mm\Waybill\Collections\Waybills;
use Cable8mm\Waybill\Support\Mpdf as SupportMpdf;
use Mpdf\Mpdf;

/**
 * Main entry point for creating new waybills
 */
class WaybillCollection
{
    /**
     * @var string The path to save the waybills
     */
    private string $path;

    /**
     * Constructor
     */
    private function __construct(
        /**
         * @var The waybill collection class
         */
        private Waybills $waybills,
        /**
         * The count of the waybills
         */
        private int $count = 1,
        /**
         * Mpdf instance
         */
        private ?Mpdf $mpdf = null
    ) {
        //
    }

    public function add(Waybill $waybill): static
    {
        $this->waybills->add($waybill);

        return $this;
    }

    /**
     * Setter for $path
     *
     * @param  string  $path  The path to save the waybills
     * @return static The method returns self instance
     *
     * @example $waybillCollection = WaybillCollection::of($waybill, 5)->path(realpath(__DIR__.'/../dist'))->...
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
     *
     * @example $waybillCollection = WaybillCollection::of($waybill, 5)->toArray();
     */
    public function toArray(): array
    {
        return $this->waybills->toArray($this->count);
    }

    /**
     * Save the waybills data to PDF
     *
     * @param  string  $filename  A filename to create
     *
     * @example $waybillCollection = WaybillCollection::of($waybill, 5)->path(realpath(__DIR__.'/../dist'))->save('test.pdf');
     */
    public function save(string $filename = 'waybills.pdf'): mixed
    {
        $this->waybills->write();

        $path = empty($this->path) ? '' : $this->path.DIRECTORY_SEPARATOR;

        return $this->mpdf->Output($path.$filename, false);
    }

    /**
     * Download the waybills data to PDF
     *
     * @param  string  $filename  A filename to create
     *
     * @example $waybillCollection = WaybillCollection::of($waybill, 5)->path(realpath(__DIR__.'/../dist'))->download('test.pdf');
     */
    public function download(string $filename = 'waybills.pdf'): mixed
    {
        $this->waybills->write();

        return $this->mpdf->Output($filename, true);
    }

    /**
     * Factory method to create an instance of WaybillCollection
     *
     * @return static The method returns the WaybillCollection instance
     *
     * @example WaybillCollection::of($waybill, 5, $mpdf)->...
     */
    public static function of(?Waybills $waybills = null, int $count = 1, ?Mpdf $mpdf = null): static
    {
        $waybills = $waybills ?? new Waybills;

        $mpdf = $mpdf ?? SupportMpdf::instance();

        return new static($waybills, $count, $mpdf);
    }

    /**
     * Factory method to create an instance without mpdf of WaybillCollection
     *
     * @return static The method returns the WaybillCollection instance
     *
     * @example WaybillCollection::make($waybill, 5)->mpdf($mpdf)->...
     */
    public static function make(?Waybills $waybills = null, int $count = 1): static
    {
        if (is_null($waybills)) {
            $waybills = new Waybills;
        }

        return new static($waybills, $count);
    }
}
