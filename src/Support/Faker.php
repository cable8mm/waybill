<?php

namespace Cable8mm\Waybill\Support;

class Faker
{
    /**
     * @var \Faker\Generator
     */
    private static $instance;

    /**
     * Get \Faker\Generator singleton instance
     *
     * @param  ?string  $locale  the locale
     * @return \Faker\Generator The method returns \Faker\Generator singleton instance
     */
    public static function shared(?string $locale = 'ko_KR'): \Faker\Generator
    {
        if (! isset(self::$instance)) {
            self::$instance = \Faker\Factory::create($locale);

            self::$instance->addProvider(new \Bezhanov\Faker\Provider\Commerce(self::$instance));
            self::$instance->addProvider(new \Bezhanov\Faker\Provider\Device(self::$instance));
        }

        return self::$instance;
    }

    public function site(): string
    {
        $sites = ['티몬', '쿠팡', '11번가'];

        return $sites[array_rand($sites)];
    }

    public function barcode(): string
    {
        $barcode = (new \Picqer\Barcode\Types\TypeCode128)->getBarcode(self::shared()->ean13());

        // Output the barcode as HTML in the browser with a HTML Renderer
        $renderer = new \Picqer\Barcode\Renderers\PngRenderer;

        return 'data:image/png;base64,'.base64_encode($renderer->render($barcode));
    }

    /**
     * Faker factory method
     */
    public static function make(): static
    {
        return new self;
    }
}
