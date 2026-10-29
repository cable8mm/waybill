# Hanjin waybill print guide fixtures

These files preserve the three output examples and their field-position diagrams from Hanjin's official developer guide. They are reference material for carrier-specific layout work; they are not generated invoices or evidence that this package has implemented Hanjin output.

| Type | Layout image | Field diagram | Dimensions stated in the layout |
| --- | --- | --- | --- |
| NS | `ns-layout.jpg` | `ns-fields.png` | Two 100 mm-wide panels side by side; 22 mm + 80 mm = 102 mm high. |
| NL | `nl-layout.jpg` | `nl-fields.png` | 100 mm wide. The guide image does not print a numeric height. |
| FS | `fs-layout.jpg` | `fs-fields.png` | 123 × 100 mm. |

Do not treat the NL image's visual aspect ratio as an authoritative height measurement. Confirm the exact media size with Hanjin before implementing or printing this form.

The guide specifies Nanum Gothic, Interleaved 2 of 5 for the waybill-number barcode, and Code 128 for the destination-terminal barcode. The displayed shipment contents are examples and should not be used as actual shipment data.

## Source

- Official guide: <https://developers.hanjin.com/printwbl>
- Retrieved: 2026-10-29
- Original image files:
  - <https://developers.hanjin.com/files/ns_new.jpg?v=1760938993000>
  - <https://developers.hanjin.com/files/ns2.jpg?v=1765941990000>
  - <https://developers.hanjin.com/files/nl_new.jpg?v=1760939004000>
  - <https://developers.hanjin.com/files/nl2.jpg?v=1765941999000>
  - <https://developers.hanjin.com/files/fs_new.jpg?v=1760938981000>
  - <https://developers.hanjin.com/files/fs2.jpg?v=1765942008000>
