<?php

namespace Cable8mm\Waybill\Tests\Fakes;

use Cable8mm\Waybill\Contracts\WaybillProvider;
use Cable8mm\Waybill\Support\LabelFormat;

/**
 * A user-supplied provider with its own data, template, and label format.
 */
final class ExampleProvider implements WaybillProvider
{
    public function factoryClass(): string
    {
        return ExampleFactory::class;
    }

    public function labelFormats(): array
    {
        return ['example' => new LabelFormat('example', 90, 60)];
    }

    public function defaultLabelFormat(): LabelFormat
    {
        return $this->labelFormats()['example'];
    }

    public function stub(LabelFormat $format): string
    {
        $this->assertSupported($format);

        return __DIR__.DIRECTORY_SEPARATOR.'Example.stub';
    }

    public function templateArea(LabelFormat $format): array
    {
        $this->assertSupported($format);

        return [5, 5, 80, 50];
    }

    public function name(): string
    {
        return 'Example Carrier';
    }

    private function assertSupported(LabelFormat $format): void
    {
        if (! array_key_exists($format->name, $this->labelFormats())) {
            throw new \DomainException('Example Carrier does not support '.$format->name.'.');
        }
    }
}
