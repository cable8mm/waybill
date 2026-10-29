# ARCHITECTURE

## Purpose

- Define how waybill label data is produced, rendered into a paged document, and emitted.
- Define which component owns each responsibility and which component owns each piece of state.
- Define the invariants that every implementation and every added carrier MUST preserve.

## Core Concepts

- Provider: the carrier-specific authority. Owns the carrier name, the set of supported label formats, the default label format, the data factory identity, the template for each supported label format, and the template area for each supported label format.
- Label Format: an immutable value describing a physical page: name, width, height, and four margins in millimetres. Identified for support checks by name only.
- Data Factory: a carrier-specific producer of one label data record. Owns default field generation, state merging, derived-field synchronization, and carrier-mandated data transformation.
- State: a caller-supplied partial record of field values that overrides defaults.
- Label Data Record: one fully merged, carrier-specific record of field values ready for rendering or export.
- Template: a carrier-and-format-specific layout that turns a Label Data Record into page content.
- Document: a paged output container with a fixed page geometry. Receives rendered pages and emits bytes.
- Label: a binding of one Provider, one Label Format, an optional Document, a State, and an output directory.
- Label List: an ordered container of Labels.
- Collection: a binding of one Label List, one Document, a repeat count, and an output directory.
- Template Area: a rectangle `[offsetX, offsetY, width, height]` in millimetres at which an imported page is placed on a new page.
- Slicer: a binding of one Provider, one Label Format, a source document path, and a page number.
- Output Sink: the destination of emitted Document bytes: a file path, a download stream, or another destination selected by the caller.
- Configuration: package-level document settings (default page, margins, fonts, metadata, watermark).

## System Flow

### SF-1 Single label to output

1. Caller creates a Label with a Provider, an optional Label Format, and an optional Document.
2. Label resolves the Label Format: given value, else the Provider's default.
3. When the Document is omitted at creation through the auto-creating entry point, Label obtains a new Document from the Document Factory for the resolved Label Format.
4. Caller optionally sets State and output directory.
5. Caller requests save or download.
6. Label requests a Label Data Record from the Provider's Data Factory with the State.
7. Label requests the Template for the Label Format from the Provider.
8. Provider validates the Label Format name against its supported set and returns the Template or fails.
9. Label renders the Template with the Label Data Record and appends the result to its Document.
10. Label instructs its Document to emit bytes to the Output Sink.

### SF-2 Label data export

1. Caller requests the Label Data Record from a Label.
2. Label requests a Label Data Record from the Provider's Data Factory with the State.
3. Label returns the record. No Document is touched.

### SF-3 Multiple labels to one output

1. Caller obtains one Document from the Document Factory.
2. Caller creates every Label with that Document.
3. Caller creates a Collection with that Document and adds Labels in order.
4. Caller requests save or download on the Collection.
5. Collection instructs the Label List to render every Label once in insertion order.
6. Each Label executes SF-1 steps 6 through 9 into its own Document.
7. Collection instructs its own Document to emit bytes to the Output Sink.

### SF-4 Collection data export

1. Caller requests data from a Collection.
2. Collection instructs the Label List to produce, repeat-count times, one Label Data Record per Label in insertion order.
3. Collection returns the list of records.

### SF-5 Page extraction

1. Caller creates a Slicer with a Provider, a page number, and an optional Label Format.
2. Slicer resolves the Label Format: given value, else the Provider's default.
3. Caller sets the source document path and optionally replaces the page number.
4. Caller requests save or download.
5. Slicer obtains a new Document from the Document Factory for the Label Format.
6. Slicer imports the selected page of the source document into the new Document.
7. Slicer requests the Template Area for the Label Format from the Provider. Provider validates the Label Format name and returns the area or fails.
8. Slicer places the imported page at the Template Area.
9. Slicer instructs the new Document to emit bytes to the Output Sink.

### SF-6 Data Factory record production

1. Generate a full default record.
2. Merge State over the default record using the carrier's merge policy.
3. Regenerate each derived field from its source field unless State supplies the derived field.
4. Apply carrier-mandated transformation.
5. Return the Label Data Record.

## Components

- C-01 Label (`Waybill`)
- C-02 Collection (`WaybillCollection`)
- C-03 Label List (`Waybills`)
- C-04 Slicer (`Slicer`)
- C-05 Provider Contract (`WaybillProvider`)
- C-06 CJ Provider (`ParcelService::Cj`)
- C-07 Hanjin Provider (`HanjinProvider`)
- C-08 Data Factory Base (`Factory`)
- C-09 CJ Data Factory (`CjFactory`)
- C-10 Hanjin Data Factory (`HanjinFactory`)
- C-11 Hanjin Masker (`HanjinDataMasker`)
- C-12 Sample Value Source (`Faker`)
- C-13 Label Format (`LabelFormat`)
- C-14 Document Factory (`Support\Mpdf`)
- C-15 Configuration (`config.php`)
- C-16 Templates (per Provider and Label Format)

## Component Responsibilities

### C-01 Label

- Responsibilities:
  - Bind one Provider and one resolved Label Format for its lifetime.
  - Hold State, output directory, and a Document reference.
  - Obtain a Label Data Record from the Provider's Data Factory.
  - Render the Provider's Template for its Label Format into its Document.
  - Request emission of its Document to an Output Sink.
  - Expose the Provider name as its string form.
- Inputs: Provider, Label Format (optional), Document (optional), State, output directory, filename, direct record (optional, render-only).
- Outputs: Label Data Record, one rendered page appended to its Document, emitted Document bytes, Provider name.
- Ownership: owns State and output directory. Does not own the Document; holds a reference. Does not own field definitions, merge policy, masking, Templates, or support checks.

### C-02 Collection

- Responsibilities:
  - Hold a Label List, a repeat count, an output directory, and a Document reference.
  - Trigger rendering of every Label once in insertion order.
  - Request emission of its own Document to an Output Sink.
  - Produce repeated data export through the Label List.
- Inputs: Label List (optional), Labels to add, repeat count, Document (optional), output directory, filename.
- Outputs: list of Label Data Records, emitted Document bytes.
- Ownership: owns repeat count and output directory. Does not own the Document; holds a reference. Does not own Label rendering, Label Data, or Label Documents.

### C-03 Label List

- Responsibilities:
  - Store Labels in insertion order.
  - Append one Label or each Label of a list.
  - Provide count and indexed read, write, append, existence check, and removal.
  - Invoke render on each Label in order.
  - Produce repeat-count passes of Label Data Records in order.
- Inputs: Labels, index, repeat count.
- Outputs: Labels, count, list of Label Data Records.
- Ownership: owns ordering and membership. Does not own any Label's state or Document.

### C-04 Slicer

- Responsibilities:
  - Bind one Provider and one resolved Label Format.
  - Hold source document path and page number.
  - Create a new Document per extraction.
  - Import one page and place it at the Provider's Template Area.
  - Request emission to an Output Sink. A file sink receives the full path. Every other sink receives the base name of the path.
- Inputs: Provider, page number, Label Format (optional), source document path, output path, Output Sink selection.
- Outputs: emitted one-page Document bytes.
- Ownership: owns source path, page number, and the extraction Document. Does not own Template Area values or support checks.

### C-05 Provider Contract

- Responsibilities:
  - Define the six capabilities every Provider MUST supply: data factory identity, supported label formats keyed by name, default label format, Template for a label format, Template Area for a label format, name.
- Inputs: none.
- Outputs: the capability set consumed by C-01, C-02, C-04.
- Ownership: owns the boundary between carrier-independent components and carrier-specific components.

### C-06 CJ Provider

- Responsibilities:
  - Declare supported label formats `{5-inch}` and default `5-inch`.
  - Resolve the CJ `5-inch` Template.
  - Return Template Area `[0, 0, width, height]` of the label format.
  - Reject unsupported label format names.
  - Identify C-09 as its Data Factory.
  - Return name `CJ택배`.
- Inputs: Label Format.
- Outputs: Template, Template Area, supported formats, default format, name, Data Factory identity.
- Ownership: owns all CJ-specific format, template, and area decisions.

### C-07 Hanjin Provider

- Responsibilities:
  - Declare supported label formats `{NS, NL, FS}` with geometries 200 x 101.5, 101.5 x 100, 123 x 100 mm and default `FS`.
  - Resolve the Hanjin Template for the label format name.
  - Return Template Area `[0, 0, width, height]` of the label format.
  - Reject unsupported label format names.
  - Identify C-10 as its Data Factory.
  - Return name `한진택배`.
- Inputs: Label Format.
- Outputs: Template, Template Area, supported formats, default format, name, Data Factory identity.
- Ownership: owns all Hanjin-specific format, template, and area decisions.

### C-08 Data Factory Base

- Responsibilities:
  - Define the produce-record lifecycle (SF-6).
  - Hold State for one production.
  - Provide the default merge policy: top-level replacement.
- Inputs: State.
- Outputs: Label Data Record.
- Ownership: owns the lifecycle and the default merge policy. Does not own field definitions.

### C-09 CJ Data Factory

- Responsibilities:
  - Generate the full CJ default record.
  - Merge State with top-level replacement, then key-level replacement inside `seller`, `receiver`, `city`, and `region`.
  - Regenerate `barcode` from `tracking_number` unless State supplies `barcode`.
- Inputs: State.
- Outputs: CJ Label Data Record.
- Ownership: owns CJ field set, CJ default value formats, CJ merge policy, CJ barcode synchronization.

### C-10 Hanjin Data Factory

- Responsibilities:
  - Generate the full Hanjin default record.
  - Merge State with top-level replacement, then key-level replacement inside `sender` and `receiver`.
  - Regenerate `tracking_barcode` from `tracking_number` unless State supplies `tracking_barcode`.
  - Regenerate `terminal_barcode` from `terminal_code` unless State supplies `terminal_barcode`.
  - Pass the record through C-11 as the final step.
- Inputs: State.
- Outputs: masked Hanjin Label Data Record.
- Ownership: owns Hanjin field set, Hanjin default value formats, Hanjin merge policy, Hanjin barcode synchronization, and the decision to mask.

### C-11 Hanjin Masker

- Responsibilities:
  - Transform `name`, `phone`, and `address` of `sender` and `receiver` according to the masking rules in PRODUCT_SPEC.
  - Leave every other field unchanged.
- Inputs: Hanjin Label Data Record.
- Outputs: masked Hanjin Label Data Record.
- Ownership: owns the masking rules. Stateless.

### C-12 Sample Value Source

- Responsibilities:
  - Supply Korean-locale sample values to Data Factories.
  - Encode a string into a barcode image value in the symbology requested by the Data Factory.
- Inputs: value requests, barcode source string, symbology.
- Outputs: sample values, barcode image values.
- Ownership: owns sample value generation and barcode encoding. Does not decide which fields exist or which symbology a field uses.

### C-13 Label Format

- Responsibilities:
  - Hold name, width, height, and four margins.
  - Reject an empty name or non-positive width or height at creation.
  - Derive page size pair and orientation: landscape when width is greater than height, else portrait.
  - Provide the CJ `5-inch` value.
- Inputs: name, width, height, margins.
- Outputs: page size pair, orientation, field values.
- Ownership: owns physical geometry. Does not own carrier support.

### C-14 Document Factory

- Responsibilities:
  - Create a new Document from Configuration.
  - Register the bundled fonts.
  - When a Label Format is supplied, override page size, orientation, and margins from it and set header and footer margins to 0.
  - Apply document metadata, watermark, and display settings from Configuration.
- Inputs: Configuration, Label Format (optional).
- Outputs: a new empty Document.
- Ownership: owns Document construction. Sole creator of Documents inside the product.

### C-15 Configuration

- Responsibilities:
  - Supply default page geometry, margins, fonts, metadata, watermark, and display settings.
- Inputs: none.
- Outputs: settings consumed by C-14.
- Ownership: owns package-wide document defaults. Read only by C-14.

### C-16 Templates

- Responsibilities:
  - Lay out one Label Data Record onto one page for one Provider and one Label Format.
  - Escape every text field.
  - Insert barcode image values unescaped.
- Inputs: Label Data Record.
- Outputs: page content.
- Ownership: owns visual placement of fields. Does not transform or derive field values.

## Responsibility Boundaries

- Carrier-independent components (C-01, C-02, C-03, C-04, C-13, C-14, C-15) MUST NOT contain carrier-specific branches, field names, geometries, or names.
- Carrier-specific knowledge MUST live only in a Provider, its Data Factory, its Templates, and carrier-specific transformers (C-11).
- Label format support decisions MUST be made only by the Provider.
- Physical geometry MUST be defined only by Label Format values.
- Field values MUST be produced and transformed only by the Data Factory and the transformers it invokes.
- Templates MUST NOT compute or alter field values.
- Documents MUST be created only by C-14 inside the product. Callers MAY supply their own Document to C-01 and C-02.
- Output emission MUST be requested only by C-01, C-02, and C-04 on a Document they reference or own.
- Configuration MUST be read only by C-14.

## Data Flow

- DF-1 State -> C-01 -> Data Factory (C-09 or C-10) -> Label Data Record.
- DF-2 Default record (C-12 values) + State -> merge -> derived-field regeneration (C-12 barcodes) -> carrier transformation (C-11 for Hanjin) -> Label Data Record.
- DF-3 Label Data Record + Template (from Provider for Label Format) -> page content -> appended to the Document referenced by C-01.
- DF-4 Direct record supplied to C-01 render -> Template -> page content. Bypasses DF-1 and DF-2.
- DF-5 Document -> Output Sink (file at `<directory>/<filename>` or `<filename>`, or download stream named `<filename>`).
- DF-6 C-02 render -> C-03 -> each C-01 (DF-1, DF-3) -> C-02's Document -> Output Sink.
- DF-7 C-02 data export -> C-03 -> repeat count x each C-01 (DF-1) -> list of Label Data Records.
- DF-8 Source document page -> C-04 new Document at Template Area (from Provider) -> Output Sink.
- DF-9 Configuration + Label Format -> C-14 -> new Document.

## Architectural Rules

- AR-01: Every Label Data Record used for rendering or export MUST be produced through the Data Factory produce-record lifecycle, except a direct record supplied to the render operation.
- AR-02: State merge MUST occur before derived-field regeneration.
- AR-03: Derived-field regeneration MUST occur before carrier transformation.
- AR-04: A derived field supplied in State MUST NOT be regenerated.
- AR-05: Each Label Data Record production MUST start from a newly generated default record.
- AR-06: Setting State on a Label MUST replace the previous State.
- AR-07: A Label MUST resolve its Label Format once, at creation.
- AR-08: Label format support MUST be checked at Template resolution and at Template Area resolution, not at Label or Slicer creation.
- AR-09: Label format support MUST be decided by name membership in the Provider's supported set.
- AR-10: Each render of a Label MUST append exactly one page to that Label's Document.
- AR-11: A Collection MUST render each Label exactly once per output request, regardless of repeat count.
- AR-12: A Collection MUST emit only its own Document.
- AR-13: The Slicer MUST create a new Document for every extraction.
- AR-14: Label Format MUST be immutable after creation.
- AR-15: Every Document page geometry MUST come from either a Label Format or Configuration, never from a Provider directly.
- AR-16: A Provider MUST be usable by C-01, C-02, and C-04 only through the C-05 capability set.

## Failure Boundaries

- FB-01 Label Format creation: invalid name or dimensions fail at C-13 creation. No Label Format exists.
- FB-02 Unsupported label format: fails in the Provider during Template or Template Area resolution. Propagates through C-01, C-02, or C-04. No page is appended and no bytes are emitted.
- FB-03 Missing Template for a supported label format: fails in the Provider during Template resolution. Same propagation as FB-02.
- FB-04 Missing Document on a Label or Collection: fails at render or emit. No bytes are emitted.
- FB-05 Barcode encoding failure (source value invalid for the symbology): fails in C-12 during Data Factory production. Propagates through C-01 data export, render, or output. No Label Data Record is returned.
- FB-06 Missing source path, unreadable source document, or out-of-range page: fails in C-04 during import. No bytes are emitted.
- FB-07 Output Sink failure: fails at Document emission. Pages already appended remain in the Document.
- No component catches, converts, or suppresses a failure. Every failure propagates to the caller unchanged.
- A failure in one Label during Collection rendering stops the Collection operation. Pages appended by earlier Labels remain in the Document.

## Non-Goals

- No registry or discovery of Providers. Callers pass a Provider instance.
- No shared Document lifecycle management. The architecture does not attach, detach, or verify Documents across Labels and Collections.
- No validation that Labels in a Collection share the Collection's Document or Label Format.
- No caching of Label Data Records. Every request produces a new record.
- No retry, rollback, or partial-output cleanup.
- No carrier-independent field schema. Each Data Factory defines its own fields.
- No transformation step for carriers other than those whose Data Factory invokes one.
- No persistence component.
- No network-facing component.

## Architectural Invariants

- INV-01: Every Label is bound to exactly one Provider and exactly one Label Format for its lifetime.
- INV-02: A Label Format accepted by a Provider has a name present in that Provider's supported set.
- INV-03: Every Hanjin Label Data Record returned by C-10 has passed through C-11.
- INV-04: No CJ Label Data Record passes through C-11.
- INV-05: In every Label Data Record from C-09, `barcode` encodes `tracking_number` unless State supplied `barcode`.
- INV-06: In every Label Data Record from C-10, `tracking_barcode` encodes `tracking_number` and `terminal_barcode` encodes `terminal_code`, each unless State supplied it.
- INV-07: The number of pages a Collection output adds equals the number of Labels in its Label List that reference its Document.
- INV-08: The number of records in a Collection data export equals repeat count multiplied by Label count.
- INV-09: Every Document is created by C-14 or supplied by the caller.
- INV-10: Carrier-independent components contain no carrier-specific knowledge.
- INV-11: Templates render field values exactly as present in the Label Data Record, with text escaped and barcode images unescaped.

## Resolved Decisions

- RD-01: Ambiguous: where the unsupported label format check happens. Decided: in the Provider at Template and Template Area resolution (AR-08). Why: PRODUCT_SPEC ERR-001 names render and extraction operations as the failure point, not creation.
- RD-02: Ambiguous: who owns the Document when Labels and a Collection share one. Decided: the creator (caller or C-14 on the caller's behalf) owns it; Label and Collection hold references only. Why: PRODUCT_SPEC FR-045 and FR-048 make output depend on sharing a Document, which requires external ownership.
- RD-03: Ambiguous: whether repeat count affects PDF output. Decided: repeat count applies only to data export (AR-11). Why: PRODUCT_SPEC FR-047.
- RD-04: Ambiguous: order of masking relative to barcode regeneration. Decided: merge, then barcode regeneration, then masking (AR-02, AR-03). Why: masking touches only sender and receiver fields, so barcodes encode unmasked source fields; PRODUCT_SPEC FR-019 requires masking before return and render.
- RD-05: Ambiguous: whether masking is a Provider or Data Factory responsibility. Decided: the Hanjin Data Factory invokes C-11 as the last lifecycle step. Why: PRODUCT_SPEC FR-019 requires masked values from data export (EL-006), which does not involve Templates; FR-032 requires direct records to bypass masking, which only holds if masking lives in the Data Factory path.
- RD-06: Ambiguous: merge depth. Decided: top-level replacement by default (C-08); CJ adds key-level replacement for `seller`, `receiver`, `city`, and `region` (C-09); Hanjin adds key-level replacement for `sender` and `receiver` (C-10). Why: PRODUCT_SPEC FR-010, FR-011, FR-012.
- RD-07: Ambiguous: Collection Document page geometry when created without a Document. Decided: C-14 creates it from Configuration without a Label Format. Why: PRODUCT_SPEC EL-012.
- RD-08: Ambiguous: Slicer filename for non-file sinks. Decided: base name of the given path; file sink uses the full path. Why: PRODUCT_SPEC EL-024.
- RD-09: Ambiguous: whether Configuration or Label Format wins for page geometry. Decided: Label Format overrides Configuration for page size, orientation, and margins, and forces header and footer margins to 0. Why: PRODUCT_SPEC EL-025 and FR-036.
- RD-10: Ambiguous: which component owns barcode symbology choice. Decided: the Data Factory chooses the symbology per field; C-12 only encodes. Why: symbology is carrier-specific (CJ Code 128; Hanjin Interleaved 2 of 5 and Code 128) and carrier knowledge belongs to carrier components.
- RD-11: Missing responsibility: verifying that Labels share the Collection's Document. Decided: not provided (Non-Goals). Why: PRODUCT_SPEC FR-048 defines the observable result of non-shared Documents instead of requiring rejection.
- RD-12: Conflict check: PRODUCT_SPEC and TECH_STACK. Decided: no conflict found. Why: every component above can be built within TECH_STACK constraints without adding network access or persistence.
