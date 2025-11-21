# AtoM Database Snapshots (mysqldump) — Plan and Status

## Goals
- Allow admins to take on‑demand database snapshots before risky operations (e.g., CSV import).
- Provide a practical rollback path using `mysqldump`/`mysql` without invasive schema changes.
- Ship incrementally: safe CLI first, then UI orchestration.

## Scope (current effort)
- Database snapshots only. Digital object files are not included. Document file backup separately.
- Use `mysqldump --single-transaction` (InnoDB) to minimize locks; support gzip.

## User Experience (target)
- CLI: `php symfony tools:db-snapshot` saves a timestamped dump with optional label.
- Admin UI: Snapshots page to list/download/delete/create snapshots.
- CSV Import: checkbox “Create snapshot before import”.
- Restore: CLI (preferred) or gated UI job; warns and requires maintenance mode.

## Phased Plan
1) CLI snapshot (MVP)
   - Task `tools:db-snapshot` creates gzipped dumps with metadata in filename.
   - Config via `app.yml` (paths, gzip, mysqldump path).

2) Snapshot registry (metadata)
   - `db_snapshot` table (filename, size, checksum, label, created_at, created_by, app/db version, status).
   - Registry service computes checksum and stores record.

3) Retention + housekeeping
   - `tools:db-snapshot-clean` with count/age retention; dry‑run support.

4) Background job
   - `arDbSnapshotJob` to run snapshot as a queued job; surface logs in Jobs UI.

5) Admin UI
   - “Snapshots” page: list, download, delete, create (enqueue job).

6) CSV Import integration
   - Checkbox “Create snapshot before import”; enqueues job with label.

7) CLI restore (rollback)
   - `tools:db-restore` with maintenance mode and safety confirmations.
   - Supports gzip; clears caches; optional reindex.

8) UI restore (optional, gated)
   - Super‑admin only; feature flag; double confirmation; runs `arDbRestoreJob`.

9) Polish and docs
   - Better error messaging, disk space checks, redacted logs, full documentation.

## Current Status
- Implemented (Phase 1): `tools:db-snapshot`
  - File: `lib/task/tools/dbSnapshotTask.class.php`
  - Options: `--label`, `--output-dir`, `--mysqldump-path`, `--no-gzip`, `--dry-run`.
  - Reads DB config from Propel; uses a secure temp `defaults-extra-file` for credentials.

## Next Actions
- Phase 2: Add `db_snapshot` registry and record checksum/size/label/user.
- Phase 3: Retention task to prune old snapshots.
- Phase 4–5: Job + Admin UI to run and view snapshots.

## Decisions Log
- Use mysqldump snapshots for simple “pre‑import” rollbacks (approved).
- Store credentials in temp `defaults-extra-file` (avoid passwords in process list) (approved).
- Compress with gzip by default (approved; override via `--no-gzip`).
- CLI first; UI later behind admin permissions (approved).
- Restore will be implemented via CLI first; UI restore optional and gated (approved).

## Open Questions
- Where to store snapshots by default in production? (Default: `data/db-snapshots/`.)
- Retention defaults (count or days)?
- Reindex policy after restore (auto vs manual)?
- Include basic environment metadata file alongside dump (JSON sidecar) before registry exists?

## Configuration (app.yml)
```yaml
all:
  app_db_snapshot:
    dir:           "%SF_DATA_DIR%/db-snapshots"
    mysqldump_path: mysqldump
    gzip:          true
```

## Commands (cheat sheet)
- Create snapshot:
  - `php symfony tools:db-snapshot --label="pre-import records.csv"`
- Dry run:
  - `php symfony tools:db-snapshot --dry-run`

## Risks & Mitigations
- Long‑running dump under load: mitigate with background jobs (Phase 4) and off‑peak scheduling.
- Storage growth: add retention (Phase 3); document cleanup.
- Restore is all‑or‑nothing: clearly warn; pair with import‑level “undo” in future work.

## Out of Scope / Future Work
- Import Batch Journal for targeted undo (record created IDs and pre‑update values; add rollback task).
- File‑store snapshots or staging area for digital objects.

