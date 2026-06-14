# Memory Bank — Izende Studio Web
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
