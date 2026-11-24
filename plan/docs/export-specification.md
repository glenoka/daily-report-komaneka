# Export Specification

## Output
- Word `.docx`
- PDF `.pdf`

## Filename
`Daily_Report_{HotelSlug}_{DD_Month_YYYY}.{extension}`

Example:
`Daily_Report_Komaneka_at_Bisma_24_November_2026.docx`

## Formatting
- Greeting: bold, 12 pt.
- Intro: 11 pt.
- Main section headings: bold, 12 pt.
- Department headings: bold, 11 pt.
- List items: 10.5–11 pt, application-generated bullet.
- Body font: Aptos/Arial or a safe installed equivalent.
- Line spacing: approx. 1.15.
- Paragraph spacing: approx. 4–6 pt.
- Margins: approx. 2.0–2.5 cm.

## DOCX
Prefer native Word bullets and editable paragraphs. Keep report content editable.

## PDF
MVP can use DomPDF if styling/layout is adequate. If fidelity to Word is important, evaluate LibreOffice conversion or another supported conversion pipeline. Document the chosen approach and its operational requirements.

## Requirements
- No duplicate bullets.
- No raw source numbering.
- No UI controls.
- Preserve paragraph text and intentional line breaks.
- Page breaks should not split headings from the first item when possible.
- Verify long reports and empty sections.
