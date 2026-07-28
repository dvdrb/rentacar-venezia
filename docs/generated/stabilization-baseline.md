# Stabilization baseline

Captured 2026-07-28 before implementation.

- Repository worktree was clean.
- LocalWP theme and plugin destinations are symlinks to the owned repository sources.
- `pnpm install --frozen-lockfile`, build and static checks passed.
- The initial Playwright run skipped the local-route tests and failed the production-smoke target because `http://rentacar-venezia-local.local/` refused connections. No production URL was accessed.
- `wp` was not on PATH; LocalWP PHP 8.3 is available and is used for PHP lint/tests.
- The source implementation had a two-day-compatible duration validator, unkeyed estimate lines, mail-only reservation delivery, no private request storage, no price sort metadata, no controlled powertrain field, and an active custom cookie interface.
