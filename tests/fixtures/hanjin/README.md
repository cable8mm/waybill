# Hanjin waybill print guide fixtures

These files preserve the three output examples, field-position diagrams, and masking guide from Hanjin's official developer guide. They are reference material for the built-in `HanjinProvider` layouts; sample contents are illustrative, not valid shipment data.

| Type | Layout image | Field diagram | Dimensions stated in the layout |
| --- | --- | --- | --- |
| NS | `ns-layout.jpg` | `ns-fields.png` | Layout: 200 × 101.5 mm (two 100 mm panels; the guide divides the height into 22 mm + 80 mm). |
| NL | `nl-layout.jpg` | `nl-fields.png` | 101.5 × 100 mm. |
| FS | `fs-layout.jpg` | `fs-fields.png` | Layout: 123 × 100 mm. |

PlayAuto's Hanjin print setup gives media sizes NS 101.5 × 200 mm, NL 101.5 × 100 mm, and FS 100 × 123 mm, each with ±5 mm tolerance. These are printer width/height settings; the Hanjin layout examples show NS and FS rotated relative to those settings. The PDF templates follow the example's visual orientation. Some printer drivers may require swapping width and height.

The guide specifies Nanum Gothic, Interleaved 2 of 5 for the waybill-number barcode, and Code 128 for the destination-terminal barcode. The displayed shipment contents are examples and should not be used as actual shipment data.

`masking.png` documents Hanjin's personal-information masking rules. For the package's generated Hanjin labels, sender and receiver names, phone numbers, and detailed addresses are masked before rendering. The templates use the Nanum Gothic font specified by Hanjin; its SIL Open Font License is included at `fonts/NanumGothic-OFL.txt`.

## Source

- Official guide: <https://developers.hanjin.com/printwbl>
- Print media settings: <https://www.plto.com/customer/HelpDesc/gmp/13789>
- Retrieved: 2026-10-29
- Original image files:
  - <https://developers.hanjin.com/files/masking_new.png?v=1763450256000>
  - <https://developers.hanjin.com/files/ns_new.jpg?v=1760938993000>
  - <https://developers.hanjin.com/files/ns2.jpg?v=1765941990000>
  - <https://developers.hanjin.com/files/nl_new.jpg?v=1760939004000>
  - <https://developers.hanjin.com/files/nl2.jpg?v=1765941999000>
  - <https://developers.hanjin.com/files/fs_new.jpg?v=1760938981000>
  - <https://developers.hanjin.com/files/fs2.jpg?v=1765942008000>
