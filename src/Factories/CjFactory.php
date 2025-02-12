<?php

namespace Cable8mm\Waybill\Factories;

use Cable8mm\Waybill\Support\Faker;

class CjFactory extends Factory
{
    /**
     * Define the order sheet's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city' => [
                'code' => Faker::shared()->bothify('?#???'),
                'name' => Faker::shared()->borough(),
            ],
            'region' => [
                'code' => Faker::shared()->bothify('?#####-##'),
                'name' => Faker::shared()->borough(),
            ],
            'line_items' => [
                Faker::shared()->productName().' x '.(Faker::shared()->randomNumber(1) + 1),
                Faker::shared()->productName().' x '.(Faker::shared()->randomNumber(1) + 1),
            ],
            'seller' => [
                'name' => Faker::shared()->company(),
                'phone' => Faker::shared()->phoneNumber(),
                'site' => Faker::make()->site(),
                'address' => Faker::shared()->address(),
            ],
            'receiver' => [
                'name' => Faker::shared()->name(),
                'phone' => Faker::shared()->phoneNumber(),
                'cell_phone' => Faker::shared()->cellPhoneNumber(),
                'address' => Faker::shared()->address(),
            ],
            'printed' => Faker::shared()->dateTime()->format('Y-m-d'),
            'total_printed_count' => Faker::shared()->randomNumber(3, true),
            'site_order_no' => Faker::shared()->randomNumber(5, true).Faker::shared()->randomNumber(5, true),
            'tracking_number' => Faker::shared()->bothify('????-????-????'),
            'delivery_worker' => Faker::shared()->name(),
            'settlement_type' => Faker::shared()->randomElement(['신용', '선불']),
            'print_date' => Faker::shared()->dateTime()->format('Y-m-d'),
            'box_quantity' => Faker::shared()->numberBetween(1, 3),
            'freight_type' => 'A',
            'barcode' => Faker::make()->barcode(),
        ];
    }
}
