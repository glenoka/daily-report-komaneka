# Architecture
## Daily Reporting System

### Stack
- Laravel 13 / PHP 8.3+
- MySQL 8
- Livewire
- Tailwind CSS 4
- Authentication (Laravel starter kit or equivalent)
- PHPWord for DOCX
- PDF: DomPDF for MVP; evaluate LibreOffice-based conversion for high fidelity
- PHPUnit or Pest

### Architecture rules
- Keep UI components thin.
- Business logic in Services/Actions.
- Template structure stored as configuration/database JSON.
- Exporters consume a normalized report DTO/data structure.
- Normalization must be independent from UI.
- Authorization policies must protect every report route and export.
- No direct raw user input in document HTML without sanitization.
- Use transactions for report and section/item updates.

### Proposed folders
app/
  Models/
    User.php
    Hotel.php
    DailyReport.php
    ReportTemplate.php
    ReportSection.php
    ReportItem.php
    Department.php
  Services/
    ReportNormalizerService.php
    ReportTemplateService.php
    ReportPreviewService.php
    DocxGeneratorService.php
    PdfGeneratorService.php
    ReportService.php
  Actions/
    CreateDailyReport.php
    SaveReportDraft.php
    NormalizeListInput.php
    CompleteDailyReport.php
    GenerateReportExport.php
  DTO/
    DailyReportData.php
    ReportSectionData.php
    ReportItemData.php
  Policies/
    DailyReportPolicy.php
  Livewire/
    Reports/
      Index.php
      Create.php
      Edit.php
      Preview.php
    Components/
      ListInput.php
      NormalizedListEditor.php
      ReportSectionEditor.php

### Data model
- hotels: id, name, code, is_active, timestamps
- users: standard auth fields + role/hotel relationship
- departments: id, hotel_id nullable, name, code, sort_order, is_active
- report_templates: id, hotel_id nullable, name, config JSON, is_default, is_active
- daily_reports: id, hotel_id, report_template_id, report_date, created_by, status, guest_experience, completed_at, timestamps
- report_sections: id, daily_report_id, section_key, title, field_type, sort_order, content nullable, timestamps
- report_items: id, report_section_id, content, sort_order, timestamps

### Relationships
Hotel has many users, departments, reports, and optional templates.
DailyReport belongs to hotel, creator, template; has many sections.
ReportSection belongs to report; has many items.
ReportItem belongs to section.

### Report DTO
Export pipeline should convert database data into a stable DTO:
- hotel_name
- report_date
- day_name
- greeting
- intro
- ordered sections
- ordered fields
- text content
- list items

### Export pipeline
1. Authorize.
2. Load report/template and relationships.
3. Build DTO.
4. Render DOCX/PDF using exporter.
5. Store private file or stream download.
6. Record export event.
7. Return download.

### Storage
Use private disk for generated reports if persisted. Prefer temporary files or signed/authorized download routes. Do not expose predictable public paths.

### Testing
- Normalizer unit tests.
- Template parser/config tests.
- Report CRUD feature tests.
- Authorization tests.
- DOCX export smoke test.
- PDF export smoke test.
- Verify generated files exist, have expected MIME/size, and contain expected content where feasible.


## Wizard workflow architecture (updated)
- Wizard step key is stable and maps to five tab groups: general_information, guest_experience, departmental_activities, operational_updates, activities_work_plan.
- Persist draft after each step save; server remains source of truth.
- Livewire step component or parent wizard tracks current step, while each step uses reusable field/list components.
- Tab status is derived from persisted data and required-field validation; do not rely only on client-side state.
- Save current step transactionally.
- Final review loads all saved sections through ReportPreviewService/DTO.
- Finalize action authorizes, validates configured required fields, marks report completed, and queues/executes export generation.
- Export results are tracked separately (e.g. docx_generated_at, pdf_generated_at or export records) so one format may retry independently without losing report data.

## Automatic paste handling
- Implement list paste normalization in the frontend component (Livewire/JS interaction) for immediate UX, and keep a backend normalization service as authoritative safety/consistency layer.
- Frontend paste handler should normalize plain-text clipboard content and insert clean text at the caret when possible.
- If the handler cannot safely normalize, insert original clipboard text.
- Keep an optional undo buffer only in transient UI state; do not persist clipboard content unless necessary.
- Text/paragraph fields such as Guest Experience do not use list normalizer.
- Backend should normalize again when converting raw list input to stored items, preventing malformed list markers from reaching exports.

## Finalization and export reliability
- Report completion and export status must be separate concepts.
- Finalizing should save report data and completion status first.
- DOCX/PDF generation should be retryable.
- Export failures must not roll back user report content.
- Provide authorized download routes; no public predictable file URLs.
