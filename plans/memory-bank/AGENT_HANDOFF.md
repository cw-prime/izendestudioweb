# Agent Handoff Standard — Izende Studio Web

## Handoff Format
Append to `plans/memory-bank/HANDOFF_LOG.md` after every meaningful change:

```
- Date: [YYYY-MM-DD HH:MM:SS UTC]
- Agent: [agent name/mode]
- Scope worked: [description of work performed]
- Business KPI targeted: [what business value this delivers]
- Files changed: [list of files modified/created]
- Tests/lint/typecheck run: [status and results]
- Result: [outcome - success/partial/blocked]
- Risks introduced: [any new risks or concerns]
- Follow-up actions: [any required next steps]
- Updated memory files: [list of memory bank files updated]
- Re-entry packet (if paused/enhancement scope): [path to packet or N/A]
```

## Handoff Rules

### Append Only
- Never overwrite prior entries in HANDOFF_LOG.md
- Always append new entries at the end of the file

### Record Decisions
- Record decisions even if no code changed
- Document the rationale for any architectural or scope decisions
- Note any trade-offs made during implementation

### Test Status
- State explicitly if tests were not run and why
- For production changes, tests should always be run
- If tests are skipped due to environment constraints, document this clearly

### Link to Next Actions
- Link any unresolved risk to NEXT_ACTIONS.md
- Update status of related items in NEXT_ACTIONS.md
- Create new items in NEXT_ACTIONS.md for newly discovered issues

### Evidence Required
- For completed tasks: provide evidence of success (screenshots, logs, test results)
- For blocked tasks: provide clear reason for blockage and what's needed to unblock
- For partial tasks: document what was completed and what remains

### Mode-Specific Requirements

#### ARCHITECT Mode
- Must document design decisions and trade-offs
- Must link to re-entry packets for paused features
- Must update CURRENT_STATE.md if architecture changes

#### CODE Mode
- Must list all files changed with line references
- Must confirm tests pass (or explicitly state why not)
- Must update NEXT_ACTIONS.md status for completed items

#### DEBUG Mode
- Must document root cause identified
- Must provide minimal fix applied
- Must link to any related NEXT_ACTIONS.md items

#### REVIEW Mode
- Must list all findings with severity
- Must link findings to AUDIT_REGISTER.md
- Must not make fixes (report only)

#### ASK Mode
- Must document research findings
- Must provide clear recommendations
- Must link to relevant memory bank files

## Definition of Done

A task is considered complete when:
1. All acceptance criteria from NEXT_ACTIONS.md are met
2. Tests/lint/typecheck status is recorded and passing (or explicitly waived)
3. Relevant action status is updated in NEXT_ACTIONS.md
4. Handoff entry is appended to HANDOFF_LOG.md
5. Any new risks are documented in AUDIT_REGISTER.md
6. Memory bank files are updated to reflect current state

## Example Handoff Entry

```
- Date: 2026-03-03T23:00:00Z
- Agent: CODE
- Scope worked: Implemented CSP nonce generation and updated inline scripts to use nonces
- Business KPI targeted: Security hardening - reduce XSS risk
- Files changed:
  - config/security.php (lines 405-409, 450-455)
  - assets/includes/header-links.php (lines 15-20)
  - index.php (lines 50-56)
- Tests/lint/typecheck run: Not run - requires browser testing for CSP validation
- Result: Success - CSP now uses nonces, all inline scripts updated
- Risks introduced: None - backward compatible change
- Follow-up actions: Test in staging environment to verify no CSP violations
- Updated memory files: NEXT_ACTIONS.md (P0-001 status updated to in_progress)
- Re-entry packet: N/A
```
