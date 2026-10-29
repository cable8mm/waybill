<?php

namespace Cable8mm\Waybill\Factories;

use Cable8mm\Waybill\Support\Faker;
use Cable8mm\Waybill\Support\HanjinDataMasker;

/**
 * Example shipment data for Hanjin waybill previews.
 */
class HanjinFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $trackingNumber = Faker::shared()->numerify('############');
        $terminalCode = Faker::shared()->numerify('####');

        return [
            'tracking_number' => $trackingNumber,
            'tracking_barcode' => Faker::make()->interleaved2of5($trackingNumber),
            'terminal_code' => $terminalCode,
            'terminal_name' => Faker::shared()->randomElement(['해운(집)', '소공동', '권순천']),
            'terminal_barcode' => Faker::make()->barcode($terminalCode),
            'hub_code' => Faker::shared()->randomElement(['NX', 'SS']),
            'hub_number' => Faker::shared()->numerify('###'),
            'middle_code' => Faker::shared()->randomElement(['A1', 'D', 'E54']),
            'center_code' => Faker::shared()->numerify('###'),
            'center_name' => Faker::shared()->randomElement(['권순천', '최영훈', '소공동']),
            'group_rank' => Faker::shared()->randomElement(['E54', 'Z99', 'B12']),
            'worker_name' => Faker::shared()->name(),
            'worker_code' => Faker::shared()->numerify('###'),
            'sender' => [
                'name' => Faker::shared()->name(),
                'phone' => Faker::shared()->phoneNumber(),
                'address' => Faker::shared()->address(),
            ],
            'receiver' => [
                'name' => Faker::shared()->name(),
                'phone' => Faker::shared()->phoneNumber(),
                'address' => Faker::shared()->address(),
            ],
            'item_name' => Faker::shared()->productName(),
            'item_quantity' => 1,
            'order_number' => 'TEST-'.Faker::shared()->numerify('######'),
            'print_date' => Faker::shared()->dateTime()->format('Y-m-d'),
            'freight_type' => Faker::shared()->randomElement(['선불', '착불']),
            'delivery_fee' => number_format(Faker::shared()->numberBetween(2500, 6000)),
            'delivery_request' => Faker::shared()->randomElement(['문 앞에 놓아주세요.', '배송 전 연락 바랍니다.', '경비실에 맡겨주세요.']),
            'region_name' => Faker::shared()->randomElement(['수도권', '지방', '제주']),
        ];
    }

    /**
     * Keep the sample barcode synchronized with caller-provided values.
     *
     * @return array<string, mixed>
     */
    public function create(): array
    {
        $record = $this->definition();
        $record = array_replace($record, $this->state);

        foreach (['sender', 'receiver'] as $side) {
            if (isset($this->state[$side]) && is_array($this->state[$side])) {
                $record[$side] = array_replace($record[$side], $this->state[$side]);
            }
        }

        if (! array_key_exists('tracking_barcode', $this->state)) {
            $record['tracking_barcode'] = Faker::make()->interleaved2of5($record['tracking_number']);
        }

        if (! array_key_exists('terminal_barcode', $this->state)) {
            $record['terminal_barcode'] = Faker::make()->barcode($record['terminal_code']);
        }

        return HanjinDataMasker::mask($record);
    }
}
