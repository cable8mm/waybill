<?php

namespace Cable8mm\Waybill;

use Cable8mm\StubTemplate\Stub;
use Cable8mm\Waybill\Enums\ParcelService;
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

        $this->mpdf = SupportMpdf::instance();
    }

    /**
     * Run WriteHTML function
     *
     * @param  ?array<string,Tvalue>  $args  The array of arguments for a model or table fields
     */
    private function write(?array $args = null): void
    {
        $data = [];

        if (! is_null($args)) {
            $data = $args;
        } elseif (! empty($this->state)) {
            $data = $this->parcelService->factoryClass()::make()->state($this->state)->definition();
        } else {
            $data = $this->parcelService->factoryClass()::make()->definition();
        }

        $this->mpdf->WriteHTML(
            Stub::of(
                $this->parcelService->stub(),
                $data
            )->render()
        );
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
     * @example $orderSheet = Waybill::of(ParcelService::Cj)->toArray();
     */
    public function toArray(): array
    {
        return $this->parcelService->factoryClass()::make()
            ->state($this->state)->create();
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
        return $this->parcelService->value;
    }

    /**
     * Factory method to create an instance of OrderSheet
     *
     * @param  ParcelService  $parcelService  The waybills type
     * @return static The method returns the OrderSheet instance
     *
     * @example Waybill::of(ParcelService::Cj)->...
     */
    public static function of(ParcelService $parcelService): static
    {
        return new static($parcelService);
    }
}
