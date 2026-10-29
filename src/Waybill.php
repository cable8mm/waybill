<?php

namespace Cable8mm\Waybill;

use Cable8mm\StubTemplate\Stub;
use Cable8mm\Waybill\Contracts\WaybillProvider;
use Cable8mm\Waybill\Support\LabelFormat;
use Cable8mm\Waybill\Support\Mpdf as SupportMpdf;
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
    private string $path = '';

    /**
     * Constructor
     */
    private function __construct(
        /**
         * Carrier-specific provider
         */
        private WaybillProvider $parcelService,
        private LabelFormat $format,
        /**
         * Mpdf instance
         */
        private ?Mpdf $mpdf = null
    ) {
        //
    }

    /**
     * Run WriteHTML function
     *
     * @param  null|array<string,Tvalue>  $args  The array of arguments for a model or table fields
     */
    public function write(?array $args = null): void
    {
        $data = [];

        if (! is_null($args)) {
            $data = $args;
        } elseif (! empty($this->state)) {
            $data = $this->parcelService->factoryClass()::make()->state($this->state)->create();
        } else {
            $data = $this->parcelService->factoryClass()::make()->create();
        }

        $this->mpdf->WriteHTML(
            Stub::of(
                $this->parcelService->stub($this->format),
                $data
            )->render()
        );
    }

    /**
     * Setter for $mpdf
     *
     * @param  Mpdf  $mpdf  The mpdf of the waybills
     * @return static The method returns self instance
     *
     * @example $waybill = Waybill::of(ParcelService::Cj)->mpdf(SupportMpdf::instance())->...
     */
    public function mpdf(Mpdf $mpdf): static
    {
        $this->mpdf = $mpdf;

        return $this;
    }

    /**
     * Setter for $state
     *
     * @param  array  $state  The state of the waybills
     * @return static The method returns self instance
     *
     * @example $waybill = Waybill::of(ParcelService::Cj)->state(['total_printed_count' => '581'])->...
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
     *
     * @example $waybill = Waybill::of(ParcelService::Cj)->path(realpath(__DIR__.'/../dist'))->...
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
     * @example $waybill = Waybill::of(ParcelService::Cj)->toArray();
     */
    public function toArray(): array
    {
        return $this->parcelService->factoryClass()::make()
            ->state($this->state)->create();
    }

    /** Get the physical label format used by this waybill. */
    public function format(): LabelFormat
    {
        return $this->format;
    }

    /**
     * Save the waybills data to PDF
     *
     * @param  string  $filename  A filename to create
     *
     * @example $waybill = Waybill::of(ParcelService::Cj)->path(realpath(__DIR__.'/../dist'))->save('test.pdf');
     */
    public function save(string $filename = 'waybills.pdf'): mixed
    {
        $this->write();

        $path = empty($this->path) ? '' : $this->path.DIRECTORY_SEPARATOR;

        return $this->mpdf->Output($path.$filename, false);
    }

    /**
     * Download the waybills data to PDF
     *
     * @param  string  $filename  A filename to create
     *
     * @example $waybill = Waybill::of(ParcelService::Cj)->path(realpath(__DIR__.'/../dist'))->save('test.pdf');
     */
    public function download(string $filename = 'waybills.pdf'): mixed
    {
        $this->write();

        return $this->mpdf->Output($filename, true);
    }

    /**
     * Magic method to convert the waybills type to string
     *
     * @return string The method returns the waybills type as string
     *
     * @example echo Waybill::of(ParcelService::Cj)
     */
    public function __toString()
    {
        return $this->parcelService->name();
    }

    /**
     * Factory method to create an instance of Waybill
     *
     * @param  ParcelService  $parcelService  The waybills type
     * @return static The method returns the Waybill instance
     *
     * @example Waybill::of(ParcelService::Cj, $mpdf)->...
     */
    public static function of(WaybillProvider $parcelService, ?Mpdf $mpdf = null, ?LabelFormat $format = null): static
    {
        $format = $format ?? $parcelService->defaultLabelFormat();
        $mpdf = $mpdf ?? SupportMpdf::instance($format);

        return new static($parcelService, $format, $mpdf);
    }

    /**
     * Factory method to create an instance without mpdf instance of Waybill
     *
     * @param  ParcelService  $parcelService  The waybills type
     * @return static The method returns the Waybill instance
     *
     * @example Waybill::make(ParcelService::Cj)->make($mpdf)->...
     */
    public static function make(WaybillProvider $parcelService, ?LabelFormat $format = null): static
    {
        return new static($parcelService, $format ?? $parcelService->defaultLabelFormat());
    }
}
