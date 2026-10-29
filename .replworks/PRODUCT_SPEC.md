# PRODUCT_SPEC

## Purpose

- The product is a library named `cable8mm/waybill`.
- The product generates printable PDF waybill labels for Korean parcel carriers.
- Built-in carriers are CJ Logistics (provider `ParcelService::Cj`, name `CJ택배`) and Hanjin (provider `HanjinProvider`, name `한진택배`).
- Built-in label formats are CJ `5-inch`, Hanjin `NS`, Hanjin `NL`, and Hanjin `FS`.
- The product exposes no graphical user interface. Its user-facing surface is the public programming interface defined in User Interface.

## Problem

- A developer who ships parcels with CJ Logistics or Hanjin needs a PDF label whose layout matches the carrier's published label guide.
- A developer needs illustrative sample label data to preview a layout before real shipment data exists.
- A developer needs one PDF containing multiple labels.
- A developer needs to extract one label page from a multi-page label PDF.

## Product Goals

- The user MUST be able to produce a PDF label for a built-in carrier and label format.
- The user MUST be able to replace any default label field with their own value.
- The user MUST be able to obtain the label data as an array without producing a PDF.
- The user MUST be able to produce one PDF containing multiple labels.
- The user MUST be able to extract a single page of an existing label PDF into a new PDF.
- The user MUST be able to add a carrier by supplying their own provider that conforms to the provider contract.

## Users

- Developer: a person who installs the library into an application and calls its public interface to produce waybill labels.

## Inputs

- Provider: one of `ParcelService::Cj`, an instance of `HanjinProvider`, or a user-supplied object conforming to the provider contract (EL-030).
- Label format: a named physical page definition consisting of name, width in mm, height in mm, and left, right, top, and bottom margins in mm (each margin defaults to 0).
- State: an associative array of label field values that replace default field values.
- Directory path: the directory into which a PDF file is written.
- Filename: the name of the PDF file to write or download (default `waybills.pdf` for EL-008, EL-009, EL-015, EL-016).
- PDF document: an existing PDF document object obtained from EL-025, shared by labels that are output together.
- Repeat count: an integer used by EL-014 (default 1).
- Source PDF path: the path of an existing PDF file to slice.
- Page number: the 1-based page of the source PDF to slice.
- Configuration file: a package-level settings file that defines PDF document title, subject, author, watermark text, watermark image, watermark visibility, display mode, default font, default page size, and default margins.

### CJ label fields

- `city.code`: city routing code slot.
- `city.name`: city name slot.
- `region.code`: region routing code slot.
- `region.name`: region name slot.
- `line_items`: list of item description slots, one line each.
- `seller.name`: sender name slot.
- `seller.phone`: sender phone slot.
- `seller.site`: sales channel slot.
- `seller.address`: sender address slot.
- `receiver.name`: recipient name slot.
- `receiver.phone`: recipient phone slot.
- `receiver.cell_phone`: recipient mobile phone slot.
- `receiver.address`: recipient address slot.
- `printed`: print date slot (Y-m-d).
- `total_printed_count`: total printed count slot.
- `site_order_no`: order number slot.
- `tracking_number`: tracking number slot.
- `delivery_worker`: delivery worker name slot.
- `settlement_type`: settlement type slot.
- `print_date`: print date slot (Y-m-d).
- `box_quantity`: box quantity slot.
- `freight_type`: freight type slot.
- `barcode`: tracking barcode image slot (PNG data URI).

### Hanjin label fields

- `tracking_number`: tracking number slot.
- `tracking_barcode`: tracking barcode image slot (PNG data URI, Interleaved 2 of 5).
- `terminal_code`: destination terminal code slot.
- `terminal_name`: destination terminal name slot.
- `terminal_barcode`: destination terminal barcode image slot (PNG data URI, Code 128).
- `hub_code`: hub code slot.
- `hub_number`: hub number slot.
- `middle_code`: middle classification code slot.
- `center_code`: delivery center code slot.
- `center_name`: delivery center name slot.
- `group_rank`: group rank slot. Rendered on `NS` and `FS` only.
- `worker_name`: delivery worker name slot. Rendered on `NL` and `FS` only.
- `worker_code`: delivery worker code slot. Rendered on `NL` and `FS` only.
- `sender.name`: sender name slot.
- `sender.phone`: sender phone slot.
- `sender.address`: sender address slot.
- `receiver.name`: recipient name slot.
- `receiver.phone`: recipient phone slot.
- `receiver.address`: recipient address slot.
- `item_name`: item name slot.
- `item_quantity`: item quantity slot.
- `order_number`: order number slot.
- `print_date`: print date slot (Y-m-d).
- `freight_type`: freight type slot.
- `delivery_fee`: delivery fee slot.
- `delivery_request`: delivery request slot.
- `region_name`: region name slot.

## Outputs

- PDF file: a PDF written to `<directory path>/<filename>`, or to `<filename>` relative to the current working directory when no directory path is set.
- PDF download: the PDF bytes written to the standard output stream together with download headers carrying the filename.
- Label data array: an associative array with the keys listed in Inputs for the provider.
- Label data list: a list of label data arrays.
- Provider name string: `CJ택배` for `ParcelService::Cj`, `한진택배` for `HanjinProvider`, or the value returned by a user-supplied provider.
- Sliced PDF: a one-page PDF containing the selected page of a source PDF.
- Page geometry per label format:
  - CJ `5-inch`: 123 mm wide by 100 mm high, landscape, zero margins.
  - Hanjin `NS`: 200 mm wide by 101.5 mm high, landscape, zero margins.
  - Hanjin `NL`: 101.5 mm wide by 100 mm high, landscape, zero margins.
  - Hanjin `FS`: 123 mm wide by 100 mm high, landscape, zero margins.
  - A label format whose width is not greater than its height: portrait, width by height.

## Constraints

- Supported environment: PHP 8.2 or later.
- Korean text MUST render in CJ labels using the bundled Nanum Barun Gothic font.
- Korean text MUST render in Hanjin labels using the bundled Nanum Gothic Regular and Bold fonts.
- The product MUST NOT require network access to produce any output.
- The product MUST NOT retain any data across calls other than files written by the user's calls.
- Hanjin printer media settings are 101.5 x 200 mm (`NS`), 101.5 x 100 mm (`NL`), and 100 x 123 mm (`FS`), each with a tolerance of plus or minus 5 mm.

## External Systems

### CJ Logistics

- The product relies on CJ Logistics to issue real tracking numbers and routing codes to the developer.
- The product MUST NOT contact CJ Logistics.

### Hanjin

- The product relies on Hanjin to issue real tracking numbers, terminal codes, and routing codes to the developer.
- The product MUST NOT contact Hanjin.

### Label printer driver [UNVERIFIED]

- The product relies on the printer driver to print the PDF page at the label format size.
- Some printer drivers require swapping width and height for Hanjin `NS` and `FS`.

## User Interface

- The product has no screens.
- Every public operation is an element (EL-xxx). Each element lists its inputs, its observable result, and its outputs.

### Single label entry point (`Waybill`)

- EL-001 `Waybill::of(provider, pdfDocument?, format?)`
  - Purpose: create a label bound to a provider, a label format, and a PDF document.
  - When format is omitted, the provider's default label format is used.
  - When the PDF document is omitted, a new PDF document sized to the label format is created.
- EL-002 `Waybill::make(provider, format?)`
  - Purpose: create a label bound to a provider and a label format without a PDF document.
- EL-003 `mpdf(pdfDocument)`
  - Purpose: attach a PDF document to the label. Returns the same label for chaining.
- EL-004 `state(array)`
  - Purpose: set the state. Replaces any state set earlier on the same label. Returns the same label for chaining.
- EL-005 `path(directory)`
  - Purpose: set the directory path. Returns the same label for chaining.
- EL-006 `toArray()`
  - Purpose: return the label data array.
- EL-007 `format()`
  - Purpose: return the label format bound to the label.
- EL-008 `save(filename = 'waybills.pdf')`
  - Purpose: render the label into its PDF document and write the PDF file.
- EL-009 `download(filename = 'waybills.pdf')`
  - Purpose: render the label into its PDF document and emit the PDF download.
- EL-010 `write(data?)`
  - Purpose: render one label page into the attached PDF document without writing output.
  - When data is supplied, data is rendered exactly as given, without defaults, state, or masking.
- EL-011 String conversion of a label
  - Purpose: return the provider name string.

### Multiple label entry point (`WaybillCollection`)

- EL-012 `WaybillCollection::of(labels?, repeatCount = 1, pdfDocument?)`
  - Purpose: create a label collection bound to a PDF document.
  - When labels are omitted, the collection starts empty.
  - When the PDF document is omitted, a new PDF document using the configuration file default page (123 mm by 100 mm, landscape) is created.
- EL-013 `add(label | list of labels)`
  - Purpose: append one label or each label of a list, in order. Returns the same collection for chaining.
- EL-014 `toArray()`
  - Purpose: return a label data list containing, repeat count times, one label data array per label in insertion order.
- EL-015 `save(filename = 'waybills.pdf')`
  - Purpose: render every label once in insertion order and write the collection's PDF document as a PDF file.
- EL-016 `download(filename = 'waybills.pdf')`
  - Purpose: render every label once in insertion order and emit the collection's PDF document as a PDF download.
- EL-017 `path(directory)`
  - Purpose: set the directory path for EL-015. Returns the same collection for chaining.
- EL-018 `WaybillCollection::make(labels?, repeatCount = 1)`
  - Purpose: create a label collection without a PDF document.

### Label list (`Waybills`)

- EL-019 `Waybills::make(list?)` and `new Waybills(list?)`
  - Purpose: create a label list holding the given labels.
- EL-020 `add(label | list of labels)`
  - Purpose: append one label or each label of a list. Returns the same label list.
- EL-021 `count()`
  - Purpose: return the number of labels held.
- EL-022 Indexed access (read, write, append, exists, remove)
  - Purpose: read, set, append, test, or remove a label by index.
  - Reading an absent index returns null.

### Page extractor (`Slicer`)

- EL-023 `Slicer::of(provider, page, format?)`
  - Purpose: create a page extractor for a provider, a page number, and a label format.
  - When format is omitted, the provider's default label format is used.
- EL-024 `source(path)`, `page(number)`, `save(path, destination = file)`, `download(path)`
  - `source(path)`: set the source PDF path. Returns the same extractor.
  - `page(number)`: replace the page number. Returns the same extractor.
  - `save(path)`: write the sliced PDF to the given path.
  - `save(path, destination)` with a non-file destination: emit the sliced PDF to that destination using the base name of the path as the filename.
  - `download(path)`: emit the sliced PDF as a PDF download using the base name of the path as the filename.

### PDF document factory

- EL-025 `Support\Mpdf::instance(format?)`
  - Purpose: return a new PDF document with the bundled Korean fonts registered and the configuration file applied.
  - When a label format is supplied, page size, orientation, and margins follow that label format and header and footer margins are 0.
  - When no label format is supplied, page size, orientation, and margins follow the configuration file.

### Label format

- EL-026 `new LabelFormat(name, width, height, marginLeft = 0, marginRight = 0, marginTop = 0, marginBottom = 0)`
  - Purpose: define a label format in millimetres.
- EL-027 `LabelFormat::cj5Inch()`
  - Purpose: return the CJ `5-inch` label format (123 x 100 mm, zero margins).
- EL-028 `mpdfOptions()`
  - Purpose: return the page size pair and orientation for the label format.
  - Width greater than height returns `[height, width]` and orientation `L`.
  - Otherwise returns `[width, height]` and orientation `P`.

### Providers

- EL-029 Built-in providers
  - `ParcelService::Cj`: label formats `{'5-inch'}`; default `5-inch`; name `CJ택배`.
  - `HanjinProvider`: label formats `{'NS', 'NL', 'FS'}`, also exposed as constants `HanjinProvider::NS`, `HanjinProvider::NL`, `HanjinProvider::FS`; default `FS`; name `한진택배`.
- EL-030 Provider contract (user-supplied providers)
  - `factoryClass()`: returns the class that produces default label data and accepts state.
  - `labelFormats()`: returns the supported label formats keyed by name.
  - `defaultLabelFormat()`: returns the label format used when none is given.
  - `stub(format)`: returns the template path for the given label format.
  - `templateArea(format)`: returns `[offsetX, offsetY, width, height]` in mm used by EL-024.
  - `name()`: returns the provider name string.

## Functional Requirements

### Label data

- FR-001: EL-006 for `ParcelService::Cj` without state MUST return an array containing every key listed under CJ label fields.
- FR-002: EL-006 for `HanjinProvider` without state MUST return an array containing every key listed under Hanjin label fields.
- FR-003: Each call to EL-006 without state MUST generate new default values.
- FR-004: CJ default `tracking_number` MUST match the pattern `####-####-####` where `#` is a digit.
- FR-005: Hanjin default `tracking_number` MUST be 12 digits.
- FR-006: Hanjin default `terminal_code` MUST be 4 digits.
- FR-007: Hanjin default `order_number` MUST be `TEST-` followed by 6 digits.
- FR-008: Default names, addresses, phone numbers, and company names MUST be Korean-locale values.
- FR-009: For every provider, each top-level key present in the state MUST replace the default value of that key.
- FR-010: For `ParcelService::Cj`, a nested array given in the state for a key MUST replace the entire default nested array for that key.
- FR-011: For `HanjinProvider`, keys given in the state under `sender` MUST replace only those keys of the default `sender` array.
- FR-012: For `HanjinProvider`, keys given in the state under `receiver` MUST replace only those keys of the default `receiver` array.
- FR-013: State keys that are not label fields MUST appear in the label data array with the given value.
- FR-014: For `ParcelService::Cj`, when the state does not contain `barcode`, `barcode` MUST be a Code 128 PNG data URI encoding the resulting `tracking_number`.
- FR-015: For `ParcelService::Cj`, when the state contains `barcode`, `barcode` MUST equal the given value.
- FR-016: For `HanjinProvider`, when the state does not contain `tracking_barcode`, `tracking_barcode` MUST be an Interleaved 2 of 5 PNG data URI encoding the resulting `tracking_number`.
- FR-017: For `HanjinProvider`, when the state does not contain `terminal_barcode`, `terminal_barcode` MUST be a Code 128 PNG data URI encoding the resulting `terminal_code`.
- FR-018: For `HanjinProvider`, when the state contains `tracking_barcode` or `terminal_barcode`, that key MUST equal the given value.

### Hanjin masking

- FR-019: For `HanjinProvider`, masking MUST apply to `sender.name`, `sender.phone`, `sender.address`, `receiver.name`, `receiver.phone`, and `receiver.address` in both default and state values before EL-006 returns and before EL-008, EL-009, EL-015, or EL-016 renders.
- FR-020: A name of fewer than 2 characters MUST remain unchanged.
- FR-021: A name containing Hangul with 2 or 3 characters MUST have its 2nd character replaced by `*`.
- FR-022: A name containing Hangul with 4 characters MUST have its 2nd and 4th characters replaced by `*`.
- FR-023: A name containing Hangul with 5 or more characters MUST have its 2nd character and its 4th through last characters replaced by `*`.
- FR-024: A name without Hangul with 2 to 4 characters MUST have its last character replaced by `*`.
- FR-025: A name without Hangul with 5 or more characters MUST have its 5th through last characters replaced by `*`.
- FR-026: A phone value MUST have its last 4 digits replaced by `*` and all non-digit characters kept in place.
- FR-027: A phone value with fewer than 4 digits MUST have all its digits replaced by `*`.
- FR-028: An address containing a road name ending in `대로`, `로`, or `길` followed by a building number MUST be output as the text up to and including that building number, followed by ` ****`.
- FR-029: An address not matching FR-028 and containing a word ending in `읍`, `면`, or `동` MUST be output as the text up to and including the first such word, followed by ` ****`.
- FR-030: An address matching neither FR-028 nor FR-029 MUST be output as its first 3 whitespace-separated words followed by ` ****`.
- FR-031: Masking MUST NOT apply to `ParcelService::Cj` label data.
- FR-032: Masking MUST NOT apply to data supplied directly to EL-010.

### Single label PDF

- FR-033: EL-008 MUST write a PDF file to `<directory path>/<filename>` when a directory path is set.
- FR-034: EL-008 MUST write a PDF file to `<filename>` when no directory path is set.
- FR-035: EL-009 MUST emit a PDF download named `<filename>`.
- FR-036: The PDF page size and orientation MUST equal the label format geometry listed in Outputs when the PDF document was created by EL-001 or by EL-025 with that label format.
- FR-037: Every CJ label field slot listed in Inputs MUST appear on the rendered CJ `5-inch` label with its label data value.
- FR-038: Each `line_items` entry MUST appear on its own line on the rendered CJ label.
- FR-039: The rendered CJ label MUST show the routing marker `◆` adjacent to the routing code slots.
- FR-040: Every Hanjin label field slot listed in Inputs MUST appear on the rendered Hanjin label with its label data value, subject to the per-format rendering limits stated in Inputs.
- FR-041: Each call to EL-008, EL-009, or EL-010 MUST append one rendered label to the attached PDF document.
- FR-042: EL-011 MUST return the provider name string.

### Multiple label PDF

- FR-043: EL-013 with a list MUST append every label in the list in list order.
- FR-044: EL-014 MUST return `repeatCount x labelCount` label data arrays.
- FR-045: EL-015 MUST write one PDF file containing one page per label, in insertion order, when every label was created with the same PDF document as the collection.
- FR-046: EL-016 MUST emit one PDF download containing one page per label, in insertion order, when every label was created with the same PDF document as the collection.
- FR-047: EL-015 and EL-016 MUST NOT repeat labels according to the repeat count.
- FR-048: A label whose PDF document differs from the collection's PDF document MUST NOT appear in the collection's output.

### Page extraction

- FR-049: EL-024 `save(path)` MUST write a one-page PDF to the given path containing the selected page of the source PDF.
- FR-050: The sliced PDF page size MUST equal the extractor's label format geometry.
- FR-051: The selected page MUST be placed at the provider's template area: `ParcelService::Cj` places it at offset (-40 mm, -46 mm) scaled to 285 x 196 mm.
- FR-052: The selected page MUST be placed at the provider's template area: `HanjinProvider` places it at offset (0, 0) scaled to the label format width and height.
- FR-053: EL-024 `download(path)` MUST emit the sliced PDF as a PDF download named with the base name of the path.

### Label formats and providers

- FR-054: A provider MUST accept a label format whose name is a key of its supported label formats.
- FR-055: A provider MUST reject a label format whose name is not a key of its supported label formats (ERR-001).
- FR-056: Label format support MUST be decided by name only.
- FR-057: EL-001 and EL-023 MUST use the provider's default label format when no label format is given.
- FR-058: A user-supplied provider conforming to EL-030 MUST work with EL-001, EL-002, EL-012, and EL-023 identically to a built-in provider.

## User Flows

### UF-001 Preview a CJ label

- Developer calls EL-001 with `ParcelService::Cj` -> label bound to `5-inch` and a new PDF document.
- Developer calls EL-005 -> directory path set.
- Developer calls EL-008 -> PDF file with one CJ label containing default data (FR-033, FR-037).

### UF-002 Produce a label with real shipment data

- Developer calls EL-001 with a provider and optional label format.
- Developer calls EL-004 with carrier-issued `tracking_number` and recipient fields -> state set.
- Developer calls EL-008 or EL-009 -> PDF containing state values and a barcode encoding the given tracking number (FR-009, FR-014, FR-016).

### UF-003 Produce a Hanjin label in a chosen format

- Developer creates `HanjinProvider` and selects `labelFormats()['NS']`, `['NL']`, or `['FS']`.
- Developer calls EL-001 with that provider and label format.
- Developer calls EL-008 -> PDF page in that format's geometry with masked personal data (FR-019, FR-036, FR-040).

### UF-004 Export label data

- Developer calls EL-001 or EL-002, optionally EL-004, then EL-006 -> label data array (FR-001, FR-002).

### UF-005 Produce multiple labels in one PDF

- Developer calls EL-025 -> shared PDF document.
- Developer calls EL-012 with that PDF document.
- Developer calls EL-013 one or more times with labels created by EL-001 using the same PDF document.
- Developer calls EL-017 and EL-015 -> one PDF file with one page per label (FR-045).

### UF-006 Extract one label page

- Developer calls EL-023 with a provider and page number.
- Developer calls `source(path)` -> source PDF set.
- Developer calls `save(path)` or `download(path)` -> one-page PDF (FR-049, FR-053).

### UF-007 Add a carrier

- Developer implements EL-030 with a data class, supported label formats, a template per label format, a template area per label format, and a name.
- Developer passes the provider to EL-001 -> label rendered with that provider's template and data (FR-058).

## Error Conditions

- ERR-001: A label format whose name is not supported by the provider is used with EL-008, EL-009, EL-010, or EL-024 `save`/`download` -> an invalid-argument error is raised naming the provider and label format; no PDF is written or emitted.
- ERR-002: EL-026 is called with an empty name, a width less than or equal to 0, or a height less than or equal to 0 -> an invalid-argument error is raised; no label format is created.
- ERR-003: EL-008, EL-009, or EL-010 is called on a label created by EL-002 without a prior EL-003 -> an error is raised; no PDF is written or emitted.
- ERR-004: EL-015 or EL-016 is called on a collection created by EL-018 -> an error is raised; no PDF is written or emitted.
- ERR-005: For `HanjinProvider`, `tracking_number` contains a non-digit character and the state does not contain `tracking_barcode` -> an invalid-character error is raised by EL-006, EL-008, or EL-009.
- ERR-006: EL-024 `save` or `download` is called before `source(path)` -> an error is raised; no PDF is written or emitted.
- ERR-007: EL-024 `save` or `download` is called with a source PDF path that does not exist or a page number outside the source PDF -> an error is raised; no PDF is written or emitted.
- ERR-008: The built-in CJ or Hanjin template file for a supported label format is missing -> an error is raised by EL-008, EL-009, or EL-010; no PDF is written or emitted.

## Non-Goals

- The product MUST NOT book shipments with any carrier.
- The product MUST NOT issue or validate tracking numbers, terminal codes, or routing codes.
- The product MUST NOT provide CJ label formats other than `5-inch`.
- The product MUST NOT combine labels of different label formats in one PDF.

## Acceptance Criteria

- AC-001: EL-006 for `ParcelService::Cj` returns all 15 top-level CJ keys (FR-001).
- AC-002: EL-006 for `HanjinProvider` returns all 23 top-level Hanjin keys (FR-002).
- AC-003: EL-004 with `['6' => 'new value']` followed by EL-006 returns `'new value'` at key `'6'` (FR-009, FR-013).
- AC-004: For `ParcelService::Cj`, EL-004 with `['seller' => ['name' => 'X']]` followed by EL-006 returns `seller` equal to `['name' => 'X']` (FR-010).
- AC-005: For `HanjinProvider`, EL-004 with `['receiver' => ['name' => '홍길동', 'phone' => '010-1234-5678', 'address' => '서울특별시 강남구 테헤란로 123 4층']]` followed by EL-006 returns `receiver` equal to `['name' => '홍*동', 'phone' => '010-1234-****', 'address' => '서울특별시 강남구 테헤란로 123 ****']` (FR-012, FR-021, FR-026, FR-028).
- AC-006: For `HanjinProvider`, EL-004 with `['receiver' => ['name' => 'X']]` followed by EL-006 returns a `receiver` that still contains `phone` and `address` (FR-012).
- AC-007: For `ParcelService::Cj`, EL-004 with `['tracking_number' => '5856-0852-8903']` followed by EL-006 returns `barcode` equal to the Code 128 PNG data URI of `5856-0852-8903` (FR-014).
- AC-008: For `HanjinProvider`, EL-004 with `['tracking_number' => 'AB12']` followed by EL-006 raises an invalid-character error (ERR-005).
- AC-009: EL-001 with `ParcelService::Cj` followed by EL-008 creates a PDF file whose single page is 123 x 100 mm landscape (FR-033, FR-036).
- AC-010: EL-001 with `HanjinProvider` and each of `NS`, `NL`, `FS`, followed by EL-008, creates a PDF file whose page is 200 x 101.5 mm, 101.5 x 100 mm, and 123 x 100 mm respectively (FR-036).
- AC-011: EL-001 with `ParcelService::Cj` followed by EL-009 writes non-empty output to the standard output stream (FR-035).
- AC-012: String conversion of EL-001 with `ParcelService::Cj` returns `CJ택배` (FR-042).
- AC-013: String conversion of EL-001 with `HanjinProvider` returns `한진택배` (FR-042).
- AC-014: EL-012 with a label list of 1 label and repeat count 5, followed by EL-014, returns 5 label data arrays (FR-044).
- AC-015: EL-012 with a label list of 3 labels and repeat count 5, followed by EL-014, returns 15 label data arrays (FR-044).
- AC-016: EL-013 with a list of 3 labels on an empty collection with repeat count 5, followed by EL-014, returns 15 label data arrays (FR-043, FR-044).
- AC-017: EL-012 with a shared PDF document, 5 calls to EL-013 with labels sharing that PDF document, and EL-015 creates a PDF file with 5 pages (FR-045).
- AC-018: EL-016 on a collection with 1 label writes non-empty output to the standard output stream (FR-046).
- AC-019: EL-023 with `ParcelService::Cj` and page 3, `source` set to the PDF from AC-017, followed by `save(path)`, creates a one-page PDF at the path (FR-049).
- AC-020: EL-023 followed by `download(path)` writes non-empty output to the standard output stream (FR-053).
- AC-021: EL-023 followed by `page(3)` sets the selected page to 3 (EL-024).
- AC-022: EL-001 with `ParcelService::Cj` and `new LabelFormat('8-inch', 100, 150)` followed by EL-008 raises an invalid-argument error (FR-055, ERR-001).
- AC-023: EL-001 with `HanjinProvider` and `LabelFormat::cj5Inch()` followed by EL-008 raises an invalid-argument error (FR-055, ERR-001).
- AC-024: EL-026 with an empty name raises an invalid-argument error (ERR-002).
- AC-025: EL-026 with width 0 raises an invalid-argument error (ERR-002).
- AC-026: EL-028 for a 123 x 100 label format returns `[100, 123]` and `L` (EL-028).
- AC-027: EL-028 for a 100 x 150 label format returns `[100, 150]` and `P` (EL-028).
- AC-028: EL-002 with `ParcelService::Cj`, EL-003 with a PDF document from EL-025, and EL-008 creates a PDF file (FR-033).
- AC-029: EL-002 with `ParcelService::Cj` followed by EL-008 without EL-003 raises an error (ERR-003).
- AC-030: A user-supplied provider conforming to EL-030 passed to EL-001, followed by EL-008, creates a PDF file rendered from that provider's template (FR-058).
