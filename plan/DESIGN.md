# Design System & UI Specification
## Daily Reporting System

### Product design direction
A calm, professional hotel operations tool. Prioritize speed, clarity, and reliable data entry over decorative complexity.

### Visual principles
- Professional hospitality tone.
- Clean, spacious layout.
- Clear hierarchy between report sections and fields.
- Neutral background with one restrained primary accent.
- Consistent input and button styles.
- Statuses must be clear without relying on color alone.
- Responsive desktop/tablet layout.

### Suggested palette
- Primary: deep teal `#0F766E`
- Primary hover: `#115E59`
- Background: `#F7F8FA`
- Surface: `#FFFFFF`
- Text: `#1F2937`
- Muted: `#6B7280`
- Border: `#E5E7EB`
- Success: `#15803D`
- Warning: `#B45309`
- Danger: `#B91C1C`

### Typography
- UI: Inter or system sans-serif.
- Heading: medium/semibold.
- Body: 14–16 px.
- Report preview typography should approximate export output.

### Layout
- Sidebar navigation: Dashboard, Daily Reports, Templates, Departments, Users, Settings.
- Main content max width around 1200–1400 px.
- Report editor uses section navigation or accordion.
- Sticky header shows report date, status, and save state.
- Preview may use a document-like centered sheet.

### Report editor
- Hotel and date at top.
- General Information.
- Guest Experience.
- Department Activities with individual cards/accordion panels.
- Operational Updates.
- Activities and Works Plan.
- Each list field includes textarea paste area, normalized item list, item add/edit/remove/reorder controls, item count, and preview.
- Avoid forcing users to manually add bullets in the textarea.

### List input interaction
1. Paste or type source text in raw input.
2. Normalize on paste or user action; implementation may offer “Format List” button and automatic preview.
3. Display detected items as separate editable rows.
4. App-rendered bullet is visual/native, not part of item text.
5. Offer undo/reset to raw pasted text if normalization was incorrect.
6. Empty lines are ignored.
7. Keep source raw text temporarily for undo/debugging if privacy and storage policy allow; default should not persist raw input separately unless required.

### Navigation and states
- Draft, Completed, Approved (Approved Phase 2).
- Save states: Saving, Saved, Save failed.
- Empty states with clear CTA.
- Confirmation dialog for delete.
- Unsaved-change warning.
- Error state with retry.

### Responsive
- Desktop: sidebar + editor/preview.
- Tablet: collapsible sidebar, stacked editor/preview.
- Small screens: single column; preview opened as separate tab/view.

### Accessibility
- Visible labels.
- Keyboard accessible controls.
- Focus states.
- Contrast.
- Avoid color-only status.
- Reorder controls should have keyboard alternatives.

### UI components
- App shell.
- Sidebar.
- Page header.
- Status badge.
- Date picker.
- Hotel selector.
- Report section card.
- List paste textarea.
- Normalized list editor.
- Reorder item controls.
- Preview sheet.
- Export buttons.
- Confirmation dialog.
- Toast/notification.


## Wizard / 5-tab flow (latest requirement)

### Entry point
Primary CTA: **Create Today's Report**. This creates a draft dated today. If a draft for the same hotel/date already exists, offer **Continue Draft** instead of creating a duplicate.

### Tab navigation
Use a clear five-step tab/stepper:
1. General Information
2. Guest Experience
3. Departmental Activities
4. Operational Updates
5. Activities & Work Plan

Each tab has:
- Step number and title.
- Status (Not started / In progress / Saved / Needs attention).
- Save button.
- Save & Continue button (except final tab).
- Back/Previous and Next navigation as appropriate.
- Validation feedback at field level.
- A saved indicator with timestamp or relative time.

Tabs should be clickable for already started steps and allow returning to revise prior content. Do not block optional sections.

### Automatic paste cleanup
For list textareas, intercept paste:
- Read pasted plain text.
- Normalize recognized list markers line-by-line.
- Insert cleaned content into the textarea immediately.
- Prevent the original clipboard text from being inserted if normalization succeeded.
- No separate “Process” button in normal flow.
- Show a small toast/inline notice such as “Bullet formatting cleaned automatically”.
- Optional Undo action restores the original pasted text.
- If uncertain whether a leading symbol is a list marker, preserve it to avoid content loss.
- Guest Experience paragraph textarea must not apply list normalization.

### Suggested list input interaction
Raw textarea remains a familiar paste target. Once pasted, content is clean (without list prefix characters). On save, split non-empty lines into stored ordered items, preserving order. A formatted preview displays application-generated bullets. The user can edit the cleaned text before saving.

### Review screen
Document-style preview with:
- Hotel and report date.
- Intro line.
- All five workflow groups in template order.
- Normalized bullets.
- Paragraph content.
- Edit link/button on each group.
- Validation/completion summary.
- Final actions: **Back to Editing**, **Create Report**.

### Final generation state
After Create Report:
- Show generation progress or loading state.
- On success, show **Download Word (.docx)** and **Download PDF (.pdf)**.
- If export is partial, clearly identify which format failed and offer retry.
- Do not clear or lose draft/report data on generation failure.
