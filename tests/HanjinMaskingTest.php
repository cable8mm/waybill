<?php

namespace Cable8mm\Waybill\Tests;

use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Providers\HanjinProvider;
use Cable8mm\Waybill\Tests\Fakes\RecordingMpdf;
use Cable8mm\Waybill\Waybill;
use PHPUnit\Framework\TestCase;

final class HanjinMaskingTest extends TestCase
{
    private const RECEIVER = [
        'name' => '홍길동',
        'phone' => '010-1234-5678',
        'address' => '서울특별시 강남구 테헤란로 123 4층',
    ];

    public function test_receiver_is_masked(): void
    {
        $this->assertSame(
            ['name' => '홍*동', 'phone' => '010-1234-****', 'address' => '서울특별시 강남구 테헤란로 123 ****'],
            $this->receiver(self::RECEIVER),
        );
    }

    public function test_default_and_state_data_are_masked(): void
    {
        $data = Waybill::make(new HanjinProvider)->state(['receiver' => self::RECEIVER])->toArray();

        $this->assertStringEndsWith('****', $data['sender']['phone']);
        $this->assertStringEndsWith(' ****', $data['sender']['address']);
        $this->assertSame('010-1234-****', $data['receiver']['phone']);
    }

    public function test_names_are_masked_by_length_and_script(): void
    {
        $expected = [
            '홍' => '홍',
            '홍길' => '홍*',
            '홍길동' => '홍*동',
            '남궁길동' => '남*길*',
            '남궁길동이' => '남*길**',
            'A' => 'A',
            'Al' => 'A*',
            'John' => 'Joh*',
            'Joseph' => 'Jose**',
        ];

        foreach ($expected as $name => $masked) {
            $this->assertSame($masked, $this->receiver(['name' => $name])['name'], $name);
        }
    }

    public function test_phone_with_fewer_than_four_digits_is_fully_masked(): void
    {
        $this->assertSame('***', $this->receiver(['phone' => '119'])['phone']);
    }

    public function test_address_without_a_road_name_is_masked_after_the_neighbourhood(): void
    {
        $this->assertSame('서울시 중구 소공동 ****', $this->receiver(['address' => '서울시 중구 소공동 51 10층'])['address']);
    }

    public function test_other_addresses_keep_the_first_three_words(): void
    {
        $this->assertSame('Seoul Jung District ****', $this->receiver(['address' => 'Seoul Jung District Building 5'])['address']);
    }

    public function test_rendered_label_shows_only_masked_values(): void
    {
        $provider = new HanjinProvider;
        $mpdf = new RecordingMpdf($provider->defaultLabelFormat());

        Waybill::make($provider)->mpdf($mpdf)->state(['receiver' => self::RECEIVER])->write();

        $text = $mpdf->writtenText();
        $this->assertStringContainsString('홍*동', $text);
        $this->assertStringContainsString('010-1234-****', $text);
        $this->assertStringNotContainsString('홍길동', $text);
        $this->assertStringNotContainsString('5678', $text);
        $this->assertStringNotContainsString('4층', $text);
    }

    public function test_cj_data_is_not_masked(): void
    {
        $receiver = Waybill::make(ParcelService::Cj)->state(['receiver' => self::RECEIVER])->toArray()['receiver'];

        $this->assertSame('홍길동', $receiver['name']);
        $this->assertSame('010-1234-5678', $receiver['phone']);
        $this->assertSame('서울특별시 강남구 테헤란로 123 4층', $receiver['address']);
    }

    public function test_direct_record_is_not_masked(): void
    {
        $provider = new HanjinProvider;
        $mpdf = new RecordingMpdf($provider->defaultLabelFormat());
        $record = Waybill::make($provider)->toArray();
        $record['receiver'] = self::RECEIVER;

        Waybill::make($provider)->mpdf($mpdf)->write($record);

        $this->assertStringContainsString('홍길동', $mpdf->writtenText());
        $this->assertStringContainsString('010-1234-5678', $mpdf->writtenText());
    }

    /**
     * @param  array<string, string>  $receiver
     * @return array<string, string>
     */
    private function receiver(array $receiver): array
    {
        return Waybill::make(new HanjinProvider)->state(['receiver' => $receiver])->toArray()['receiver'];
    }
}
