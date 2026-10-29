<?php

namespace Cable8mm\Waybill\Contracts;

use Cable8mm\Waybill\Support\LabelFormat;

/**
 * Defines the carrier-specific data and templates used to render waybills.
 */
interface WaybillProvider
{
    /** @return class-string */
    public function factoryClass(): string;

    /** @return array<string, LabelFormat> */
    public function labelFormats(): array;

    public function defaultLabelFormat(): LabelFormat;

    public function stub(LabelFormat $format): string;

    /** @return array{0: int|float, 1: int|float, 2: int|float, 3: int|float} */
    public function templateArea(LabelFormat $format): array;

    public function name(): string;
}
