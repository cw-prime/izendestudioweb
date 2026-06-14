# Agent Workflow Template — Mark Barton / Izende Studio Web

Copy this file to a new project root as `CLAUDE.md`. Claude Code auto-reads it every
session. Project-specific state lives in `plans/memory-bank/` (see Bootstrap section).

---

## Communication Preferences
- No emojis. Concise responses. Markdown links for file references (`[file.ts](path/file.ts)`).
- No over-engineering. Only make changes directly requested or clearly necessary.
- No backwards-compat hacks, no docstrings/comments on unchanged code.
- Ask before risky or irreversible actions (destructive ops, pushes, deletes).
- Always update memory bank after any meaningful change.

---

## Session Startup (Every Session)
When asked "what should we do today?" or equivalent:
1. Read `plans/memory-bank/README.md` → understand file map
2. Read `plans/memory-bank/CURRENT_STATE.md` → current truth
3. Read `plans/memory-bank/NEXT_ACTIONS.md` → priority queue
4. Read `plans/memory-bank/HANDOFF_LOG.md` → last agent's output
5. Return: top 3 priorities, KPI impact, assigned mode, validation steps, definition of done

If memory bank doesn't exist in a new project → bootstrap it (see Bootstrap section below).

If runtime code contradicts docs → code wins. Queue a memory-bank update immediately.

---

## Model Routing
| Mode | When to Use |
|---|---|
| `ARCHITECT` | API contracts, schema design, migration planning, rollout/rollback strategy |
| `CODE` | Feature implementation, tests, refactors within approved scope |
| `DEBUG` | Reproduce failures, isolate root cause, minimal corrective fix |
| `REVIEW` | Risk-first: security, regressions, missing tests, metric integrity |
| `ASK` | Research, unknowns, option analysis, documentation checks |

---

## Priority System
- **P0** — Blocking. Fix before anything else.
- **P1** — High. This sprint / today.
- **P2** — Standard. Next available slot.
- **P3** — Later. Track but don't act yet.

Pick work in order: P0 → open Critical/High audit findings → P1 → everything else.
Reject low-value work that doesn't move KPI or reduce meaningful risk.

---

## Paused Feature Gate
- Paused/enhancement scope requires a re-entry packet before CODE mode.
- Packet lives at `plans/memory-bank/reentry/<feature-slug>-YYYY-MM-DD.md`.
- Only ARCHITECT mode allowed until packet status is `approved`.

---

## Handoff Standard
Append to `plans/memory-bank/HANDOFF_LOG.md` after every meaningful change:

```
- Date:
- Agent:
- Scope worked:
- Business KPI targeted:
- Files changed:
- Tests/lint/typecheck run:
- Result:
- Risks introduced:
- Follow-up actions:
- Updated memory files:
- Re-entry packet (if paused/enhancement scope):
```

Rules:
- Append only — never overwrite prior entries.
- Record decisions even if no code changed.
- State explicitly if tests were not run and why.
- Link any unresolved risk to NEXT_ACTIONS.md.

---

## Branch Policy
- Feature work → `staging`
- Production hardening → `release/<date>-<tag>` branched from `main`
- Only cherry-picked, reviewed changes from `staging` allowed into `release/*`
- Only `release/*` merges into `main` for production deploy
- Trifecta push order: `staging` → `development` → `main`

---

## Pre-Merge Checklist (All PRs to main/staging)
- [ ] `npm run build` passes
- [ ] TypeScript check passes (`npx tsc --noEmit`)
- [ ] Lint clean (`npm run lint`)
- [ ] Critical E2E smoke passes (auth, core user journey, generate/submit)
- [ ] No fake-success API paths for user-visible actions
- [ ] Feature flags at safe defaults
- [ ] `.env` changes documented
- [ ] Rollback plan noted
- [ ] `HANDOFF_LOG.md` updated with evidence

## Merge Approval Gate
1. ARCHITECT signs off on scope, dependencies, rollback
2. REVIEW signs off on regressions/security/test coverage
3. Owner final approval before merge to `main`

## Post-Deploy
- [ ] Health check passes
- [ ] Core user journey verified in production
- [ ] Error monitoring watched for 15–30 min
- [ ] If failure: rollback within 10 minutes, log incident

---

## Code Review Triggers
- All PRs targeting `main`, `staging`, or `development`
- Nightly diff reviews of integration branches before new feature work
- Go/No-Go: any Critical or High finding blocks merge without documented waiver
- Waivers require written justification + risk assessment + ARCHITECT approval

---

## Failover Rule
If a model fails (429/402/unavailable), switch to backup for same mode. Keep same
acceptance criteria and validation bar. Never lower the bar due to tooling issues.

---

## Memory Bank Bootstrap (New or Vibe-Coded Projects)

**Trigger phrase:** *"Bootstrap the memory bank for this project"* or
*"Audit this codebase and get it under control"*

**Steps:**
1. Read the entire codebase first — understand what's actually built, not what was planned
2. Create `plans/memory-bank/` directory
3. Create each file below using the skeleton, filled with real project content
4. Populate NEXT_ACTIONS.md P0 section with the top issues found during audit

---

### Skeleton: `plans/memory-bank/README.md`
```markdown
# Memory Bank — [Project Name]
Single source of truth for project state, priorities, and agent handoffs.

## Read Order (Every Session)
1. `plans/memory-bank/CURRENT_STATE.md`
2. `plans/memory-bank/NEXT_ACTIONS.md`
3. `plans/memory-bank/AUDIT_REGISTER.md`
4. `plans/memory-bank/ORCHESTRATION.md`
5. `plans/memory-bank/AGENT_HANDOFF.md`
6. `plans/memory-bank/HANDOFF_LOG.md`

## File Purpose
- `CURRENT_STATE.md`: What is true now (product, tech, constraints, risks).
- `NEXT_ACTIONS.md`: Prioritized execution queue with acceptance criteria.
- `AUDIT_REGISTER.md`: Findings from codebase audit with severity and status.
- `ORCHESTRATION.md`: Agent roles, mode routing, constraints, and invocation.
- `AGENT_HANDOFF.md`: Required output format after any meaningful change.
- `HANDOFF_LOG.md`: Append-only execution log from all agents.

## Ground Rules
- If code contradicts docs, code wins. Update CURRENT_STATE.md immediately.
- Paused feature work requires re-entry packet before CODE mode.
- Every merged change must update at least one memory bank file.
```

---

### Skeleton: `plans/memory-bank/CURRENT_STATE.md`
```markdown
# Current State — [Project Name]
Last updated: [DATE]

## Product
- What it does:
- Who it's for:
- Live at:
- Status: [prototype / MVP / production]

## Tech Stack
- Frontend:
- Backend:
- Database:
- Auth:
- Payments:
- Hosting:
- Key APIs:

## Key Files
- Entry point:
- API routes:
- Data models / schema:
- Auth logic:
- Config / env:

## Environment
- Dev branch:
- Staging branch:
- Production branch:
- Env files:

## Known Constraints
-

## Known Risks
-
```

---

### Skeleton: `plans/memory-bank/NEXT_ACTIONS.md`
```markdown
# Next Actions (Execution Queue)
Last updated: [DATE]

## P0 — Blockers (fix before anything else)

## P1 — High Priority (this sprint / today)

## P2 — Standard (next available slot)

## Later (Scale / Nice to Have)

## Status Legend
- `todo` not started
- `in_progress` actively being worked
- `blocked` waiting on decision/dependency
- `done` completed and verified
```

---

### Skeleton: `plans/memory-bank/AUDIT_REGISTER.md`
```markdown
# Audit Register — [Project Name]
Date: [DATE]

## Severity Legend
- `Critical` — data loss, security breach, auth bypass, revenue impact
- `High` — broken core flow, exposed data, no error handling on critical path
- `Medium` — degraded experience, workaround exists, tech debt
- `Low` — polish, minor inconsistency, future consideration

## Open Findings
| # | Severity | File | Finding | Status |
|---|---|---|---|---|

## Resolved Findings
| # | Severity | File | Finding | Resolved |
|---|---|---|---|---|
```

---

### Skeleton: `plans/memory-bank/ORCHESTRATION.md`
```markdown
# Agent Orchestration — [Project Name]

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
```

---

### Skeleton: `plans/memory-bank/HANDOFF_LOG.md`
```markdown
# Handoff Log — [Project Name]
Append-only. Do not edit prior entries.

---
```

---

## Definition of Done (Any Task)
- Code implemented and validated (or explicitly blocked with reason)
- Tests/lint/typecheck status recorded
- Relevant action updated in `NEXT_ACTIONS.md`
- Handoff appended to `HANDOFF_LOG.md`
