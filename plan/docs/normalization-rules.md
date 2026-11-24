# List Normalization Specification

## Objective
Convert pasted multi-line list content into clean ordered item strings. Bullet markers are presentation, not part of stored item content.

## Pipeline
1. Normalize CRLF/CR to LF.
2. Split by LF.
3. Trim line.
4. Skip blank lines.
5. Remove recognized leading markers repeatedly (safe bounded loop).
6. Trim again.
7. Skip if empty.
8. Preserve order and content.
9. Return array of item strings.

## Markers to recognize
- Bullet: `•`, `●`, `○`, `◦`, `▪`, `▫`, `‣`, `⁃`
- Dash: `-`, `–`, `—` only when used as a leading list marker followed by whitespace/content
- Asterisk: `*`
- Numeric: `1.`, `2)`, `10.`
- Alphabetic: `a.`, `b)`, `A.`
- Checkboxes: `[ ]`, `[x]`, `[X]`, `☐`, `☑`, `☒`
- Checkmarks: `✓`, `✔`
- Emoji list markers: support common leading emoji markers, ideally via explicit marker list or Unicode-aware detection; do not strip arbitrary emoji from actual content without clear list-marker context.
- Combined markers: e.g. `• - 1. Item`

## Safety constraints
- Do not strip numbers embedded in text.
- Do not strip symbols from the middle of lines.
- Do not split paragraphs into sentences automatically.
- Do not treat `Room 101 has 2 AC units` as a list.
- Do not treat `F&B 2nd floor operation` as a list.
- Preserve punctuation and meaningful content.
- Make normalization reversible in the UI when possible by retaining raw pasted input temporarily for Undo.

## Examples
Input:
`• AC checked`
`- Pump checked`
`1. Generator checked`
`✓ Water pressure checked`

Normalized:
`AC checked`
`Pump checked`
`Generator checked`
`Water pressure checked`

Input:
`Room 101 has 2 AC units`
Output unchanged.

Input:
`F&B 2nd floor operation`
Output unchanged.

## Tests
Cover each marker, multiple prefixes, blank lines, whitespace, punctuation, embedded numbers, content beginning with symbols that are not list markers, and long input.
