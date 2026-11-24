# Database Schema Draft

## hotels
- id
- name
- code (nullable)
- is_active
- timestamps

## users
- standard authentication fields
- role or role/permission relation
- hotel_id (nullable, depending on multi-property access)
- timestamps

## departments
- id
- hotel_id (nullable for global)
- name
- code
- sort_order
- is_active
- timestamps

## report_templates
- id
- hotel_id (nullable/global)
- name
- config JSON
- is_default
- is_active
- timestamps

## daily_reports
- id
- hotel_id
- report_template_id
- report_date
- created_by
- status: draft/completed (approved reserved for Phase 2)
- guest_experience (optional, if text field remains top-level)
- completed_at
- timestamps

## report_sections
- id
- daily_report_id
- section_key
- title
- field_type
- sort_order
- content nullable
- timestamps

## report_items
- id
- report_section_id
- content
- sort_order
- timestamps

## Constraints and indexes
- Index daily_reports (hotel_id, report_date).
- Index status, report_date.
- Consider unique hotel/date constraint only after duplicate policy is decided.
- Foreign keys should use deliberate cascade/restrict behavior.
