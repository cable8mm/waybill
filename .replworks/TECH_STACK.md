# TECH_STACK

## 1. Languages / runtimes / frameworks / libraries

- PHP: `^8.2`.
- Composer: dependency manager and autoloader.
- PHP extensions: `mbstring`, `gd` (required transitively by mPDF and the barcode PNG renderer).
- mpdf/mpdf: `^8.0|^8.1`.
- cable8mm/stub-template: `^1.0`.
- fakerphp/faker: `^1.0`.
- mbezhanov/faker-provider-collection: `^2.0`.
- picqer/php-barcode-generator: `^3.2`.
- phpunit/phpunit (dev): `^9.0|^10.0|^11.0`.
- laravel/pint (dev): `^1.20`.
- Doctum (API docs, run as `doctum.phar` or via CI action): no Composer constraint.
- Fonts: Nanum Barun Gothic (`NanumBarunGothic.ttf`), Nanum Gothic Regular and Bold (`NanumGothic-Regular.ttf`, `NanumGothic-Bold.ttf`).
- MUST NOT add a runtime dependency outside this list.

## 2. Framework- or tool-mandated paths and file names only

- `composer.json`: package manifest.
- `vendor/`: Composer install directory.
- `vendor/autoload.php`: Composer autoloader.
- `src/`: PSR-4 root for namespace `Cable8mm\Waybill\`.
- `tests/`: PSR-4 root for namespace `Cable8mm\Waybill\Tests\`.
- `phpunit.xml.dist`: PHPUnit configuration.
- `phpunit.xml`: local PHPUnit override (git-ignored).
- `tests/**/*Test.php`: PHPUnit test file pattern.
- `doctum.php`: Doctum configuration.
- `build/`, `cache/`: Doctum output (git-ignored).
- `coverage/`: local HTML coverage output.
- `config.php`: mPDF settings file read at package root.
- `fonts/`: mPDF custom font directory.
- `tmp/`: mPDF temp directory (git-ignored).
- `.github/workflows/`: GitHub Actions workflows.
- `.editorconfig`, `.gitattributes`, `.gitignore`.
- `LICENSE.md`, `CHANGELOG.md`.

## 3. Coding conventions specific to the chosen stack

- Code style MUST pass Laravel Pint with the default `laravel` preset.
- MUST NOT add `pint.json` unless the preset changes.
- Files MUST be UTF-8, LF line endings, final newline, 4-space indent.
- Markdown and YAML files MUST use 2-space indent.
- Every class MUST live under namespace `Cable8mm\Waybill\` mapped to `src/` by PSR-4.
- PHP language features MUST be limited to those available in PHP 8.2.
- mPDF instances MUST be created through `Cable8mm\Waybill\Support\Mpdf::instance()`.
- MUST NOT instantiate `\Mpdf\Mpdf` directly.
- Faker MUST be obtained through `Cable8mm\Waybill\Support\Faker::shared()` with locale `ko_KR`.
- Barcode images MUST be rendered by picqer as PNG and passed to templates as `data:image/png;base64,...` strings.
- Code 128 MUST be produced with `TypeCode128`.
- Interleaved 2 of 5 MUST be produced with `TypeInterleaved25`.
- Templates MUST be Twig syntax rendered through `Cable8mm\StubTemplate\Stub`, stored with the `.stub` extension.
- Templates MUST wrap output in `{% autoescape %}`.
- Barcode data URIs in templates MUST use the `|raw` filter.
- Public methods and classes MUST carry PHPDoc blocks readable by Doctum.

## 4. Testing rules

- Test runner: PHPUnit, invoked by `composer test` (`vendor/bin/phpunit`).
- Test bootstrap: `vendor/autoload.php`.
- Tests MUST be in `tests/` with file names ending in `Test.php`.
- Test classes MUST be `final class` extending `PHPUnit\Framework\TestCase`.
- Test method names MUST be snake_case prefixed with `test_`.
- Tests MUST pass with `beStrictAboutOutputDuringTests="true"`.
- Tests that emit PDF output MUST wrap it in `ob_start()` / `ob_end_clean()`.
- Tests that write PDF files MUST `unlink()` them after assertion.
- Private property access in tests MUST use `ReflectionClass` with `setAccessible(true)`.
- Tests MUST NOT require network access.
- Tests MUST pass on PHP 8.2, 8.3, 8.4, and 8.5.
- Coverage is not used.
- MUST NOT enforce a coverage threshold.

## 5. Configuration rules

- Configuration file: `config.php` at package root, returning a PHP array.
- Configuration keys: `mode`, `format`, `default_font_size`, `default_font`, `margin_left`, `margin_right`, `margin_top`, `margin_bottom`, `margin_header`, `margin_footer`, `orientation`, `title`, `subject`, `author`, `watermark`, `show_watermark`, `show_watermark_image`, `watermark_font`, `display_mode`, `watermark_text_alpha`, `watermark_image_path`, `watermark_image_alpha`, `watermark_image_size`, `watermark_image_position`, `custom_font_dir`, `custom_font_data`, `auto_language_detection`, `temp_dir`, `pdfa`, `pdfaauto`, `use_active_forms`.
- Custom font keys in `custom_font_data` MUST be lowercase.
- `custom_font_data` MUST define `nanumbarungothic` and `nanumgothic` with `R`, `B`, `I`, `BI` entries.
- MUST NOT read environment variables.
- MUST NOT use secrets.

## 6. Build, packaging, and release rules

- No build step.
- Distribution target: Packagist package `cable8mm/waybill`, type `library`.
- Install command: `composer require cable8mm/waybill`.
- Release MUST be a GitHub release on a semantic version tag (`vX.Y.Z`).
- `composer.lock` MUST NOT be committed.
- `composer validate` MUST pass.
- `.gitattributes` `export-ignore` MUST exclude `/.github`, `/dist`, `/tests`, `/resources/tests`, `.editorconfig`, `.gitattributes`, `.gitignore`, `CHANGELOG.md`, `LICENSE.md`, `phpunit.xml.dist` from the dist archive.
- `fonts/` and `config.php` MUST be included in the dist archive.
- Binary fixtures (`tests/fixtures/cj/*.pdf`, `tests/fixtures/hanjin/*.jpg`, `tests/fixtures/hanjin/*.png`) MUST be marked `binary` in `.gitattributes`.
- CI system: GitHub Actions on `ubuntu-latest` with `shivammathur/setup-php@v2`.
- Workflow `run-tests`: on push to `main`, pull requests, and manual dispatch; PHP matrix 8.2, 8.3, 8.4, 8.5; steps `composer validate`, `composer install --prefer-dist --no-progress`, `composer test`.
- Workflow `code-style`: on push (excluding `dependabot/npm_and_yarn/*`) and manual dispatch; PHP 8.4; runs `pint` and auto-commits with message `Fixes coding style`.
- Workflow `deploy-to-github-pages`: on push to `main`; builds Doctum from `doctum.php` and deploys `./build` to GitHub Pages.
- Workflow `update-changelog`: on GitHub release `released`; calls `cable8mm/.github/.github/workflows/update-changelog.yml@main`.
- Composer scripts: `test`, `test-coverage`, `lint`, `apidoc`, `opendoc`.
- License: MIT.
- Bundled Nanum Gothic MUST ship with `fonts/NanumGothic-OFL.txt` (SIL Open Font License 1.1).

## 7. Security rules tied to the chosen stack

- Templates MUST keep Twig autoescaping enabled for all user-supplied fields.
- `|raw` MUST be used only for barcode data URIs generated by picqer.
- `use_active_forms` MUST be `false`.
- mPDF MUST NOT be configured to load remote resources.
- Workflow permissions MUST be declared explicitly per workflow.
- Workflows other than `code-style` and `update-changelog` MUST NOT hold `contents: write`.
- Security reports MUST go through the private channel in `.github/SECURITY.md`.
- Only the latest major version MUST receive security fixes.

## 8. Non-goals

- Coverage tooling and coverage thresholds.
- Static analysis tools (PHPStan, Psalm).
- Committed `composer.lock`.
- Environment variable configuration (`.env`, dotenv).
- PDF engines other than mPDF (TCPDF, Dompdf, wkhtmltopdf).
- Template engines other than Twig via cable8mm/stub-template.
- Barcode libraries other than picqer/php-barcode-generator.
- HTTP clients or carrier API SDKs.
- Frameworks (Laravel, Symfony) as runtime dependencies.
- Build tools, bundlers, or compilers.
