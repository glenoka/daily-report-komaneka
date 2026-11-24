# Implementation Tasks & Milestones

Status convention: `[ ]` not started, `[~]` in progress, `[x]` done.

## Milestone 0 — Planning
- [x] PRD
- [x] Design specification
- [x] Architecture specification
- [x] Report template specification
- [x] Normalization specification
- [x] CLI agent rules
- [x] Initial agent prompt

## Milestone 1 — Foundation
- [x] Create Laravel 13 project (PHP 8.3+)
- [x] Configure MySQL
- [x] Configure auth
- [x] Configure roles/permissions
- [x] Create Hotel model/migration/seed
- [x] Create base app shell/navigation
- [x] Configure Tailwind
- [x] Create dashboard shell
- [x] Add environment documentation
- [x] Tests pass

## Milestone 2 — Report CRUD & Schema
- [x] Daily report model/migration
- [x] Report section/item models and migrations
- [x] Department model/migration/seeder
- [x] Report template model/migration/seeder
- [x] Create report action with draft resumption
- [x] Duplicate report action
- [x] Date/hotel validation
- [x] Status transitions
- [x] Authorization policies (DailyReportPolicy)
- [x] Feature & Unit tests

## Milestone 3 — Template & Sections Engine
- [x] Seed default report template (5 tabs, 11 sections)
- [x] Implement configurable template service (ReportTemplateService)
- [x] Department configuration (6 official departments)
- [x] Section hierarchy & field type mappings (text vs list)
- [x] Template tests

## Milestone 4 — Normalization engine
- [ ] Normalize line endings
- [ ] Prefix recognition
- [ ] Repeated prefix removal
- [ ] Numbering and alphabet markers
- [ ] Checkbox/checkmark markers
- [ ] Emoji markers
- [ ] Empty-line handling
- [ ] Preserve embedded numbers/symbols
- [ ] Unit test edge cases
- [ ] UI paste/format action
- [ ] Editable normalized items
- [ ] Add/delete/reorder items
- [ ] Undo/reset raw input
- [ ] Live preview

## Milestone 5 — Auto-save
- [ ] Debounced save
- [ ] Save status UI
- [ ] Error and retry
- [ ] Restore after refresh
- [ ] Unsaved changes warning
- [ ] Save tests

## Milestone 6 — Preview
- [ ] Document-like preview
- [ ] Dynamic hotel/date
- [ ] Dynamic day name
- [ ] Section order
- [ ] Paragraph rendering
- [ ] Bullet rendering
- [ ] Empty section handling
- [ ] Preview/export consistency

## Milestone 7 — DOCX
- [ ] PHPWord install/config
- [ ] DOCX exporter
- [ ] Native bullets
- [ ] Heading and paragraph styles
- [ ] Page margins
- [ ] Filename format
- [ ] DOCX smoke test

## Milestone 8 — PDF
- [ ] PDF strategy selected
- [ ] PDF exporter
- [ ] Pagination and overflow
- [ ] Font setup
- [ ] Bullet and heading styles
- [ ] PDF smoke test

## Milestone 9 — Report history/dashboard
- [ ] Report list
- [ ] Search
- [ ] Date filter
- [ ] Hotel filter
- [ ] Status filter
- [ ] Dashboard counts
- [ ] Export/download actions

## Milestone 10 — QA & deployment
- [ ] Authorization audit
- [ ] Validation audit
- [ ] Input sanitization
- [ ] Responsive QA
- [ ] Full automated test suite
- [ ] Production env guide
- [ ] Backup guide
- [ ] Deployment guide
- [ ] Final README update

## Phase 2 backlog
- [ ] Approval workflow
- [ ] Email delivery
- [ ] Enhanced multi-property permissions
- [ ] Audit log UI
- [ ] Analytics
- [ ] AI-assisted rewrite (opt-in)
- [ ] Multiple template presets


## Updated workflow tasks (Wizard UX)
- [ ] Create Today's Report entry action (today's date, hotel default)
- [ ] Prevent duplicate same-day drafts or offer continue existing draft
- [ ] Implement five-step/tab navigation
- [ ] Tab 1 General Information (Overview + Forecast)
- [ ] Tab 2 Guest Experience & Concern
- [ ] Tab 3 Departmental Activities (all six departments)
- [ ] Tab 4 Operational Updates
- [ ] Tab 5 Activities and Works Plan
- [ ] Step status: Not started/In progress/Saved/Needs attention
- [ ] Save current tab
- [ ] Save & Continue
- [ ] Back/Previous navigation
- [ ] Resume incomplete report
- [ ] Auto-clean list markers on paste (no process button)
- [ ] Undo paste normalization (optional MVP)
- [ ] Review Report consolidated preview
- [ ] Edit links from review to each tab
- [ ] Create Report finalization action
- [ ] Generate DOCX and PDF after finalization
- [ ] Independent retry for failed exports
