# PDF Waybill Generator

![Coding Style Actions](https://github.com/cable8mm/waybill/actions/workflows/code-style.yml/badge.svg)
![Run Tests Actions](https://github.com/cable8mm/waybill/actions/workflows/run-tests.yml/badge.svg)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/cable8mm/waybill.svg)](https://packagist.org/packages/cable8mm/waybill)
[![Packagist Dependency Version](https://img.shields.io/packagist/dependency-v/cable8mm/waybill/php?logo=PHP&logoColor=white&color=777BB4)](https://packagist.org/packages/cable8mm/waybill)
[![Total Downloads](https://img.shields.io/packagist/dt/cable8mm/waybill.svg)](https://packagist.org/packages/cable8mm/waybill)
[![Packagist Stars](https://img.shields.io/packagist/stars/cable8mm/waybill)](https://github.com/cable8mm/waybill/stargazers)

PHP로 PDF 운송장 레이아웃을 생성하는 라이브러리입니다. 기본 제공 양식은 CJ대한통운 5인치(123 × 100 mm)와 한진 NS·NL·FS이며, 바코드와 송신자/수신자 정보를 출력합니다.

> 기본 Factory 데이터는 미리보기용 임의 데이터입니다. 이 패키지는 CJ대한통운에 배송을 접수하거나 운송장 번호·분류 코드를 발급하지 않습니다. 실제 배송에 사용하려면 CJ 또는 계약된 연동 솔루션에서 발급한 운송장 번호와 도착지/분류 코드를 `state()`로 전달하고, 사용 중인 계약 양식과 출력 위치를 확인해야 합니다. 운송장 번호를 전달하면 같은 번호를 바코드로 인코딩합니다. CJ 운송장 양식은 규격과 출력 프로그램에 따라 달라질 수 있습니다.

기본 레이아웃은 [CJ대한통운 5인치 신형 운송장 가이드](tests/fixtures/cj/source.md)의 123 × 100 mm 예시를 기준으로 합니다. [2026년 분류 코드 표기 변경 안내](https://team.ebut.co.kr/3b1770fd-007f-8025-9e35-e044daeb5fdc)도 반영했습니다. 실제 배송에는 계약 고객에게 발급된 유효 운송장 번호와 분류 코드가 필요하며, 프린터별 출력 위치는 테스트 인쇄로 맞춰야 합니다.

## 설치

PHP 8.2 이상과 `gd`, `mbstring` 확장이 필요합니다. Composer로 설치할 수 있습니다:

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

`Waybill::of()`의 두 번째 인자로 PDF 인스턴스를 계속 전달할 수 있습니다. 용지 규격을 명시하려면 `LabelFormat`을 지정합니다. 같은 택배사도 계약 양식이나 프린터 매체에 따라 서로 다른 템플릿이 필요하므로, 해당 택배사 provider가 실제로 제공하는 규격만 선택할 수 있습니다.

```php
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Support\LabelFormat;
use Cable8mm\Waybill\Waybill;

Waybill::of(ParcelService::Cj, format: LabelFormat::cj5Inch())
    ->save('cj-5inch.pdf');
```

기본 CJ provider는 현재 가이드에 근거한 5인치(123 × 100 mm)만 제공합니다. 다른 크기를 쓰려면 실제 양식에 맞는 템플릿과 규격을 provider에 함께 추가해야 합니다.

### 한진택배 양식

한진 provider는 공식 가이드의 NS, NL, FS 형식과 각 양식의 샘플 데이터/바코드를 제공합니다. 크기 선택은 명시적으로 합니다:

```php
use Cable8mm\Waybill\Providers\HanjinProvider;
use Cable8mm\Waybill\Waybill;

$hanjin = new HanjinProvider;

Waybill::of($hanjin, format: $hanjin->labelFormats()[HanjinProvider::NS])
    ->save('hanjin-ns.pdf');

Waybill::of($hanjin, format: $hanjin->labelFormats()[HanjinProvider::NL])
    ->save('hanjin-nl.pdf');

Waybill::of($hanjin, format: $hanjin->labelFormats()[HanjinProvider::FS])
    ->save('hanjin-fs.pdf');
```

NS/NL/FS 용지 크기와 허용 오차(±5 mm)는 [플레이오토의 한진 출력 설정](https://www.plto.com/customer/HelpDesc/gmp/13789)을 따릅니다. 프린터 드라이버에 따라 가로·세로 값을 바꿔 설정해야 할 수 있으며, 템플릿은 한진 [공식 출력 가이드](https://developers.hanjin.com/printwbl)의 예시 방향을 따릅니다.

한진 운송장은 가이드의 개인정보 마스킹 규칙에 따라 보내는 분과 받는 분의 이름, 전화번호, 상세 주소를 가린 뒤 출력합니다. `state()`로 넘긴 값도 똑같이 가려집니다. 한진 운송장 번호는 Interleaved 2 of 5 바코드로 인코딩되므로 숫자만 사용할 수 있습니다. 이 패키지는 한진에 배송을 접수하거나 운송장 번호·분류 코드를 발급하지 않습니다.

운송장 데이터를 배열로 가져오기 (API 응답 또는 CSV 내보내기용):

```php
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Waybill;

$waybill = Waybill::of(ParcelService::Cj)
            ->toArray();
```

### 커스텀 데이터 사용하기 (`state()`)

실제 주문 데이터로 특정 필드를 덮어씁니다. 넘기지 않은 필드는 미리보기용 임의 값으로 채워집니다:

```php
use Cable8mm\Waybill\Enums\ParcelService;
use Cable8mm\Waybill\Waybill;

Waybill::of(ParcelService::Cj)
    ->state([
        'seller' => [
            'name' => '내회사',
            'phone' => '02-1234-5678',
            'site' => '쿠팡',
            'address' => '서울특별시 중구 세종대로 110',
        ],
        'receiver' => [
            'name' => '홍길동',
            'phone' => '02-987-6543',
            'cell_phone' => '010-1234-5678',
            'address' => '서울특별시 강남구 테헤란로 123 4층',
        ],
        'tracking_number' => '5856-0852-8903',
    ])
    ->path(realpath(__DIR__.'/../dist'))
    ->save('waybill.pdf');
```

배열로 된 필드는 넘긴 키만 바꾸고 나머지 키는 기본값을 유지합니다. CJ는 `seller`, `receiver`, `city`, `region`, 한진은 `sender`, `receiver`가 여기에 해당합니다.

`state()`를 다시 호출하면 앞서 설정한 값은 모두 새 값으로 바뀝니다.

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

컬렉션과 모든 송장은 같은 `$mpdf` 인스턴스를 써야 합니다. 다른 인스턴스로 만든 송장은 컬렉션 PDF에 들어가지 않습니다. `Mpdf::instance()`는 인자가 없으면 `config.php`의 기본 용지(123 × 100 mm)를 쓰므로, 한진 NS·NL처럼 크기가 다른 양식은 `Mpdf::instance($format)`으로 같은 규격의 인스턴스를 만들어 사용합니다.

`WaybillCollection::of($waybills, 5)`처럼 지정하는 두 번째 인자(반복 횟수)는 `toArray()`에만 적용됩니다. PDF에는 송장마다 한 장씩만 출력됩니다.

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

`config.php` 파일에서 mPDF 설정을 관리합니다. 한글 폰트, 기본 용지 크기, 여백, 문서 정보, 워터마크 등을 설정할 수 있습니다:

```php
return [
    'mode' => 'utf-8',
    'format' => [100, 123],
    'orientation' => 'L',
    'default_font' => 'nanumbarungothic',
    'custom_font_dir' => __DIR__.DIRECTORY_SEPARATOR.'fonts',
    'custom_font_data' => [
        'nanumbarungothic' => [ // CJ 양식
            'R' => 'NanumBarunGothic.ttf',
            'B' => 'NanumBarunGothic.ttf',
            'I' => 'NanumBarunGothic.ttf',
            'BI' => 'NanumBarunGothic.ttf',
        ],
        'nanumgothic' => [ // 한진 양식
            'R' => 'NanumGothic-Regular.ttf',
            'B' => 'NanumGothic-Bold.ttf',
            'I' => 'NanumGothic-Regular.ttf',
            'BI' => 'NanumGothic-Bold.ttf',
        ],
    ],
    // ...
];
```

폰트 키는 소문자로 써야 합니다. `Waybill::of()`나 `Mpdf::instance($format)`에 `LabelFormat`을 지정하면 용지 크기, 방향, 여백은 `config.php` 대신 `LabelFormat` 값을 따르고 머리글·바닥글 여백은 0이 됩니다.

### 택배사와 규격 추가하기

택배사별 데이터 생성과 템플릿 선택은 `WaybillProvider` 계약으로 분리되어 있습니다. 새 택배사는 앱이나 패키지에서 이 인터페이스를 구현하고, `labelFormats()`에 지원하는 `LabelFormat` 목록을 반환합니다. 각 메서드가 반환해야 하는 값은 다음과 같습니다.

- `factoryClass()`: `Cable8mm\Waybill\Factories\Factory`를 상속하고 `definition()`을 구현한 클래스 이름. 이 클래스가 택배사별 기본 데이터를 만들고 `state()` 값을 합칩니다.
- `labelFormats()`: 지원하는 `LabelFormat`을 이름을 키로 담은 배열. 규격 지원 여부는 이름으로만 판단합니다.
- `defaultLabelFormat()`: 규격을 지정하지 않았을 때 쓸 `LabelFormat`.
- `stub(LabelFormat)`: 해당 택배사와 규격 조합의 Twig 템플릿 파일 절대 경로. 지원하지 않는 규격이면 `InvalidArgumentException`을 던져야 합니다.
- `templateArea(LabelFormat)`: `Slicer`가 PDF에서 송장을 잘라낼 때 쓸 `[x, y, 너비, 높이]` 영역(mm).
- `name()`: 송장을 문자열로 바꿀 때 쓰는 택배사 이름.

아래는 provider가 맡는 메서드를 보여주는 축약 예시입니다. 템플릿 경로와 자르기 영역은 실제 택배사 양식에 맞춰 구현해야 합니다.

```php
use Cable8mm\Waybill\Contracts\WaybillProvider;
use Cable8mm\Waybill\Support\LabelFormat;

final class ExampleCarrier implements WaybillProvider
{
    public function factoryClass(): string { return ExampleFactory::class; }

    public function labelFormats(): array
    {
        return ['example-5inch' => new LabelFormat('example-5inch', 123, 100)];
    }

    public function defaultLabelFormat(): LabelFormat
    {
        return $this->labelFormats()['example-5inch'];
    }

    public function stub(LabelFormat $format): string { /* 택배사 + 규격 템플릿의 절대 경로 */ }
    public function templateArea(LabelFormat $format): array { /* 자르기 영역(mm) */ }
    public function name(): string { return 'Example Carrier'; }
}
```

`Waybill::of(new ExampleCarrier())`로 기본 규격을 쓰거나, `Waybill::of(new ExampleCarrier(), format: $format)`으로 해당 provider에 등록된 다른 규격을 선택합니다. 기존 `ParcelService::Cj` enum은 `WaybillProvider`를 구현하므로 기존 호출 코드는 유지됩니다. 현재 PDF 하나에 서로 다른 용지 크기의 송장을 섞는 기능은 지원하지 않으므로 컬렉션의 모든 송장은 같은 `LabelFormat`을 사용해야 합니다.

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
