# Agent Orchestration — Izende Studio Web

## Mode Routing
When a task arrives, declare your mode before starting. Stay in that mode for the session.

| Mode | Invoke With | Allowed | Forbidden |
|---|---|---|---|
| `ARCHITECT` | "Think through the architecture for..." | Design, contracts, schema, migration plans, rollback strategy | Writing production code, skipping trade-off analysis |
| `CODE` | "Implement...", "Build...", "Fix..." | Feature code, tests, refactors within approved scope | Architecture decisions, scope creep beyond the task |
| `DEBUG` | "Debug this", "Why is X broken" | Reproduce failure, isolate root cause, minimal corrective fix | Refactoring while debugging, changing unrelated code |
| `REVIEW` | "Review...", "Audit..." | Find issues, assign severity, document findings | Making fixes (report only — fixes go through CODE mode) |
| `ASK` | "Research...", "What are the options for..." | Research, option analysis, documentation checks | Implementation, making changes |

**Declaration format:** Start every session with:
> "I am in [MODE] mode. I will [what I'll do]. I will not [what I won't do]."

## Priority Rules
- **P0** — Fix before anything else. Block all other work.
- **P1** — This session / today.
- **P2** — Next available slot.
- **P3** — Later. Track but don't act yet.

Work order: P0 → open Critical/High audit findings → P1 → everything else.

## Daily Startup
When asked "what should we do today?" or equivalent:
1. Read memory bank files in order (README → CURRENT_STATE → NEXT_ACTIONS → AUDIT_REGISTER)
2. Identify highest priority item
3. Declare mode
4. State top 3 tasks, KPI impact, validation steps, definition of done

## Paused Feature Gate
- Any paused or enhancement feature requires a re-entry packet before CODE mode
- Packet: `plans/memory-bank/reentry/<feature-slug>-YYYY-MM-DD.md`
- Only ARCHITECT mode allowed until packet status = `approved`

## Handoff Enforcement
No task is complete until:
- Validation evidence recorded
- NEXT_ACTIONS.md status updated
- Entry appended to HANDOFF_LOG.md

## Code Review Gate
- All PRs require REVIEW mode before merge
- Critical or High finding = NO-GO until resolved or waived with justification
- Waivers need written risk assessment + ARCHITECT sign-off

## Failover
If model unavailable (429/402), switch to backup. Keep same acceptance criteria.
