# Release Notes v1

## Release scope
This release packages the full Makasouk web stack for launch readiness:
- Laravel backend with PostgreSQL + PostGIS
- Filament admin panel (`/admin-panel`)
- Public website routes (`/`, `/how-it-works`, `/for-customers`, `/for-tailors`, `/faq`, `/contact`)
- Vue SPA (`/app/*`) for customer and tailor workflows
- Reverb realtime contract integration
- CI quality gates + Playwright E2E baseline

## Included readiness tracks
- Phase 8: deterministic QA fixtures, Playwright E2E, CI pipeline, release runbooks
- Phase 9: staging validation framework, UAT matrix, cutover/signoff docs
- Phase 10: production execution alignment, hypercare and incident playbooks

## Operational prerequisites
- One green hosted CI run on release commit (`quality-gates` + `e2e`)
- Completed staging signoff (`docs/release/staging-signoff.md`)
- No open `BLOCKER` issues

## Known non-blocking follow-ups
- Expand cross-browser E2E beyond Chromium
- Continue Arabic translation coverage improvements for long-form copy
- Add deeper production dashboards/alerts if needed

## References
- `docs/release/launch-checklist.md`
- `docs/release/deployment-notes.md`
- `docs/release/manual-qa-matrix.md`
- `docs/release/staging-signoff.md`
- `docs/release/production-cutover-plan.md`
- `docs/release/hypercare-plan.md`
- `docs/release/incident-response-playbook.md`
