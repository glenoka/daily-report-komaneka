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

## Milestone 4 — Normalization Engine & List Input
- [x] Normalize line endings (CRLF / CR -> LF)
- [x] Bullet prefix recognition (• ● ○ ◦ ▪ ▫ ‣ ⁃)
- [x] Repeated prefix removal (safe bounded loop)
- [x] Numbering (1. 2) 10. 1-) and alphabet (a. b) A.) markers
- [x] Checkbox ([ ] [x] [X] ☐ ☑ ☒) and checkmark (✓ ✔) markers
- [x] Common list emoji markers
- [x] Empty-line handling and line trimming
- [x] Content preservation guarantee (embedded numbers & technical text)
- [x] Comprehensive unit tests (ReportNormalizerServiceTest)
- [x] Client-side zero-click paste cleaner (resources/js/list-normalizer.js)
- [x] Reusable ListInput Blade component with item counter & Undo support (resources/views/components/list-input.blade.php)

## Milestone 5 — Auto-save
- [x] Debounced save
- [x] Save status UI
- [x] Error and retry
- [x] Restore after refresh
- [x] Unsaved changes warning
- [x] Save tests

## Milestone 6 — Preview
- [x] Document-like preview
- [x] Dynamic hotel/date
- [x] Dynamic day name
- [x] Section order
- [x] Paragraph rendering
- [x] Bullet rendering
- [x] Empty section handling
- [x] Preview/export consistency

## Milestone 7 — DOCX
- [x] PHPWord install/config
- [x] DOCX exporter
- [x] Native bullets
- [x] Heading and paragraph styles
- [x] Page margins
- [x] Filename format
- [x] DOCX smoke test

## Milestone 8 — PDF
- [x] PDF strategy selected
- [x] PDF exporter
- [x] Pagination and overflow
- [x] Font setup
- [x] Bullet and heading styles
- [x] PDF smoke test

## Milestone 9 — Report history/dashboard
- [x] Report list
- [x] Search
- [x] Date filter
- [x] Hotel filter
- [x] Status filter
- [x] Dashboard counts
- [x] Export/download actions

## Milestone 10 — QA & deployment
- [x] Authorization audit
- [x] Validation audit
- [x] Input sanitization
- [x] Responsive QA
- [x] Full automated test suite
- [x] Production env guide
- [x] Backup guide
- [x] Deployment guide
- [x] Final README update

## Phase 2 backlog
- [ ] Approval workflow
- [ ] Email delivery
- [ ] Enhanced multi-property permissions
- [ ] Audit log UI
- [ ] Analytics
- [ ] AI-assisted rewrite (opt-in)
- [ ] Multiple template presets


## Updated workflow tasks (Wizard UX)
- [x] Create Today's Report entry action (today's date, hotel default)
- [x] Prevent duplicate same-day drafts or offer continue existing draft
- [x] Implement five-step/tab navigation
- [x] Tab 1 General Information (Overview + Forecast)
- [x] Tab 2 Guest Experience & Concern
- [x] Tab 3 Departmental Activities (all six departments)
- [x] Tab 4 Operational Updates
- [x] Tab 5 Activities and Works Plan
- [x] Step status: Not started/In progress/Saved/Needs attention
- [x] Save current tab
- [x] Save & Continue
- [x] Back/Previous navigation
- [x] Resume incomplete report
- [x] Auto-clean list markers on paste (no process button)
- [x] Undo paste normalization (optional MVP)
- [x] Review Report consolidated preview
- [x] Edit links from review to each tab
- [x] Create Report finalization action
- [x] Generate DOCX and PDF after finalization
- [x] Independent retry for failed exports
