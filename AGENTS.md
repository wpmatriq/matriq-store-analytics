# Sales Pulse — Codex project notes

> Revenue diagnosis engine for WooCommerce. Daily morning briefing that
> explains *why* revenue changed, in plain language, using deterministic
> math (no AI). Free forever — the data foundation for the AI-powered
> [Store Copilot](../store-copilot) Pro plugin.

**Identity:** "Commerce truth engine." Deterministic. Auditable. Trustworthy.
**Anti-identity:** Not a dashboard, not an AI chatbot, not a reporting tool, not real-time.

---

## Read first

- [STRATEGY.md](./STRATEGY.md) — full product plan, schema, algorithms, four-week sprint, future roadmap. Single source of truth for *what* and *why*.
- [../store-copilot/STORE-COPILOT-STRATEGY.md](../store-copilot/STORE-COPILOT-STRATEGY.md) — the Pro plugin's plan. Sections 4 and 8 explain how Pro extends this plugin and what hooks it relies on.

## Architecture in one screen

```
WooCommerce Analytics tables
      ↓ (nightly cron, 02:10 AM site-time)
DataCollector → SnapshotBuilder → wp_salespulse_daily_stats (1 row / day)
                                        ↓
                          DiagnosisEngine + ActionEngine (deterministic)
                                        ↓
                          ┌─────────────┴─────────────┐
                          ↓                            ↓
                  Dashboard (React)              Email digest
                  morning briefing               (WC_Email subclass)
```

Four DB tables: `daily_stats`, `dirty_dates`, `campaigns`, `system_state`. Order edits → mark date dirty (lightweight) → repaired in next nightly run. Backfill runs progressively reverse-chronological so the dashboard shows insight from second one.

## Where things live

| Concern | Path |
|---|---|
| Plugin bootstrap | [`sales-pulse.php`](sales-pulse.php), [`loader.php`](loader.php) |
| REST controllers | `core/controllers/` (Overview, History, Campaigns, Settings, DataReadiness, Digest) |
| Services | `core/services/` (DataCollector, SnapshotBuilder, DiagnosisEngine, ActionEngine, DigestMailer, DigestEmail) |
| DB models | `core/database/` (Base, DailyStats, DirtyDates, Campaigns, SystemState, Schema) |
| Cron | `core/cron/cron-manager.php` |
| Order hooks | `core/hooks/order-hooks.php` |
| Admin shell | `admin/menu.php`, `admin/notices.php`, `admin/api.php` |
| Reusable utils | `inc/{traits,utils,functions}/` — `Get_Instance` trait lives here |
| React app | `src/dashboard/` (App, DashboardApp, Pages, Components, hooks, api, Utils) |
| Email templates | `templates/email/digest-html.php`, `digest-text.php` |
| Design tokens | `src/dashboard/design-tokens.scss` (OKLch CSS variables, Figtree font) |

## Code conventions

- **PHP version**: 7.4+.
- **Namespace root**: `EC_Sales_Pulse\` (PascalCase, never uppercased).
- **Constants**: existing constants are mixed-case `EC_Sales_Pulse_VER`, `EC_Sales_Pulse_DIR`, etc. Don't introduce new mixed-case constants — prefer `EC_SALES_PULSE_*` (UPPER_SNAKE_CASE) for any new constant. Migrating existing ones is out of scope unless explicitly requested.
- **Class names**: PascalCase (`DigestMailer`, `SnapshotBuilder`).
- **Filenames**: lowercase-kebab-case so the autoloader maps `EC_Sales_Pulse\Foo\BarBaz` → `foo/bar-baz.php`.
- **Option keys**: legacy entries use `__wc_sma_*`; new options use `salespulse_*` (e.g. `salespulse_settings`).
- **DB table prefix**: `wp_salespulse_*`.
- **Hook prefix**: `salespulse_*` for action hooks (`salespulse_after_nightly_snapshot`, `salespulse_action_scenarios`); `EC_Sales_Pulse_*` for filters where existing code already uses that style. Pick the closest neighbour.
- **Text domain**: `sales-pulse`. All user-facing strings via `__()` / `esc_html__()` / `_e()`. Add `/* translators: ... */` for `sprintf`.
- **Permission check**: `manage_woocommerce` (use `BaseController::admin_permission_check`).
- **Database**: extend `EC_Sales_Pulse\Core\Database\Base` for new tables. Always `$wpdb->prepare()`. Schema migrations tracked via `db_version` in `wp_salespulse_system_state`.
- **REST**: namespace `sales-pulse/v2`. Extend `EC_Sales_Pulse\Core\Controllers\BaseController`. Use `$this->success()` / `$this->error()`.
- **Singletons**: `use EC_Sales_Pulse\Inc\Traits\Get_Instance;` — `static::$instance` late binding so Pro can subclass and swap.
- **React**: TanStack Query for server state, Radix-based primitives under `src/dashboard/Components/ui/`, design tokens via Tailwind `var(--*)`.
- **Voice**: "Calm Intelligence" (STRATEGY.md Section 18). Observation → cause → guidance. Informed, not impressed.
- **No em dashes** in user-facing strings or code comments. Regular hyphens or commas only. (Read as AI-generated; explicit user preference.)
- **No** comments that explain *what* code does. Only *why* — a hidden constraint, invariant, workaround. Identifiers should be self-documenting.

## Build / lint / test

```bash
# First-time setup
npm install
composer install

# Dev (watches src/, rebuilds on save)
npm run start

# Production build → assets/build/wc-sma-app.{js,css}
npm run build

# Linting
npm run lint-js
npm run lint-css
composer lint            # PHPCS (WordPress + WPCS)
composer phpstan         # static analysis
composer insights        # PHP Insights

# Fixers
npm run lint-js:fix
npm run lint-css:fix
composer format          # PHPCBF

# Combined
npm run scanner          # all linters
npm run fixer            # all fixers
```

PHP lint a single file before considering work done:

```bash
php -l path/to/file.php
```

> **Note**: `npm run build` may fail on Node 14 because `@wordpress/scripts` uses `require('node:path')` (Node 16+ syntax). Use `nvm use 18` if your local Node is older.

## Asset filename quirk

The webpack entry is `wc-sma-app` (legacy "WC Smart Analytics" naming). The plugin slug is `sales-pulse` everywhere else. When writing PHP that enqueues built assets, look up `wc-sma-app.js` / `wc-sma-app.css` under `assets/build/`. Don't rename — backwards-compat for any sites that have been pre-loading these.

## The Pro plugin (Store Copilot)

[`../store-copilot`](../store-copilot) is a separate Pro plugin that **requires** Sales Pulse. It extends rather than forks: subclasses `DiagnosisEngine` / `ActionEngine` (both expose `static::$instance` late binding), listens on `salespulse_after_nightly_snapshot`, filters `salespulse_action_scenarios`, registers REST routes under the same `sales-pulse/v2/copilot/*` namespace, adds an admin sub-tab.

When extension would be cleaner with a new hook, the path is: small PR adds the hook here, then build on it in store-copilot. See `STORE-COPILOT-STRATEGY.md` Section 8 for the planned filter additions (e.g. `salespulse_diagnosis_result`, `salespulse_overview_response`, a React `registerTab` slot system).

## Things NOT to do

- Don't paywall any feature in this plugin. STRATEGY.md Section 19: "Never paywall the core brain." Premium triggers belong in Store Copilot.
- Don't introduce AI calls here. This plugin is deterministic — that's the moat.
- Don't query WooCommerce analytics tables in real-time on page load. Always read from the daily snapshot.
- Don't run aggregation inside order hooks. Hooks only mark dates dirty (`INSERT IGNORE` into `dirty_dates`); rebuilding happens in nightly cron.
- Don't add new top-level admin menus. Stay under the `sales-pulse` parent slug.
- Don't introduce React state libraries that aren't already in `package.json`.
- Don't take risky actions (irreversible deletes, force pushes, sends to real customers) without explicit user confirmation.

## When the user asks for a feature

1. Re-read the relevant section of [STRATEGY.md](./STRATEGY.md). If the feature isn't covered, ask if it's still in scope or has shifted to Store Copilot.
2. Check if it's a free-plugin concern (data, deterministic logic, dashboard) or a Pro concern (AI, forecasts, automation). If Pro, redirect to `../store-copilot`.
3. Sketch the data flow: which table it reads, which hook it listens on, which REST route exposes it, which React component renders it.
4. Look for the closest existing pattern via `grep` and follow it.
5. Lint (PHP + JS) before reporting done.
