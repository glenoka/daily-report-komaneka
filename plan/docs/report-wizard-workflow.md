# Report Wizard Workflow

## User flow
1. Dashboard → **Create Today's Report**.
2. System creates/opens a draft for today's date.
3. Complete five tabs, saving each independently.
4. On paste into list textarea, markers are removed automatically before pasted text appears.
5. Save each tab; use Save & Continue to advance.
6. Return to previous tabs any time; draft persists on refresh/exit.
7. After Tab 5, open **Review Report**.
8. Review the consolidated report and use Edit section links if corrections are needed.
9. Click **Create Report** to finalize.
10. System generates DOCX and PDF, with independent retry if one export fails.

## Five tabs
### Tab 1 — General Information
- Today's Overview (list)
- Forecast for Tomorrow (list)

### Tab 2 — Guest Experience & Concern
- Paragraph/text field
- Preserve paragraphs and line breaks
- No list-prefix normalization

### Tab 3 — Departmental Activities
- Engineering (list)
- Food & Beverage — Production (list)
- Food & Beverage — Service (list)
- SPA (list)
- Security (list)
- IT (list)

### Tab 4 — Operational Updates
- Operational Updates (list)

### Tab 5 — Activities and Works Plan
- Activities and Works Plan (list)

## Tab states
- Not started
- In progress
- Saved
- Needs attention

## Actions
- Save
- Save & Continue
- Previous
- Review Report
- Back to Editing
- Create Report

## Paste cleanup
- List fields intercept paste.
- Recognized markers are removed per line.
- Clean text is inserted at caret when feasible.
- Original paste can optionally be restored via Undo.
- Do not destructively strip uncertain symbols.
- Normalization is also enforced server-side before list items are persisted/exported.

## Final review
- Show the report in official template order.
- Show completeness/required-field warnings.
- Provide Edit links for each step.
- Finalization should persist before exports.
- DOCX and PDF generation status must be separately visible and retryable.
