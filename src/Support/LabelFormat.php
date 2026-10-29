<?php

namespace Cable8mm\Waybill\Support;

/**
 * Physical page dimensions and margins for a waybill label, in millimetres.
 */
final readonly class LabelFormat
{
    public function __construct(
        public string $name,
        public float $width,
        public float $height,
        public float $marginLeft = 0,
        public float $marginRight = 0,
        public float $marginTop = 0,
        public float $marginBottom = 0,
    ) {
        if ($name === '' || $width <= 0 || $height <= 0) {
            throw new \InvalidArgumentException('A label format needs a name and positive dimensions.');
        }
    }

    /** @return array{format: array{float, float}, orientation: string} */
    public function mpdfOptions(): array
    {
        $landscape = $this->width > $this->height;

        return [
            'format' => $landscape ? [$this->height, $this->width] : [$this->width, $this->height],
            'orientation' => $landscape ? 'L' : 'P',
        ];
    }

    public static function cj5Inch(): self
    {
        return new self('5-inch', 123, 100);
    }
}
