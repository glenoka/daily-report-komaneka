# Product Requirements Document (PRD)
## Daily Reporting System

### 1. Product overview
A web-based daily hotel reporting application. Users fill report fields, paste list-style text from external sources, and the system cleans the pasted prefixes and recreates a consistent bullet list. Reports can be previewed and exported as Word and PDF.

### 2. Goals
- Reduce daily report preparation time.
- Keep reports consistent with the official document structure.
- Preserve user-entered content while removing inconsistent list markers.
- Provide editable drafts, preview, DOCX/PDF export, and report history.
- Keep templates configurable instead of hardcoded.

### 3. Non-goals for MVP
- AI rewriting or translation.
- Automatic data collection from PMS, POS, engineering systems, or other hotel platforms.
- Email sending.
- Multi-step approval workflow (Phase 2).
- Analytics beyond basic dashboard counts.
- Mobile native app.

### 4. Primary users
- Report User: create, edit, save, preview, complete, and download reports.
- Administrator: manage users, hotel/property settings, report templates, departments, and access.
- Manager/Approver (Phase 2): review, return, approve reports.

### 5. User stories
1. As a report user, I can create a report for a hotel and date.
2. I can paste text containing bullets, numbers, checkboxes, or emoji markers.
3. The system normalizes list prefixes and displays a clean bullet preview.
4. I can edit, add, remove, and reorder list items after normalization.
5. I can enter Guest Experience as paragraph text with line breaks.
6. I can save a draft and continue later.
7. I can preview the complete report before export.
8. I can generate and download DOCX and PDF.
9. I can find previous reports by date, hotel, status, and keyword.
10. An admin can manage hotels, users, departments, and templates.

### 6. Report fields
- Hotel name: dynamic, required.
- Report date: required, defaults to current date; editable.
- Day name: derived from report date.
- Today's Overview: list.
- Forecast for Tomorrow: list.
- Guest Experience & Concern: paragraph text.
- Departmental Activities:
  - Engineering: list.
  - Food & Beverage — Production: list.
  - Food & Beverage — Service: list.
  - SPA: list.
  - Security: list.
  - IT: list.
- Operational Updates: list.
- Activities and Works Plan: list.

Fields are optional in draft. Required fields can be configured per template. Empty sections may be hidden in export based on template setting.

### 7. Workflow
Create Draft → Fill Sections → Auto-save → Preview → Complete → Export DOCX/PDF.
MVP has no approval gate. A completed report may still be editable according to permissions.

### 8. Functional requirements
#### FR-01 Authentication
Users must authenticate. Authorization controls report and configuration access.

#### FR-02 Report CRUD
Create, view, edit, delete, and duplicate reports. Enforce hotel/date duplicate policy, configurable as warning or restriction.

#### FR-03 Dynamic template sections
Section structure and fields come from template configuration. Avoid hardcoding export output to one template.

#### FR-04 List normalization
Normalize pasted list text: remove list prefixes, ignore blank lines, preserve content, return ordered items. Prefixes may include bullet symbols, dashes, numbered or alphabetic list markers, checkbox/checkmark and emoji markers. Multiple prefixes may be present.

#### FR-05 List editing
After normalization, users can add/edit/delete/reorder individual list items. Preview uses the application's bullet style, not original markers.

#### FR-06 Text fields
Guest Experience supports paragraphs and line breaks; it is not normalized as a list.

#### FR-07 Auto-save
Debounced auto-save (target 1–2 seconds after inactivity), save status, error handling, and retry. Draft survives refresh/navigation.

#### FR-08 Preview
Preview includes hotel, date/day, intro, sections, department labels, and normalized bullets. Preview should closely match export.

#### FR-09 Export
Generate DOCX and PDF. Native Word bullet formatting is preferred. Export must not contain raw source prefixes or UI artifacts.

#### FR-10 Report history
Search/filter by date, hotel, status, creator, and keyword.

#### FR-11 Dashboard
Show counts for reports in current month, drafts, completed, and approved (approved is Phase 2).

#### FR-12 Template configuration
Admin can configure hotel name, intro, section order, labels, field types, required state, and hide-empty behavior.

#### FR-13 Audit trail
Record key events such as create, edit, complete, export, delete. Full audit UI can be Phase 2.

### 9. List normalization rules
- Normalize line endings.
- Split on newline.
- Trim each line.
- Remove recognized list prefixes repeatedly where safe.
- Ignore blank lines.
- Preserve remaining content, punctuation, and meaningful symbols.
- Do not split a single paragraph into sentences unless explicit rule is enabled.
- Do not remove embedded numbers (e.g. “Room 101 has 2 AC units”).
- Do not remove text such as “F&B 2nd floor operation”.
- Keep order.
- Return structured list items.
- Show preview and allow correction.

### 10. Non-functional requirements
- Responsive desktop/tablet UI.
- Security: CSRF, validation, authorization, sanitized inputs, private downloads.
- Reliable save and export.
- Maintainable modular code.
- Test coverage for normalization edge cases.
- Accessible keyboard navigation and labels.
- Clear feedback and error states.

### 11. MVP scope
- Login and role access.
- Report CRUD.
- All template fields.
- List normalization.
- Item editing/reordering.
- Auto-save.
- Preview.
- DOCX and PDF.
- Report history and basic dashboard.

### 12. Phase 2
- Approval workflow.
- Multiple configurable templates.
- Multi-property enhanced permissions.
- Email delivery.
- AI assistance (optional, explicit opt-in).
- Analytics.
- Audit log UI.
- Export presets and branding options.

### 13. Acceptance criteria
- All source template sections represented.
- User paste content is normalized and original list markers are removed.
- Text content remains intact in representative edge cases.
- Empty lines ignored.
- User can edit normalized items.
- Draft auto-saves and can be reopened.
- Preview matches export structure.
- DOCX/PDF downloads succeed and are readable.
- Report list filters work.
- Permissions prevent unauthorized report access.
- Automated tests cover normalizer and key CRUD/export flows.


## 14. Wizard / Tab-based reporting workflow (UPDATED)

### Main flow
1. User clicks **Create Today's Report**.
2. System creates a draft for today's date (hotel is selected/defaulted).
3. User completes five tabs in order:
   - Tab 1: General Information
   - Tab 2: Guest Experience & Concern
   - Tab 3: Departmental Activities
   - Tab 4: Operational Updates
   - Tab 5: Activities and Works Plan
4. Each tab is an independent step with its own Save action and saved status.
5. User can navigate between completed/in-progress tabs and resume an existing draft.
6. After all tabs are saved, user opens **Review Report**.
7. Review shows a consolidated report preview and allows returning to any tab for edits.
8. User clicks **Create Report** (finalize).
9. System generates DOCX and PDF and presents download actions.

### Tab behavior
- Tab 1 includes Today's Overview and Forecast for Tomorrow.
- Tab 2 includes Guest Experience & Concern paragraph/text input.
- Tab 3 includes department list inputs: Engineering, F&B Production, F&B Service, SPA, Security, IT.
- Tab 4 includes Operational Updates.
- Tab 5 includes Activities and Works Plan.
- A tab can show status: Not started, In progress, Saved, or Needs attention.
- User can save and continue, save and go next, or return to prior tab.
- The system should preserve draft data when the user leaves the wizard or refreshes.

### Paste normalization UX (preferred)
- On paste into a list textbox, intercept clipboard text and normalize it immediately.
- Remove recognized leading bullet, dash, numbered, checkbox, checkmark, and supported emoji-list markers from each pasted line.
- The pasted text shown in the textarea should already be clean, without the original bullet prefixes.
- Do not require a separate “Process/Format” button for the normal happy path.
- User can still manually edit the cleaned text.
- Normalization must not remove numbers or symbols that are part of content.
- If automatic paste normalization is uncertain, preserve the line rather than destructively stripping content.
- A small unobtrusive status may indicate “List formatting cleaned automatically”.
- Support an optional Undo paste action restoring the original clipboard text.
- For typed input, normalization may occur on paste only; when manually typing, the UI may display clean preview or normalize when saving, but must not aggressively alter user keystrokes.

### Save/navigation
- Each tab has **Save** and **Save & Continue** actions.
- Save persists the current tab and marks it Saved.
- Save & Continue persists and navigates to the next tab.
- User may navigate back to previous tabs.
- Next tab can be opened even if optional fields are empty; required-field validation is template-driven.
- If user exits before completing all tabs, draft remains available in report history/dashboard.
- Save state must be visible and avoid ambiguous unsaved data.

### Review & finalize
- Review Report consolidates all five tabs using official template structure.
- Review screen offers section navigation/edit links.
- “Create Report” finalizes the report (status Completed/Generated).
- On successful finalization, generate DOCX and PDF.
- Downloads are shown with clear format labels.
- If one export fails, report finalization should not lose data; show retry for failed export.
- Finalized report editing policy must be explicit. MVP recommendation: allow reopening/editing via “Revise Report”, creating a new version or returning status to Draft, with confirmation and audit trail. Avoid silent overwrites of already downloaded output.

### Acceptance criteria for wizard
- User starts a report for today with one action.
- Five tabs match the official template groupings.
- Pasted bullets are cleaned automatically before the text appears, where feasible.
- No separate process button is required for normal paste.
- Each tab can be saved independently.
- User can resume incomplete reports.
- Progress/status is visible.
- Review is consolidated and editable by returning to a tab.
- Create Report generates DOCX and PDF.
- Export failure does not erase or invalidate saved report content.
