# Store Analytics by Matriq #
**Contributors:** [wpmatriq](https://profiles.wordpress.org/wpmatriq/)  
**Tags:** woocommerce, sales analytics, revenue reports, daily digest, store insights  
**Tested up to:** 7.0  
**WC requires at least:** 7.0  
**WC tested up to:** 10.8.0  
**Stable tag:** 1.0.0  
**Requires at least:** 6.7  
**Requires PHP:** 7.4  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

Daily revenue diagnosis for WooCommerce. Explains why your store revenue changed, in plain language, with no AI guessing.

## Description ##

Matriq Store Analytics is a deterministic revenue diagnosis engine for WooCommerce. Every morning, it produces a one-page briefing that explains what changed in your store and why, using auditable math rather than AI.

Instead of another dashboard full of charts, you get one answer to the question merchants actually ask: "Did revenue go up or down yesterday, and what drove the change?"

### How it works ###

Once a night, the plugin builds a single snapshot row for the previous day from WooCommerce's analytics tables. It then decomposes the change in revenue across three drivers:

* Orders, did fewer or more customers buy?
* Items per order, did basket size shift?
* Average item price, did discount mix or product mix change?

The dominant driver is flagged as the primary cause with a confidence score, and a short suggested action is attached.

### What you get ###

* A morning Overview page in WP Admin with the previous day's revenue, the comparison versus the prior day and prior week, and the primary cause of the change.
* An optional daily email digest delivered to your inbox.
* A History page listing every past daily briefing, so you can audit any number it ever showed you.
* A Campaigns page to record sales and launches, so the engine knows when a drop is expected (a planned sale ending) versus a real signal.
* A Settings page covering timezone, revenue basis, and snapshot time.

### What it does not do ###

* No real-time charts on page load. The dashboard reads from a pre-built daily snapshot.
* No AI calls, no external services, no third-party tracking.
* No paywalled features. The diagnostic engine is fully free.

### Requirements ###

* WooCommerce 7.0 or later, with WooCommerce Analytics enabled.
* At least one completed order so a baseline can be built.

## Premium Features ##

Unlock AI-powered growth features with **Store Copilot**, the optional premium companion to Store Analytics by Matriq. The free plugin remains the deterministic truth engine. Copilot adds an intelligence layer on top of your store's reliable daily data, turning diagnosis into action.

* **Conversational Analyst:** Ask questions about your store in plain English, like "why was Wednesday weird?", and get instant, grounded answers with supporting numbers and charts.
* **30-day Revenue Forecasting:** Confidence-banded revenue projections built on your store's unique trends, seasonality, and campaign history.
* **Anomaly Detection:** Get notified the moment something unusual happens, with an explanation of what changed and why it matters, instead of waiting for tomorrow's digest.
* **Growth Playbooks:** Per-product trends and consent-gated playbooks that turn daily diagnoses into specific, ready-to-run next actions.
* **Smart Scheduler:** Deliver each playbook to the right customers, at the right time, with the right discount, to maximize recovered revenue.
* **Multi-channel Alerts:** Beyond the daily digest. Slack, WhatsApp, and webhook notifications for the moments that need attention now.
* **Product-level Insights:** Top-mover surfacing and per-product diagnosis to finally answer "which product caused this change?"
* **Customer Cohort Intelligence:** Track new versus returning behaviour, churn risk on past cohorts, and win-back opportunities with measurable goals.
* **Automated Outreach (with consent):** Approve once, and Copilot executes segmenting, coupon creation, and follow-up scheduling for you.

Store Analytics by Matriq stays free forever. Store Copilot is a separate annual license for stores ready to move from understanding to action.

We have just launched so don't miss the early bird access. [Upgrade to Pro 🚀](https://matriq.in/)

## Installation ##

1. Install and activate WooCommerce, and visit WooCommerce > Analytics at least once so the analytics tables are populated.
2. Upload the plugin to /wp-content/plugins/matriq-store-analytics or install via Plugins > Add New.
3. Activate the plugin through the Plugins screen.
4. Open Matriq Store Analytics in the WP Admin sidebar and follow the onboarding to pick your timezone, revenue basis, and snapshot time.
5. The first overnight snapshot runs at the configured snapshot time. Until then, a backfill builds the last 30 days progressively so the dashboard is populated within a few minutes.

## Frequently Asked Questions ##

### Does this plugin use AI? ###

No. Every number and every cause attribution comes from deterministic math against your WooCommerce orders. You can audit how any figure was derived.

### Does it slow down my store? ###

No. All aggregation runs in a nightly cron job. Page loads in WP Admin only read pre-built snapshot rows.

### What happens if I refund or edit an old order? ###

The plugin records that the affected day is "dirty" and rebuilds only that day's snapshot in the next nightly run. The rest of the history is untouched.

### Is my data sent anywhere? ###

No. All analysis happens on your own site against your own database. Nothing leaves your server.

## Changelog ##
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
