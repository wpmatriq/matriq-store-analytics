# Revenue Diagnosis for WooCommerce – Daily Store Analytics by Matriq #
**Contributors:** [wpmatriq](https://profiles.wordpress.org/wpmatriq/)  
**Tags:** woocommerce, revenue, analytics, sales-analytics, store-insights  
**Requires at least:** 6.7  
**Tested up to:** 7.0  
**Requires PHP:** 7.4  
**Stable tag:** 1.0.2  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Find out why your WooCommerce revenue went up or down yesterday — every morning, in plain language, with no charts to dig through.

## Description ##

**Every morning, one clear answer: why did my revenue change?**

Most WooCommerce store owners check their revenue and think: *is this good? Is this bad? What do I do about it?* Dashboards and charts don't answer that. Matriq Store Analytics does.

Each night, the plugin analyses your previous day's orders and delivers a plain-language briefing: revenue went down 12% because fewer customers bought (not because they spent less per order). Or: revenue jumped because average item price rose — your weekend sale ended and full-price items came back.

No guessing. No AI hallucinations. Just auditable math against your own WooCommerce data, run entirely on your server.

**Who it's for:** WooCommerce store owners who want to understand their revenue without hiring an analyst or learning SQL.

### What you get each morning ###

* **Revenue Overview** – yesterday's revenue, compared to the prior day and prior week
* **Primary Cause** – the single biggest driver of the change (orders, basket size, or item price mix), with a confidence score
* **Suggested Action** – one short next step tied to the diagnosed cause
* **Daily Email Digest** – optional morning email so you know before you open your laptop
* **History Log** – every past briefing, fully auditable, so you can trace any number back to its source
* **Campaigns Tracker** – log your sales and launches so the engine knows a post-sale revenue dip is expected, not alarming

### How the diagnosis works ###

The engine breaks your revenue change into three components every night:

1. **Order volume** – did more or fewer customers buy?
2. **Items per order** – did basket size grow or shrink?
3. **Average item price** – did discount mix or product mix shift the per-item value?

The dominant driver is flagged as the primary cause. This is deterministic arithmetic — you can audit every figure on the History page.

### What it does NOT do ###

* No real-time charts that slow your admin panel
* No AI calls, no external services, no third-party data sharing
* No paywalled core features — the diagnosis engine is completely free

### Privacy & performance ###

All analysis runs in a nightly WP-Cron job. Your order data never leaves your server. Admin pages load from pre-built daily snapshots — no heavy queries on page load. Fully compatible with WooCommerce HPOS (High-Performance Order Storage).

### Requirements ###

* WooCommerce 7.0 or later, with WooCommerce Analytics enabled
* At least one completed order so a baseline can be established

### Premium: Store Copilot ###

The free plugin is your revenue truth engine — always free, no limits. **Store Copilot** is an optional premium add-on that turns diagnosis into action:

* **Conversational Analyst** – ask "why was Wednesday weird?" in plain English and get an answer with supporting numbers
* **30-day Revenue Forecasting** – confidence-banded projections built on your store's own trends and campaign history
* **Anomaly Detection** – get notified the moment something unusual happens, before tomorrow's digest
* **Product-level Insights** – find out which specific product caused the change
* **Growth Playbooks** – per-product recommendations that go from diagnosis to a ready-to-run next action
* **Multi-channel Alerts** – Slack, WhatsApp, and webhook notifications for the moments that need attention now
* **Customer Cohort Intelligence** – new vs returning behaviour, churn risk, and win-back opportunities
* **Smart Scheduler & Automated Outreach** – Copilot segments, creates coupons, and schedules follow-ups once you approve

[Early bird access → matriq.in](https://matriq.in/)

## Installation ##

1. Make sure WooCommerce is active and you have visited **WooCommerce → Analytics** at least once (this populates the analytics tables).
2. Install via **Plugins → Add New** and search for "Matriq Store Analytics", or upload the zip manually.
3. Activate the plugin.
4. Open **Matriq** in the WP Admin sidebar and complete the short onboarding (timezone, revenue basis, snapshot time).
5. The first nightly snapshot runs at your configured time. A 30-day backfill runs immediately so the History page is populated within a few minutes.

## Frequently Asked Questions ##

### Does this plugin use AI or send my data anywhere? ###
No. Every number is calculated with deterministic arithmetic against your own WooCommerce database. Nothing leaves your server.

### Will this slow down my store? ###
No. All aggregation happens in a nightly background job. WP Admin pages read from pre-built daily snapshots — no heavy queries on page load.

### What happens if I refund or edit an old order? ###
The plugin marks that day as "dirty" and rebuilds only that day's snapshot in the next nightly run. The rest of your history is untouched.

### Does it work with WooCommerce HPOS? ###
Yes. The plugin is fully compatible with WooCommerce High-Performance Order Storage.

### What if I have a slow day because of a planned sale ending — will it flag a false alarm? ###
That's exactly what the Campaigns page is for. Log your sales and launches, and the engine knows a post-sale dip is expected context, not a signal to act on.

### Is the diagnostic engine really free forever? ###
Yes. The free plugin — daily briefings, email digest, history, campaigns — has no feature limits and will remain free. Store Copilot is a separate premium add-on for stores ready to automate action on those insights.

## Changelog ##
### 1.0.2 ###
* Compatibility with future release.

### 1.0.1 ###
* Compatibility for Store Copilot premium add-on.

### 1.0.0 ###
* Initial public release.

### 0.0.2 ###
* Add: Public extension surface for premium add-ons (Phase 0 of Store Copilot integration).
  - Filter `matriq_msa_diagnosis_result` wraps `DiagnosisEngine::diagnose()` output for AI enrichment.
  - Filter `matriq_msa_action_recommendation` wraps `ActionEngine::recommend()` output for tailored next-step copy.
  - Filter `matriq_msa_overview_response` wraps the `/overview` REST payload so extensions can append fields.
  - Filter `matriq_msa_admin_submenus` lets extensions register additional admin sub-tabs.
  - Action `matriq_msa_data_collector_extra` fires after every per-day snapshot write so extensions can fan out per-product / per-customer collection sharing the same WC analytics read pass.
  - JavaScript slot `window.matriqMSA.registerTab({ id, label, component })` lets extensions plug a React page into the dashboard. Built-in tabs cannot be overridden.
* Plugin rebrand as Matriq Store Analytics to reflect the new public-facing name of the plugin.

### 0.0.1 ###
* Initial release.
