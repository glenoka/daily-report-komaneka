# AI Coding Agent Rules

## Before coding
- Read `README.md`, `PRD.md`, `DESIGN.md`, `ARCHITECTURE.md`, `TASKS.md`, and relevant docs.
- Inspect the current repository before changing anything.
- Identify existing framework/version and do not overwrite an existing project.
- If repository contains an existing application, adapt rather than replace.

## Workflow
- Work on one milestone at a time.
- For the current milestone, identify task scope and acceptance criteria.
- Implement, run tests, inspect errors, fix, and update task checkboxes.
- Do not proceed to the next milestone without explicit user instruction or clear milestone completion in the task workflow.
- Keep changes small and reviewable.

## Code quality
- Keep controllers/Livewire components thin.
- Use services/actions for business logic.
- Use explicit validation.
- Use policies for access control.
- Use transactions where appropriate.
- Prefer stable, readable code over clever abstractions.
- Do not hardcode template structure in export classes.
- Avoid unnecessary packages.
- Do not introduce breaking changes without explanation.

## Safety
- Never delete production data.
- Never run destructive migrations or commands without approval.
- Never expose private report files publicly.
- Never commit secrets.
- Do not disable security protections to make tests pass.

## Testing
- Add tests for all business logic.
- Normalizer must have unit tests for common markers and edge cases.
- Run relevant tests after changes.
- Report commands run and results honestly.

## Documentation
- Update `TASKS.md` when tasks change or are completed.
- Document decisions and configuration.
- Include setup/run instructions.
- Keep README accurate.

## Current instruction
Start with Milestone 1 only. First inspect the environment and repository, then present a concise plan. If the repository is empty/new, initialize the Laravel project. Do not implement Milestone 2 until Milestone 1 is completed and tested.


## Required UX workflow (latest)
The app must use a five-tab wizard, not one long form:
1. General Information
2. Guest Experience & Concern
3. Departmental Activities
4. Operational Updates
5. Activities and Works Plan

User starts with **Create Today's Report**. Each tab is independently saveable. Save & Continue saves then advances. Drafts must be resumable. After the five steps, provide **Review Report**. Review must allow returning to any tab. **Create Report** finalizes and produces both DOCX and PDF.

## Paste UX requirement
For list textareas, clean recognized bullet/number/checklist prefixes during the paste event so the pasted text appears clean immediately. Do not require a separate process button. Use a safe normalizer: uncertain markers should be preserved rather than destructively removed. Guest Experience paragraph field must not be list-normalized. Implement frontend normalization for immediate feedback and backend normalization as a safety layer. Test paste cases and content preservation.
