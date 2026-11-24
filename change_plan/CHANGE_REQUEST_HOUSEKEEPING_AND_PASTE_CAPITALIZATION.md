# Change Request — Housekeeping Department & Sentence Capitalization for Department Activities

## Objective
Implement these two improvements in the existing Daily Reporting System. Inspect the project first and integrate changes without rebuilding or disrupting completed milestones.

## Change 1 — Add Housekeeping Department

The Departmental Activities section is missing **Housekeeping**.

### Requirements
- Add Housekeeping to the department list used by Departmental Activities.
- Put it in the appropriate order. Inspect current department ordering and project docs; maintain consistent ordering across UI, database seeders, templates/configuration, and report output.
- Ensure it is available to the intended hotel/property configuration and is not duplicated when seeders run repeatedly.
- Users must enter Housekeeping activities using the same workflow and input component as other departments.
- Activities must persist, restore when reopening/resuming drafts, and appear in report output/export wherever Departmental Activities are included.
- If departments are maintained by a seeder or template configuration, update the source of truth. Do not hard-code it only in the UI.
- Preserve existing departments and data. Do not reset or destructively alter the database.

### Verification
- Confirm Housekeeping is in the source of truth and rendered in Departmental Activities.
- Confirm save/resume persistence and report output/export inclusion where applicable.
- Confirm seeding is idempotent and creates no duplicate Housekeeping entries.

## Change 2 — Capitalize First Letter of Each Sentence on Paste in Department Activities

When users paste text into Departmental Activities input fields, automatically capitalize the first alphabetic character of each sentence.

### Example 1

Pasted:
```text
guest request extra pillow. room 2310 need maintenance. housekeeping checked the room.
```

Expected:
```text
Guest request extra pillow. Room 2310 need maintenance. Housekeeping checked the room.
```

### Example 2

Pasted:
```text
replaced light bulb; cleaned corridor; checked minibar
```

Expected:
```text
Replaced light bulb; cleaned corridor; checked minibar
```

For this second example, capitalize only the beginning of the text, not each semicolon-separated phrase, unless the existing requirements or implementation explicitly treat those as separate sentences.

### Functional requirements
- Apply only to paste actions in Departmental Activities inputs.
- Capitalize the first alphabetic character at the start of text and after sentence-ending punctuation (`.`, `!`, `?`) followed by whitespace.
- Preserve the rest of each sentence's casing; do not uppercase the entire text or convert to title case.
- Preserve room numbers, quantities, abbreviations, punctuation, currency, names, and formatting as much as possible.
- Do not capitalize after semicolons or commas unless they form sentence boundaries under the chosen implementation.
- Handle multiline paste; treat each new line as the beginning of a new entry/sentence and capitalize its first alphabetic character.
- Work together with existing list-marker and paste normalization without breaking it.
- Do not alter Guest Experience paragraph input or unrelated fields.
- Avoid regressions to cursor position, input dispatch, Livewire/Alpine synchronization, undo-paste, or current normalization.

### Implementation guidance
- Inspect the current paste handler/list normalizer and Departmental Activities inputs.
- Prefer extending the existing paste pipeline rather than adding competing listeners.
- Scope using the Departmental Activities input attribute/component or another reliable mechanism; do not apply globally to all textareas.
- Handle Unicode letters reasonably and avoid changing already-correct text.
- Add frontend tests if supported; otherwise document the verification performed.

## Required Workflow
1. Read project docs and applicable `AGENTS.md`.
2. Inspect department source of truth, seeders, template/config, Departmental Activities UI, persistence, and output/export.
3. Inspect current paste-normalization code.
4. Check existing department records/configuration to prevent duplicates.
5. Briefly state the plan, then implement both changes.
6. Keep scope focused; do not start unrelated milestones.
7. Add/update tests for Housekeeping source/idempotency, UI/persistence, capitalization after `.`, `!`, `?`, multiline text, text without sentence punctuation, list-marker normalization, and scope isolation from Guest Experience.
8. Run relevant Laravel tests and frontend build.
9. Review Git diff and ensure no unrelated or destructive changes.

## Acceptance Criteria
- [ ] Housekeeping exists in the department source of truth.
- [ ] Housekeeping appears in Departmental Activities.
- [ ] Housekeeping activities save and restore.
- [ ] Housekeeping appears in report output/export where appropriate.
- [ ] Re-running seeders does not create duplicates.
- [ ] Paste capitalization applies to Departmental Activities only.
- [ ] `.`, `!`, and `?` sentence boundaries are supported.
- [ ] Multiline paste is handled.
- [ ] Remaining sentence casing is preserved.
- [ ] Room numbers, quantities, punctuation, and list normalization remain intact.
- [ ] Guest Experience and unrelated fields are unaffected.
- [ ] Tests pass and frontend build succeeds.

## Final Report
Report:
1. Files changed.
2. Where Housekeeping was added and how duplicate prevention works.
3. Paste capitalization behavior with examples.
4. Tests run and results.
5. Frontend build result.
6. Assumptions or remaining issues.
