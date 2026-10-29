<?php

namespace Cable8mm\Waybill\Providers;

use Cable8mm\Waybill\Contracts\WaybillProvider as WaybillProviderContract;
use Cable8mm\Waybill\Factories\HanjinFactory;
use Cable8mm\Waybill\Support\LabelFormat;

final class HanjinProvider implements WaybillProviderContract
{
    public const NS = 'NS';

    public const NL = 'NL';

    public const FS = 'FS';

    /** @return class-string<HanjinFactory> */
    public function factoryClass(): string
    {
        return HanjinFactory::class;
    }

    /** @return array<string, LabelFormat> */
    public function labelFormats(): array
    {
        return [
            self::NS => new LabelFormat(self::NS, 200, 101.5),
            self::NL => new LabelFormat(self::NL, 101.5, 100),
            self::FS => new LabelFormat(self::FS, 123, 100),
        ];
    }

    public function defaultLabelFormat(): LabelFormat
    {
        return $this->labelFormats()[self::FS];
    }

    public function stub(LabelFormat $format): string
    {
        $path = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR.'Hanjin'.DIRECTORY_SEPARATOR.$format->name.'.stub');

        if ($path === false || ! array_key_exists($format->name, $this->labelFormats())) {
            throw new \InvalidArgumentException(sprintf('Hanjin does not support the %s label format.', $format->name));
        }

        return $path;
    }

    /** @return array{0: int|float, 1: int|float, 2: int|float, 3: int|float} */
    public function templateArea(LabelFormat $format): array
    {
        $this->assertSupportedFormat($format);

        return [0, 0, $format->width, $format->height];
    }

    public function name(): string
    {
        return '한진택배';
    }

    private function assertSupportedFormat(LabelFormat $format): void
    {
        if (! array_key_exists($format->name, $this->labelFormats())) {
            throw new \InvalidArgumentException(sprintf('Hanjin does not support the %s label format.', $format->name));
        }
    }
}
