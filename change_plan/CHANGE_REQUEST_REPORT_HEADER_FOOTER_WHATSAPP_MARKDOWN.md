# Change Request — Remove Report Header/Footer & Add WhatsApp Markdown Preview

## Objective
Update the existing Daily Reporting System with two changes:
1. Remove the report header and footer from generated report output.
2. Add a Markdown preview on the initial/main report view so the user can copy the report and paste it into WhatsApp with clean formatting.

Inspect the current implementation and project documentation first. This is an incremental change, not a rebuild.

## 1. Remove Report Header and Footer

### Requirements
- Remove the report header and footer from the report output.
- Report content should begin directly with the report body.
- No footer should be appended after the body.
- Inspect where the header/footer are generated (template, Blade view, export/print renderer, service, or layout) and remove them from the relevant report output path(s).
- Preserve report body, section order, formatting, and data.
- Do not remove unrelated application page headers/footers unless they are part of report output.
- If multiple output formats exist (preview, print, DOCX, PDF), inspect them and apply consistently to the intended report output paths.
- Do not destructively modify stored report data.

## 2. Add Markdown Preview for WhatsApp Copying

### Goal
On the initial/main report view or report table screen, add a clearly labeled Markdown preview so the user can copy report text and paste it into WhatsApp.

### Requirements
- Add a section such as **WhatsApp Report / Markdown Preview** on the initial/main report view.
- Generate Markdown from actual saved report data, not hard-coded example content.
- Use the same section ordering and report body content as the existing report output.
- The preview must exclude report header and footer.
- Use WhatsApp-friendly formatting:
  - `*bold*` for headings/section labels where appropriate (WhatsApp uses single asterisks for bold).
  - Plain text bullets or `-` for list entries.
  - Readable line breaks and spacing.
  - Avoid tables, code fences, or complex Markdown that WhatsApp may not render reliably.
- Ensure preview reflects the latest report data and follows existing save-state conventions. Do not show stale or misleading data.
- Provide a **Copy** button that copies the generated Markdown to the clipboard.
- Show clear copy-success feedback.
- Handle clipboard failure gracefully with appropriate feedback/fallback.
- Keep UI responsive and consistent with the existing design system.

### Suggested output style (illustrative only)

```text
*DAILY REPORT — 24 NOVEMBER 2026*

*Today's Overview*
- Occupancy: 20.00%
- Rooms Occupied: 15 Rooms
- Out of Order: 3 Rooms — #2310, #2321 & #3311 (Preventive Maintenance Program)
- Number of Guests: 33 Pax (31 Adults + 2 Children)
- Check-ins: 7 Rooms
- Check-outs: 7 Rooms
- Property Tours: Room #2311 & #4103
- Manager on Duty: Ariawan, Agung Janardana, Laksmi

*Guest Experience & Concern*
Guest experience content...

*Departmental Activities*
*Engineering*
- Activity 1
- Activity 2

*Housekeeping*
- Activity 1

*Operational Updates*
- Update 1

*Activities and Works Plan*
- Plan 1
```

The example is not a new fixed template. Inspect the current approved report template and use its actual section structure, ordering, and available data. Do not invent data or silently omit sections.

## 3. Source of Truth and Data Integrity
- Generate Markdown from the existing report model/data and report structure.
- Reuse existing report-generation logic/template where practical; avoid duplicating formatting rules.
- Preview and copy output must follow the same section order and content as report body.
- Do not store a separately maintained duplicate of the Markdown unless existing architecture clearly requires it.
- Do not alter report data, delete sections, or change unrelated workflows.

## 4. UI/UX
- Place the preview on the initial/main report view as requested, not in settings.
- Distinguish visual report from WhatsApp Markdown preview.
- Provide readable preview and copy button.
- Optionally allow preview to expand/collapse if needed to avoid an overly long page.
- Ensure copy works on supported browsers/mobile where possible.
- Provide accessible button label and feedback.

## 5. Required Workflow
1. Read project docs and applicable `AGENTS.md`.
2. Inspect current main report view, report renderer, header/footer source, report model, and export/print paths.
3. Identify current report body assembly and section ordering.
4. Briefly provide implementation plan, then proceed.
5. Implement only this change request; do not rebuild or start unrelated milestones.
6. Add/update tests for header/footer removal, body and section order, Markdown generation from real data, exclusion of header/footer, WhatsApp formatting, copy action/feedback where testable, empty sections, and regressions.
7. Run relevant Laravel tests and frontend build.
8. Review Git diff and avoid unrelated/destructive changes.

## 6. Acceptance Criteria
- [ ] Report output has no header.
- [ ] Report output has no footer.
- [ ] Report body and section order are preserved.
- [ ] Main/initial report view includes a WhatsApp Markdown preview.
- [ ] Preview is generated from actual report data.
- [ ] Preview excludes header and footer.
- [ ] Formatting is WhatsApp-friendly.
- [ ] Copy button copies the Markdown.
- [ ] Success feedback and graceful failure handling are present.
- [ ] Preview follows existing save-state conventions and does not display stale data.
- [ ] Existing data and unrelated workflows remain intact.
- [ ] Relevant tests pass and frontend build succeeds.

## Final Report
After implementation, report:
1. Files changed/created.
2. Where header/footer were removed and affected output paths.
3. Markdown generation source and method.
4. Preview and copy behavior.
5. Tests run/results.
6. Build result.
7. Remaining assumptions/issues.
8. Confirm no unrelated milestones were started.
