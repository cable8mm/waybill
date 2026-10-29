# PDF Waybill Generator

![Coding Style Actions](https://github.com/cable8mm/waybill/actions/workflows/code-style.yml/badge.svg)
![Run Tests Actions](https://github.com/cable8mm/waybill/actions/workflows/run-tests.yml/badge.svg)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/cable8mm/waybill.svg)](https://packagist.org/packages/cable8mm/waybill)
[![Packagist Dependency Version](https://img.shields.io/packagist/dependency-v/cable8mm/waybill/php?logo=PHP&logoColor=white&color=777BB4)](https://packagist.org/packages/cable8mm/waybill)
[![Total Downloads](https://img.shields.io/packagist/dt/cable8mm/waybill.svg)](https://packagist.org/packages/cable8mm/waybill)
[![Packagist Stars](https://img.shields.io/packagist/stars/cable8mm/waybill)](https://github.com/cable8mm/waybill/stargazers)

PHP로 PDF 운송장 레이아웃을 생성하는 라이브러리입니다. 기본 템플릿은 CJ대한통운 5인치 라벨 크기(123 × 100 mm)에 맞춰져 있으며, 바코드와 송신자/수신자 정보를 출력합니다.

> 기본 Factory 데이터는 미리보기용 임의 데이터입니다. 이 패키지는 CJ대한통운에 배송을 접수하거나 운송장 번호·분류 코드를 발급하지 않습니다. 실제 배송에 사용하려면 CJ 또는 계약된 연동 솔루션에서 발급한 운송장 번호와 도착지/분류 코드를 `state()`로 전달하고, 사용 중인 계약 양식과 출력 위치를 확인해야 합니다. 운송장 번호를 전달하면 같은 번호를 바코드로 인코딩합니다. CJ 운송장 양식은 규격과 출력 프로그램에 따라 달라질 수 있습니다.

기본 레이아웃은 [CJ대한통운 5인치 신형 운송장 가이드](tests/fixtures/cj/source.md)의 123 × 100 mm 예시를 기준으로 합니다. [2026년 분류 코드 표기 변경 안내](https://team.ebut.co.kr/3b1770fd-007f-8025-9e35-e044daeb5fdc)도 반영했습니다. 실제 배송에는 계약 고객에게 발급된 유효 운송장 번호와 분류 코드가 필요하며, 프린터별 출력 위치는 테스트 인쇄로 맞춰야 합니다.

## 설치

Composer로 설치할 수 있습니다:

```bash
composer require cable8mm/waybill
```

## 사용법

### 빠른 시작

기본 5인치 라벨 미리보기를 PDF로 저장:

```php
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Waybill;

Waybill::of(ParcelService::Cj)
    ->path(realpath(__DIR__.'/../dist'))
    ->save('test.pdf');
```

운송장 데이터를 배열로 가져오기 (API 응답 또는 CSV 내보내기용):

```php
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Waybill;

$waybill = Waybill::of(ParcelService::Cj)
            ->toArray();
```

### 커스텀 데이터 사용하기 (`state()`)

실제 주문 데이터로 특정 필드를 덮어씁니다:

```php
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Waybill;

Waybill::of(ParcelService::Cj)
    ->state([
        'seller' => ['name' => '내회사', 'phone' => '02-1234-5678'],
        'receiver' => ['name' => '홍길동', 'phone' => '010-1234-5678'],
        'tracking_number' => '5856-0852-8903',
    ])
    ->path(realpath(__DIR__.'/../dist'))
    ->save('waybill.pdf');
```

### 인스턴스 분리 생성 후 mpdf 주입

`Waybill` 인스턴스를 먼저 만들고, 나중에 `mpdf`를 주입합니다:

```php
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Support\Mpdf;
use Cable8mm\Waybill\Waybill;

$mpdf = Mpdf::instance();

$waybill = Waybill::make(ParcelService::Cj)
    ->mpdf($mpdf)
    ->path(realpath(__DIR__.'/../dist'))
    ->save('test.pdf');
```

### 여러 개의 운송장 생성

하나의 PDF 파일에 여러 장의 운송장을 저장:

```php
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Support\Mpdf;
use Cable8mm\Waybill\Waybill;
use Cable8mm\Waybill\WaybillCollection;

$mpdf = Mpdf::instance();

WaybillCollection::of(mpdf: $mpdf)
    ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
    ->add(Waybill::of(ParcelService::Cj, mpdf: $mpdf))
    ->path(realpath(__DIR__.'/../dist'))
    ->save('collection.pdf');

// 또는 한 번에 여러 개 추가:
WaybillCollection::of(mpdf: $mpdf)
    ->add([
      Waybill::of(ParcelService::Cj, mpdf: $mpdf),
      Waybill::of(ParcelService::Cj, mpdf: $mpdf),
      ])
    ->path(realpath(__DIR__.'/../dist'))
    ->save('collection.pdf');

```

### PDF에서 특정 운송장만 추출하기

여러 페이지로 된 PDF에서 원하는 페이지 하나만 잘라냅니다:

```php
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Slicer;

Slicer::of(ParcelService::Cj, 1)
    ->source('source.pdf')
    ->save('one_page.pdf'); // 또는 ->download('one_page.pdf') 로 다운로드
```

### 설정

`config.php` 파일에서 mPDF 설정을 관리합니다. 한글 폰트(NanumBarunGothic), 용지 크기, 여백, 워터마크 등을 설정할 수 있습니다:

```php
return [
    'mode' => 'utf-8',
    'format' => 'A4',
    'custom_font_dir' => __DIR__.'/fonts',
    'custom_font_data' => [
        'nbg' => [
            'R' => 'NanumBarunGothic.ttf',
        ],
    ],
    'default_font' => 'nbg',
    // ...
];
```

### 택배사 추가하기 (커스터마이징)

UPS 같은 새 택배사를 추가하려면 `Enum`과 `Factory` 클래스를 만들어야 합니다:

1. `src/Factories/` 폴더에 `UpsFactory.php` 생성
2. `src/Enums/ParcelService.php` Enum에 `Ups` 케이스 추가
3. `factoryClass()`, `stub()`, `templateArea()` 메서드 구현
4. PDF 레이아웃을 위한 `stubs/Ups.stub` 파일 생성

### 테스트

```bash
composer test
```

### 변경 내역

자세한 내용은 [CHANGELOG](CHANGELOG.md)를 확인해주세요.

## 기여하기

기여 방법은 [CONTRIBUTING](CONTRIBUTING.md)을 참고해주세요.

### 보안

보안 관련 이슈는 Issue Tracker 대신 <cable8mm@gmail.com>으로 메일 부탁드립니다.

## 크레딧

- [Samgu Lee](https://github.com/cable8mm)
- [All Contributors](../../contributors)

## 라이선스

MIT 라이선스입니다. 자세한 내용은 [License File](LICENSE)을 확인해주세요.
