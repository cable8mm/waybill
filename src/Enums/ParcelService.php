<?php

namespace Cable8mm\Waybill\Enums;

use Cable8mm\Waybill\Contracts\WaybillProvider;
use Cable8mm\Waybill\Factories\CjFactory;
use Cable8mm\Waybill\Support\LabelFormat;

enum ParcelService: string implements WaybillProvider
{
    /**
     * Parcel Services
     */
    case Cj = 'CJ택배';

    /**
     * Get the name of the factory class with namespace
     *
     * @return string The name of the factory class with namespace
     *
     * @example ParcelService::Cj->factoryClass() Cable8mm\Waybill\Factories\CjFactory
     */
    public function factoryClass(): string
    {
        return match ($this) {
            self::Cj => CjFactory::class,
        };
    }

    /**
     * Get the stub filename with path for the factory class
     *
     * @return string The stub filename with path for the factory class
     *
     * @example ParcelService::Cj->stub()   __DIR__/stubs/Cj.stub
     */
    public function stub(?LabelFormat $format = null): string
    {
        $this->assertSupportedFormat($format ?? $this->defaultLabelFormat());

        $stubPath = match ($this) {
            self::Cj => realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'../stubs/'.$this->name.'.stub'),
        };

        if ($stubPath === false) {
            throw new \Exception('Failed to read file: no existing stub');
        }

        return $stubPath;
    }

    /**
     * Get the template area for slicing a specific waybill from a page
     *
     * Returns [offsetX, offsetY, width, height] in mm units.
     * These values define the crop region when extracting a single waybill
     * from a multi-waybill PDF page using mPDF's ImportPage + UseTemplate.
     *
     * @return array{0: int, 1: int, 2: int, 3: int} The area as [offsetX, offsetY, width, height]
     */
    public function templateArea(?LabelFormat $format = null): array
    {
        $this->assertSupportedFormat($format ?? $this->defaultLabelFormat());

        return match ($this) {
            // CJ waybill: offset (-40mm, -46mm), size 285mm x 196mm
            self::Cj => [-40, -46, 285, 196],
        };
    }

    /** @return array<string, LabelFormat> */
    public function labelFormats(): array
    {
        return match ($this) {
            self::Cj => ['5-inch' => $this->defaultLabelFormat()],
        };
    }

    public function defaultLabelFormat(): LabelFormat
    {
        return match ($this) {
            self::Cj => LabelFormat::cj5Inch(),
        };
    }

    public function name(): string
    {
        return $this->value;
    }

    private function assertSupportedFormat(LabelFormat $format): void
    {
        if (! array_key_exists($format->name, $this->labelFormats())) {
            throw new \InvalidArgumentException(sprintf(
                'The %s provider does not support the %s label format.',
                $this->value,
                $format->name
            ));
        }
    }
}
