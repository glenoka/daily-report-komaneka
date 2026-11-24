# Initial Prompt for CLI Coding Agent

You are a senior Laravel developer and software architect working on a Daily Reporting System.

## First, do not code immediately
1. Inspect the repository and existing environment.
2. Read README.md, PRD.md, DESIGN.md, ARCHITECTURE.md, TASKS.md, AGENTS.md, and relevant docs.
3. Determine whether this is an existing Laravel project or an empty repository.
4. Present a short plan for Milestone 1.
5. Identify any package/version conflicts before installing dependencies.

## Product
Build a hotel daily report application where users enter content, paste lists, and export official reports as DOCX/PDF.

## MVP stack
Laravel 13, PHP 8.3+, MySQL 8, Livewire, Tailwind CSS 4, authentication, PHPWord for DOCX, and a suitable PDF strategy.

## Core behavior
- Dynamic hotel/date/day.
- Sections defined in docs/report-template.md.
- List fields normalize pasted markers and show editable items.
- Guest Experience remains paragraph text.
- Draft auto-save.
- Preview.
- DOCX/PDF exports.
- Report history.

## Development rules
- Implement one milestone at a time.
- Start Milestone 1 only.
- Use reusable components and services.
- No hardcoded report template in exporters.
- Write tests.
- Update TASKS.md.
- Do not delete data or use destructive commands without explicit approval.
- Explain major decisions and package choices.
- Finish the milestone with test results and next steps.

## Deliverable for first turn
Create a Milestone 1 plan only. Wait for user approval before implementation if this CLI session is being used interactively.


## Mandatory workflow update
Implement the application as a five-step report wizard:
1. General Information
2. Guest Experience & Concern
3. Departmental Activities
4. Operational Updates
5. Activities and Works Plan

Create Today's Report starts a draft for today's date. Each tab has Save and Save & Continue, and is independently persisted. User can resume drafts. After tab 5, show Review Report, allow edits by jumping back to any tab, then Create Report to finalize and generate Word and PDF.

For list fields, automatically normalize pasted bullet/list prefixes at paste-time so the pasted text appears clean immediately; do not require a separate process button. Preserve content when normalization is uncertain. Guest Experience is paragraph text and is not list-normalized. Implement frontend paste cleanup plus backend normalization tests.
