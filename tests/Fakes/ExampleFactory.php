<?php

namespace Cable8mm\Waybill\Tests\Fakes;

use Cable8mm\Waybill\Factories\Factory;

/**
 * Sample data for the user-supplied provider used in tests.
 */
final class ExampleFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'tracking_number' => 'EXAMPLE-TRACKING',
            'recipient' => 'EXAMPLE-RECIPIENT',
        ];
    }
}
