# Daily Reporting System — CLI Starter Specification

This package is the planning and implementation specification for a hotel Daily Reporting System.

## Goal
Allow hotel users to enter daily report content through a web UI, paste text from WhatsApp/email/notes, automatically normalize list prefixes, preview the report, and export Word (.docx) and PDF according to the supplied report template.

## Source template structure
- Dear Bapak and Ibu,
- Intro line with hotel and report date
- 1. GENERAL INFORMATION
  - Today's Overview (list)
  - Forecast for Tomorrow (list)
- 2. GUEST EXPERIENCE & CONCERN (text)
- 3. DEPARTMENTAL ACTIVITIES
  - Engineering (list)
  - Food & Beverage — Production (list)
  - Food & Beverage — Service (list)
  - SPA (list)
  - Security (list)
  - IT (list)
- 4. OPERATIONAL UPDATES (list)
- 5. ACTIVITIES AND WORKS PLAN (list)

## Suggested stack
- Laravel 13, PHP 8.3+
- MySQL 8
- Livewire
- Tailwind CSS 4
- Authentication and role-based authorization
- PHPWord for DOCX generation
- PDF export: evaluate DomPDF for MVP; consider LibreOffice conversion if Word/PDF parity is required
- Pest or PHPUnit tests

## Start
1. Read `PRD.md`, `DESIGN.md`, `ARCHITECTURE.md`, `AGENTS.md`.
2. Review the template spec in `docs/report-template.md`.
3. Implement only Milestone 1 from `TASKS.md`.
4. Run tests, update task status and docs before moving forward.

## Latest workflow update
See `docs/report-wizard-workflow.md` for the required five-tab wizard, save/resume behavior, automatic paste cleanup, review, finalization, and DOCX/PDF export.
