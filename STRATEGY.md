# Sales Pulse v2 - Revenue Diagnosis Engine

> Complete Strategy & Implementation Plan
> Last Updated: February 2026

---

## Table of Contents

1. [Product Vision & Identity](#1-product-vision--identity)
2. [v1 Scope - Clear Boundaries](#2-v1-scope--clear-boundaries)
3. [Two-Product Architecture](#3-two-product-architecture)
4. [System Architecture & Data Flow](#4-system-architecture--data-flow)
5. [Database Schema](#5-database-schema)
6. [Revenue Diagnosis Algorithm](#6-revenue-diagnosis-algorithm)
7. [Action Recommendation Engine](#7-action-recommendation-engine)
8. [WordPress Hooks & Events Map](#8-wordpress-hooks--events-map)
9. [Snapshot System](#9-snapshot-system)
10. [Backfill Strategy - First Install Experience](#10-backfill-strategy--first-install-experience)
11. [Campaign Context System](#11-campaign-context-system)
12. [UI Structure - Morning Briefing](#12-ui-structure--morning-briefing)
13. [Copy & Language System](#13-copy--language-system)
14. [Email Digest](#14-email-digest)
15. [Navigation & Menu Structure](#15-navigation--menu-structure)
16. [Settings - Minimal by Design](#16-settings--minimal-by-design)
17. [Safety, Edge Cases & Fail-Safes](#17-safety-edge-cases--fail-safes)
18. [Brand & Tone - Calm Intelligence](#18-brand--tone--calm-intelligence)
19. [Monetization Strategy](#19-monetization-strategy)
20. [Current Codebase Impact](#20-current-codebase-impact)
21. [Four-Week Sprint Plan](#21-four-week-sprint-plan)
22. [Future Roadmap - Store Copilot](#22-future-roadmap--store-copilot)
23. [SQL Query Reference](#23-sql-query-reference)
24. [Testing Strategy](#24-testing-strategy)

---

## 1. Product Vision & Identity

### The Fundamental Pivot

| Aspect | Old (v1) | New (v2) |
|--------|----------|----------|
| **Identity** | Better WooCommerce Analytics Dashboard | Revenue Diagnosis Engine |
| **Core Job** | Show metrics and charts | Explain WHY revenue changed |
| **User Action** | Interpret data themselves | Receive clear explanation |
| **Value** | Feature richness | Outcome clarity |
| **Feeling** | "More reports to read" | "I understand my store" |

### Core Promise

> **"Stop guessing why revenue changed."**

Subline: Sales Pulse analyzes your WooCommerce store daily and explains what changed - and why - in plain language.

### What Sales Pulse Is

- A **Commerce Intelligence Data Layer** inside WooCommerce
- A **deterministic revenue reasoning system** (no AI, no guessing)
- A **daily store health diagnostic** that merchants check every morning
- The **data foundation** for future AI-powered Store Copilot

### What Sales Pulse Is NOT

- Not a dashboard with more charts
- Not an AI chatbot
- Not a reporting tool
- Not a real-time monitoring system
- Not a configuration-heavy analytics suite

### Why This Direction Is Defensible

AI agents can clone:
- UI and dashboards
- Chart libraries
- KPI calculators

AI agents CANNOT easily clone:
- Historical data pipelines with store memory
- Normalized commerce metrics with WooCommerce-native depth
- Subscription lifecycle tracking
- Cohort computation correctness
- Deterministic causal reasoning embedded in WordPress ecosystem

---

## 2. v1 Scope - Clear Boundaries

### What v1 DOES (Store-Level Revenue Diagnosis Only)

- Nightly snapshot system (one row per day of store health)
- Daily comparison (yesterday vs day before)
- Weekly comparison (last 7 days vs previous 7 days)
- Revenue decomposition: `Revenue = Orders x Items/Order x Avg Item Price`
- Primary cause detection with confidence scoring
- Deterministic action suggestions (rule-based, no AI)
- Manual campaign context (suppress false alarms during sales)
- Optional email digest (daily morning briefing)
- Minimal settings (timezone, revenue basis, snapshot time)
- History page (daily explanation list for trust building)

### What v1 Does NOT Do

- Product-level drilldowns or analysis
- Category-level analysis
- Customer cohort analysis
- Real-time updates or live counters
- Forecasting or predictions
- AI chat or conversational interface
- Segmentation or advanced filtering
- CSV exports
- Custom metric builders
- Multi-store support

These belong to **Store Copilot** (premium) or later phases.

---

## 3. Two-Product Architecture

```
Layer 1: Sales Pulse (Free Forever)
─────────────────────────────────────
Role: "Commerce Truth Engine"
- Deterministic analytics infrastructure
- Collects + pre-computes WooCommerce data
- Stores daily store health snapshots
- Provides revenue diagnosis
- Provides REST APIs for data access
- NO AI, pure math and rules

        ↓ (data foundation)

Layer 2: Store Copilot (Premium, Future)
─────────────────────────────────────
Role: "Store Brain"
- AI-powered reasoning layer
- Insights, predictions, explanations
- Anomaly detection
- Automated actions (coupons, emails, tagging)
- Conversational interface
- Requires Sales Pulse as prerequisite
```

### Why This Separation Is Strategic

**Problem 1 - Data correctness** (boring but defensible)
LLMs are terrible at raw commerce accounting logic. The company that owns the cleanest commerce dataset wins the AI layer. Sales Pulse becomes the moat.

**Problem 2 - Intelligence** (fast evolving)
AI features change every 6 months. If AI is baked into Sales Pulse, you rewrite constantly. With separation, the data platform stays stable while unlimited AI products can be built on top.

### Positioning

| Product | Position | Promise | Goal |
|---------|----------|---------|------|
| Sales Pulse | Reliable numbers | "Finally trust your WooCommerce metrics" | Replace spreadsheets |
| Store Copilot | Business advisor | "Know what to do today to grow revenue" | Replace thinking |

---

## 4. System Architecture & Data Flow

```
WooCommerce Analytics Tables
(wc_order_stats, wc_order_product_lookup, wc_customer_lookup)
                    |
                    | Nightly Cron (02:10 AM)
                    v
    ┌───────────────────────────────┐
    │   SalesPulse_Data_Collector   │
    │   (reads WC analytics tables) │
    └───────────────┬───────────────┘
                    |
                    v
    ┌───────────────────────────────┐
    │  SalesPulse_Snapshot_Builder  │
    │  (aggregates day → snapshot)  │
    └───────────────┬───────────────┘
                    |
                    v
    ┌───────────────────────────────┐
    │  wp_salespulse_daily_stats    │
    │  (one row per day)            │
    └───────────────┬───────────────┘
                    |
        ┌───────────┴───────────┐
        |                       |
        v                       v
┌──────────────┐    ┌──────────────────┐
│  Diagnosis   │    │  Action Engine   │
│  Engine      │    │  (rule-based     │
│  (math +     │    │   recommendations│
│   confidence)│    │   + campaign     │
│              │    │   awareness)     │
└──────┬───────┘    └────────┬─────────┘
       |                     |
       └──────────┬──────────┘
                  |
        ┌─────────┴─────────┐
        |                   |
        v                   v
┌──────────────┐    ┌──────────────┐
│  Dashboard   │    │  Email       │
│  UI (React)  │    │  Digest      │
│  "Morning    │    │  (optional)  │
│   Briefing"  │    │              │
└──────────────┘    └──────────────┘
```

### Key Architecture Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Data source | WC Analytics tables only | HPOS compatible, status-aware, refund-aware, indexed, already cleaned |
| Processing | Nightly cron only | Reliable morning briefing > noisy live dashboard |
| Storage | Pre-computed snapshots | Dashboard loads <300ms, even on 100K+ order stores |
| Diagnosis | Deterministic math | Trustworthy, explainable, no AI hallucination |
| Campaign context | Manual | Avoids fake AI guesses (auto-detection is wrong 40-60% of time) |
| Settings | Minimal | Opinionated product, not configuration engine |
| Monetization | Free forever | Lead magnet - build habit, own dataset, convert to premium |

---

## 5. Database Schema

**4 tables only** - small, focused, scalable.

### Table 1: `wp_salespulse_daily_stats` (Core Brain)

One row = one day of store health. Dashboard reads this only.

```sql
CREATE TABLE wp_salespulse_daily_stats (
    stat_date DATE NOT NULL,

    revenue DECIMAL(14,2) NOT NULL,
    orders INT UNSIGNED NOT NULL,
    items_sold INT UNSIGNED NOT NULL,

    avg_order_value DECIMAL(14,2) NOT NULL,
    items_per_order DECIMAL(10,2) NOT NULL,
    avg_item_price DECIMAL(14,2) NOT NULL,

    new_customers INT UNSIGNED NOT NULL,
    returning_customers INT UNSIGNED NOT NULL,

    discount_total DECIMAL(14,2) NOT NULL,
    refund_total DECIMAL(14,2) NOT NULL,

    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,

    PRIMARY KEY (stat_date),
    KEY revenue_idx (revenue),
    KEY orders_idx (orders)
) ENGINE=InnoDB;
```

**Performance**: Even after 5 years = ~1,825 rows. Constant-time queries forever.

### Table 2: `wp_salespulse_dirty_dates` (Repair Mechanism)

When an old order is edited/refunded, we only rebuild that specific day.

```sql
CREATE TABLE wp_salespulse_dirty_dates (
    stat_date DATE NOT NULL,
    reason VARCHAR(50),
    detected_at DATETIME NOT NULL,

    PRIMARY KEY (stat_date)
) ENGINE=InnoDB;
```

### Table 3: `wp_salespulse_campaigns` (Context Layer)

Manual merchant input - affects interpretation only, not data.

```sql
CREATE TABLE wp_salespulse_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,
    goal ENUM('orders','aov','clearance','launch') NOT NULL,

    start_date DATE NOT NULL,
    end_date DATE NULL,

    created_at DATETIME NOT NULL
) ENGINE=InnoDB;
```

### Table 4: `wp_salespulse_system_state` (Internal Memory)

Tracks onboarding, backfill progress, plugin internals. Avoids polluting `wp_options`.

```sql
CREATE TABLE wp_salespulse_system_state (
    state_key VARCHAR(100) PRIMARY KEY,
    state_value TEXT NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB;
```

**Example entries:**

| key | value |
|-----|-------|
| `last_snapshot_date` | `2026-02-14` |
| `backfill_start` | `2024-01-01` |
| `backfill_complete` | `yes` |
| `plugin_version` | `1.0.0` |
| `db_version` | `1` |

---

## 6. Revenue Diagnosis Algorithm

### Core Principle

Revenue is not magic. It decomposes mathematically:

```
Revenue = Orders x AOV
AOV = Items per Order x Avg Item Price

Therefore:
Revenue = Orders x Items per Order x Avg Item Price
```

When revenue changes, one (or more) of these changed. We don't guess. We calculate.

### Variables

```
R1 = revenue current period       R0 = revenue previous period
O1 = orders current                O0 = orders previous
I1 = items per order current       I0 = items per order previous
P1 = avg item price current        P0 = avg item price previous
AOV1 = avg order value current     AOV0 = avg order value previous
```

### Step 1: Detect Revenue Change

```
revenue_change_pct = ((R1 - R0) / R0) * 100

If abs(revenue_change_pct) < 5%:
    → "Revenue stable" (no diagnosis needed)
```

### Step 2: Level 1 Decomposition (Orders vs AOV)

Using midpoint decomposition for fairness (no bias, no double counting):

```
Order Impact:  ΔR_orders = (O1 - O0) x ((AOV1 + AOV0) / 2)
AOV Impact:    ΔR_aov    = (AOV1 - AOV0) x ((O1 + O0) / 2)

Verification: ΔR ≈ ΔR_orders + ΔR_aov
```

If `abs(ΔR_orders) > abs(ΔR_aov)` → primary driver is **orders**
Else → primary driver is **AOV**

### Step 3: Level 2 Deep Dive

**If Orders caused change (Level 2A):**

Check customer mix:
```
new_change = pct_change(new_customers_current, new_customers_previous)
ret_change = pct_change(returning_customers_current, returning_customers_previous)
```
Whichever changed more = sub-cause

**If AOV caused change (Level 2B):**

Check item behavior:
```
Item Count Impact: ΔR_items = (I1 - I0) x ((P1 + P0) / 2) x ((O1 + O0) / 2)
Price Impact:      ΔR_price = (P1 - P0) x ((I1 + I0) / 2) x ((O1 + O0) / 2)
```
Whichever contributed more = sub-cause

### Step 4: Confidence Scoring

```
confidence = abs(highest_impact) / abs(total_revenue_delta)

> 60%  → High    → "Clear cause identified"
40-60% → Medium  → "Likely cause detected"
< 40%  → Low     → "No strong single cause"
```

### Step 5: Compose Explanation

Output structure:
```php
[
    'revenue_change_percent' => -18.2,
    'revenue_change_amount'  => -5700,
    'direction'              => 'decline',
    'primary_factor'         => 'orders',
    'primary_reason'         => 'Returning customers dropped 18%',
    'impact_breakdown'       => [
        'orders' => -12500,
        'items'  => -4100,
        'price'  => +900
    ],
    'confidence'             => 0.72,
    'confidence_label'       => 'Clear cause identified'
]
```

### Edge Cases

| Case | Handling |
|------|----------|
| Zero orders day | Skip formula, output: "No orders were placed" |
| Very small revenue (<threshold) | Suppress strong diagnosis, mark "low sample size" |
| Both orders and AOV changed equally | Report "Mixed factors" |

---

## 7. Action Recommendation Engine

### Purpose

Convert diagnosis into actionable advice. Not generic tips - context-aware actions.

### Scenario-to-Action Mapping

| Condition | Interpretation | Real World Meaning | Recommendation |
|-----------|---------------|-------------------|----------------|
| Orders ↓ but AOV stable | Conversion failure | Checkout/payment/shipping issue | "Customers are reaching your store but fewer are completing purchase. Check payment gateway logs, shipping costs, or recent checkout changes." |
| Orders stable but Revenue ↓ | AOV drop | Customers buying cheaper products | "Customers are choosing lower-priced items than usual. Review stock levels of premium products or active discounts." |
| Items/order ↓ | Bundle loss | Cross-sell removed or OOS | "Shoppers are adding fewer items to each order. Verify cross-sell widgets and product recommendations visibility." |
| Returning customers ↓ | Retention drop | Product dissatisfaction or season gap | "Fewer repeat customers purchased today. This often happens after delivery issues or poor product experience." |
| New customers ↓ | Acquisition drop | Ads stopped or tracking broken | "Fewer new customers are making first purchases. Review advertising campaigns, referral traffic, or tracking setup." |
| Refunds ↑ sharply | Quality mismatch | Wrong size/expectation/defect | "Refunds increased significantly. Review recent orders - likely product expectation mismatch or defect batch." |
| Revenue ↑ but Orders ↓ | Premium skew | High ticket purchases increased | "Revenue improved from higher-value purchases. Consider highlighting premium products while demand is strong." |
| Mixed factors | No clear issue | Multiple small changes | "No clear issue detected. Monitor the next day before making changes." |

### Trigger Thresholds

```
orders_change      < -12%  AND  aov_change within ±5%  → Conversion failure
aov_change         < -10%  AND  orders stable           → AOV drop
items_per_order    < -10%                                → Bundle loss
returning_customers < -15%                               → Retention drop
refund_rate_change > +25%                                → Quality mismatch
```

### Campaign-Aware Tone Adjustment

When a campaign is active, suppress irrelevant warnings:

| Campaign Goal | Suppress |
|---------------|----------|
| Increase Orders | Low AOV warnings |
| Increase AOV | Low order count warnings |
| Clearance / Liquidation | Price drop alerts |
| New Product Launch | Conversion instability alerts |

Prefix all campaign-mode messages with:
> "During your campaign: [adjusted message]"

---

## 8. WordPress Hooks & Events Map

### Core Principle

Events should be **lightweight**. They only mark affected dates as dirty. All heavy lifting happens in nightly cron.

Never:
- Run aggregation during events
- Block checkout flow
- Calculate analytics in real-time

### Dirty Date Triggers

```php
// A) Order Created / Updated
add_action('woocommerce_new_order', 'mark_order_date_dirty');
add_action('woocommerce_update_order', 'mark_order_date_dirty');

// B) Order Status Changed (very important - affects revenue)
add_action('woocommerce_order_status_changed', 'mark_original_date_dirty');

// C) Refund Created (affects original order date)
add_action('woocommerce_order_refunded', 'mark_original_date_dirty');
```

**What each handler does:**
```php
function mark_order_date_dirty($order_id) {
    $order = wc_get_order($order_id);
    $order_date = $order->get_date_created()->date('Y-m-d');

    // INSERT IGNORE - no duplicates, very lightweight
    INSERT IGNORE INTO wp_salespulse_dirty_dates
        (stat_date, reason, detected_at)
    VALUES ($order_date, 'order_update', NOW());
}
```

### What We Do NOT Hook

- Product updates (doesn't affect revenue snapshots)
- Coupon creation (analytics tables already reflect final state)
- Customer profile changes (not relevant to daily revenue)

### Scheduled Jobs

| Hook Name | Schedule | Purpose |
|-----------|----------|---------|
| `matriq_msa_nightly_snapshot` | Daily at 02:10 AM | Build yesterday snapshot + repair dirty dates |
| `matriq_msa_backfill_runner` | Every 5 min (during backfill only) | Process 1-3 missing historical days |
| `matriq_msa_send_digest_email` | Daily at 08:00 AM | Send morning briefing email (if enabled) |

### Nightly Cron Flow

```
matriq_msa_nightly_snapshot fires at 02:10 AM
    |
    ├── Step 1: Build yesterday's snapshot
    |   └── Query WC analytics tables for yesterday
    |   └── Compute all metrics
    |   └── INSERT/UPDATE into daily_stats
    |
    ├── Step 2: Check dirty_dates table
    |   └── For each dirty date:
    |       └── Rebuild that day's snapshot
    |       └── DELETE from dirty_dates
    |
    └── Step 3: Update system_state
        └── last_snapshot_date = yesterday
```

---

## 9. Snapshot System

### Why Snapshots Instead of Live Queries

| Approach | Performance | Accuracy | Complexity |
|----------|------------|----------|------------|
| Live queries on every page load | Slow (500ms-5s) | Current | High |
| Pre-computed nightly snapshots | Instant (<50ms) | Yesterday | Low |

**We choose snapshots** because:
- Store owners check **yesterday**, not real-time
- Query performance stays constant regardless of order volume
- Same approach enables future trend detection, anomaly detection, forecasting

### Snapshot Generation

```php
function build_snapshot($date) {
    $start = $date . ' 00:00:00';
    $end   = $date . ' 23:59:59';

    $metrics = $data_collector->get_day_metrics($start, $end);

    // Pre-compute derived values
    $metrics['avg_order_value'] = $metrics['orders'] > 0
        ? $metrics['revenue'] / $metrics['orders'] : 0;

    $metrics['items_per_order'] = $metrics['orders'] > 0
        ? $metrics['items_sold'] / $metrics['orders'] : 0;

    $metrics['avg_item_price'] = $metrics['items_sold'] > 0
        ? $metrics['revenue'] / $metrics['items_sold'] : 0;

    // Insert or update
    $wpdb->replace('wp_salespulse_daily_stats', $metrics);
}
```

### Snapshot Timing

**Why 02:10 AM?**
- Midnight orders have settled
- Payment gateways have finalized
- Refunds have synced
- Avoids race conditions with other cron jobs

### Data Freshness

| Period | Method | Accuracy |
|--------|--------|----------|
| Today | Not shown (or "partial" label) | Incomplete |
| Yesterday | Finalized snapshot | Authoritative |
| Older | Immutable history (unless dirty-repaired) | Authoritative |

### Dirty Date Repair

When an old order is edited/refunded:
1. Hook fires → marks that date dirty
2. Nightly cron checks dirty_dates table
3. Rebuilds ONLY affected date(s)
4. Deletes from dirty_dates

System stays accurate without heavy full recalculations.

---

## 10. Backfill Strategy - First Install Experience

### The Problem

Store owner installs plugin → opens dashboard → sees empty screen → uninstalls.

### The Solution: Progressive Reverse-Chronological Backfill

We build history from **newest to oldest** because merchants only care about recent performance.

### Phases

**Phase 1 - Instant Insight (0-5 seconds)**
```
Build: Yesterday + Day before yesterday
Result: Diagnosis engine works immediately

Dashboard shows real insight:
"Revenue dropped 12% yesterday mainly due to fewer orders"

→ Value delivered. User trusts plugin.
```

**Phase 2 - Recent Context (5-20 seconds)**
```
Background job builds: Last 7 → 14 → 30 days
Result: Weekly comparison unlocks

Dashboard updates automatically. No waiting screen.
```

**Phase 3 - Deep History (background, silent)**
```
Cron continues: Month 2, 3, ... up to 12 months
Result: Full trend history available

User never blocked.
```

### Processing Method

Per-day aggregates from WC analytics tables (NOT per-order):
```
for each missing day (newest first):
    aggregate that date from wc_order_stats
    insert snapshot
    sleep(0.1)
```

**Batch size**: 1-3 days per cron iteration
**Max execution**: 15 seconds per run
**Resume**: Automatically via scheduled cron

### Performance Scaling

Because we process per-day (not per-order):
- 50 orders/day store: same speed as 10,000 orders/day store
- Performance depends on **days**, not order count

### UI During Backfill

Top banner:
> "Sales Pulse is learning your store...
> Analyzing last 30 days (8/30 completed)
> You can already use insights."

**Never block the dashboard.**

### Detect Store Age

Before starting, check oldest order date from WC analytics:
```sql
SELECT MIN(date_created) FROM wp_wc_order_stats;
```
Don't process empty years.

---

## 11. Campaign Context System

### Purpose

When a merchant runs a sale, launch, or ads burst - revenue behavior becomes intentional. We must prevent false alarms.

Campaign mode **doesn't change data**. It **changes interpretation**.

### UX

Dashboard top bar:
```
No campaign running  [ Start Campaign ]
```

When clicked:
```
Campaign Name: __________
Start Date: Today
End Date: Optional (leave empty for ongoing)
Primary Goal:
  ( ) Increase Orders
  ( ) Increase AOV
  ( ) Clearance / Liquidation
  ( ) New Product Launch
```

Active state:
```
Campaign Active: "Summer Sale" - diagnostics adapted
[ End Campaign ]
```

### Internal Logic

```php
// Check if campaign active today
SELECT * FROM wp_salespulse_campaigns
WHERE start_date <= CURDATE()
AND (end_date IS NULL OR end_date >= CURDATE())
LIMIT 1;
```

If active → modify Action Engine tone (see Section 7).

### What Changes During Campaign

| Component | Without Campaign | During Campaign |
|-----------|-----------------|-----------------|
| Snapshot data | Unchanged | Unchanged |
| Diagnosis math | Unchanged | Unchanged |
| Action recommendation | Standard | Suppressed/adapted based on goal |
| Email digest | Standard tone | "During your campaign..." prefix |

---

## 12. UI Structure - Morning Briefing

### Design Philosophy

This is NOT a dashboard. This is a **Morning Briefing**.

If they open it every morning → we win.
If it feels like a report → they never return.

### Overview Page Layout (Top → Bottom)

```
─────────────────────────────────────────────────
  Revenue dropped 18% mainly due to fewer orders
─────────────────────────────────────────────────

  [ Revenue    ]  [ Orders     ]  [ AOV        ]  [ Items/Order ]
  [ ₹32,500    ]  [ 48         ]  [ ₹677       ]  [ 1.9         ]
  [ -18%       ]  [ -21%       ]  [ +4%        ]  [ -9%         ]

─────────────────────────────────────────────────
  What changed in your store

  • Fewer visitors completed checkout         Impact: -₹5,200
  • Customers bought fewer items per order    Impact: -₹4,100
  • Discounts increased                       Impact: +₹1,900

─────────────────────────────────────────────────
  Suggested action (Clear cause identified)

  Customers are reaching your store but fewer are
  completing purchase. Check payment gateway logs,
  shipping costs, or recent checkout changes.

─────────────────────────────────────────────────
  [  Yesterday  ] [  Last 7 Days  ]

  ▁▃▅▇▅▃▁  (7-day revenue sparkline)
─────────────────────────────────────────────────
```

### UX Rules

- No dropdown filters initially
- Default = Yesterday vs Previous day
- Page loads under 300ms (snapshots!)
- One screen, minimal scrolling
- Mobile friendly (store owners check phone)
- Period toggle: buttons, not dropdown

---

## 13. Copy & Language System

### Tone: Calm Intelligence

Every sentence follows: **Observation → Cause → Guidance**

| Avoid | Because |
|-------|---------|
| Excitement ("Wow!") | Feels marketing-ish |
| Humor ("Uh-oh!") | Reduces trust in financial insight |
| Corporate jargon ("Transactional deterioration") | Hard to understand |
| Over AI personality | Feels fake |

**Target emotion**: Informed, not impressed.

### Headline Templates

**Revenue Drop - High confidence (>60%)**
- "Revenue decreased {%}% yesterday, mainly due to fewer orders."
- "Revenue fell {%}% driven by lower average order value."
- "Revenue declined {%}% because customers bought fewer items per order."

**Revenue Drop - Medium confidence (40-60%)**
- "Revenue decreased {%}% due to multiple smaller changes in customer behavior."

**Revenue Drop - Low confidence (<40%)**
- "Revenue decreased {%}% but no single strong cause was detected."

**Revenue Increase - High confidence**
- "Revenue increased {%}% primarily from more completed orders."
- "Revenue grew {%}% as customers purchased higher-value products."
- "Revenue improved {%}% due to larger baskets per order."

**Revenue Increase - Medium confidence**
- "Revenue increased {%}% from combined improvements across the store."

**Stable**
- "Revenue remained stable compared to the previous day. No action required."

### Metric Card Labels (Human Language)

| Label | Subtext |
|-------|---------|
| Revenue | money earned |
| Orders | completed purchases |
| Avg order value | spend per order |
| Items per order | basket size |

### Confidence Indicator Text

| Score | Display Text |
|-------|-------------|
| >60% | Clear cause identified |
| 40-60% | Likely cause detected |
| <40% | No strong single cause |

### Weekly Mode Copy Adjustment

Replace "yesterday" with "over the last 7 days":
> "Over the last 7 days revenue increased 14% mainly due to larger basket sizes."

---

## 14. Email Digest

### Philosophy

The email should feel like a **calm business briefing**, not a marketing email.

### Toggle Setting

```
[ ] Send daily performance summary email
Time: 08:00 AM (store timezone)
Recipient: Admin email (editable)
```

### Email Template

**Subject Lines:**
- Revenue down: `Revenue decreased 12% yesterday - here's why`
- Revenue up: `Revenue increased 9% yesterday`
- Stable: `Yesterday's revenue remained stable`

**Body:**
```
Sales Pulse - Daily Store Briefing
Date: February 14, 2026

─────────────────────────────────
CORE INSIGHT
Revenue decreased 12% mainly due to fewer completed orders.

─────────────────────────────────
KEY METRICS
Revenue:         ₹32,500  (-12%)
Orders:          48       (-18%)
Avg Order Value: ₹677     (+6%)
Items per Order: 1.9      (-8%)

─────────────────────────────────
SUGGESTED ACTION
Customers are completing fewer purchases.
Review payment logs or recent checkout changes.

─────────────────────────────────
→ View full breakdown in your dashboard
  [Link to Overview page]
```

### Send Conditions

- Always send (tone adjusts for stable days)
- Do NOT send if snapshot not completed
- Do NOT send if plugin just installed and backfill incomplete
- Stable day example: "Revenue remained stable compared to the previous day. No action required."

### Technical Implementation

```
matriq_msa_nightly_snapshot fires → snapshot built
    ↓
matriq_msa_send_digest_email fires at 08:00 AM
    ↓
Read yesterday's snapshot + diagnosis output
    ↓
Format email from template
    ↓
wp_mail() to configured recipient
```

---

## 15. Navigation & Menu Structure

### Top-Level Menu (Not Nested Under WooCommerce)

```
Sales Pulse                          ← Top-level, just below WooCommerce
 ├── Overview                        ← Default landing (Morning Briefing)
 ├── History                         ← Daily explanation timeline
 ├── Campaigns                       ← Start/stop/view campaigns
 └── Settings                        ← Minimal options
```

**Why top-level?**
- Forms a daily habit
- Feels like a primary tool, not a sub-feature
- Brand recall improves
- Not buried under WooCommerce → Analytics

### Icon

Heartbeat / activity pulse icon (matches "Sales Pulse" branding)

### Position

Just below WooCommerce menu item (so merchant mentally links it to store performance)

### Admin Bar Shortcut

Add top admin bar item for instant daily glance:
```
Sales Pulse: Revenue ↓12%
```

Clicking → goes to Overview page.

### First Visit After Activation

Auto-redirect to Sales Pulse → Overview with onboarding banner:
> "We're analyzing your recent store performance..."

---

## 16. Settings - Minimal by Design

### Philosophy

> "We don't ask how you want to analyze your store.
> We assume you want to know what changed and why."

### Settings Available

| Setting | Default | Notes |
|---------|---------|-------|
| Timezone | WordPress timezone | Override only if mismatch |
| Revenue Basis | Net Revenue | Toggle: Net (recommended) / Gross |
| Snapshot Time | 02:10 AM | Rarely changed |
| Email Digest | Off | Toggle + recipient email |

### What We Do NOT Add

- Product filters
- Category filters
- Status selection
- Custom metric builder
- Complex segmentation
- Dashboard widget configuration
- Custom date ranges (Daily + Weekly only in v1)

**Why?** Minimal settings → stable data structure → easier AI layer later.

---

## 17. Safety, Edge Cases & Fail-Safes

### Data Readiness Check

Before showing dashboard, verify:
```
✓ WooCommerce detected and active
✓ Analytics tables exist and populated
✓ At least 2 days of orders found
✓ Snapshot for yesterday exists
```

If not ready → show clear message with specific issue, not empty dashboard.

### Permission Control

Default: `manage_woocommerce` capability (not just any admin).

### Multi-Currency

- Snapshots use WooCommerce **base currency only**
- Clearly state: "All insights are calculated in store base currency"
- Do NOT attempt currency conversion in v1

### WP Cron Fallback

If WP Cron doesn't run (common issue on low-traffic sites):
```
When admin visits dashboard:
    If yesterday's snapshot missing:
        → Trigger background build silently
        → Show "Preparing your insights..." message
```

### Plugin Deactivation / Reactivation

- **Deactivate**: Do NOT delete snapshot tables. Preserve all historical data.
- **Reactivate**: Resume from last snapshot date. Fill gaps via backfill.
- **Uninstall** (explicit): Optionally clean up tables (with confirmation).

### Versioned Schema Migration

```php
// In activation hook
$current_db_version = get_system_state('db_version');
if ($current_db_version < REQUIRED_DB_VERSION) {
    run_migrations($current_db_version, REQUIRED_DB_VERSION);
}
```

### Debug Logging

Optional lightweight logging (disabled by default):
```
[2026-02-15 02:10:01] Nightly snapshot started
[2026-02-15 02:10:02] Snapshot for 2026-02-14 completed (revenue: 32500, orders: 48)
[2026-02-15 02:10:03] Dirty date 2026-02-12 repaired
[2026-02-15 02:10:03] Nightly snapshot finished
```

### Timezone Handling

Always use `wp_timezone()`. Never rely on server timezone.
Snapshot date boundaries must align with store timezone.

---

## 18. Brand & Tone - Calm Intelligence

### The Fourth Category

Not analytical (spreadsheet), not friendly (chatbot), not executive (boardroom).

**Calm Intelligence** = A finance assistant who speaks plainly.

### Communication Formula

Every sentence: **Observation → Cause → Guidance**

### Examples

| Bad | Also Bad | Correct |
|-----|----------|---------|
| "Uh-oh! Looks like conversions crashed" | "Transactional performance deterioration detected" | "Fewer customers completed purchase than usual" |
| "Great news! Your revenue is through the roof!" | "Revenue metrics indicate positive trajectory" | "Revenue increased 14% driven by higher order volume" |

### Visual Direction

| Aspect | Guideline |
|--------|-----------|
| Colors | Neutral base (white/soft gray), one primary accent (blue/indigo), red only for serious drops, green only for improvement |
| Typography | System font stack, readable > stylish, medium weight headings |
| Gradients | Avoid bright SaaS gradients (feel marketing, not financial) |
| Spacing | Generous whitespace, breathable layout |
| Charts | Minimal - only 7-day sparkline for context, not analysis |

### Emotional Target

When merchant reads Sales Pulse, they should feel:
> **Informed, not impressed.**

That emotion creates long-term daily usage.

---

## 19. Monetization Strategy

### Sales Pulse = Free Forever (Lead Magnet)

The free plugin's job is NOT revenue. Its job is:
1. Become a daily habit
2. Own the store's historical performance dataset
3. Build trust in diagnosis accuracy

### Conversion Psychology

After 2-3 weeks of daily usage, merchants start asking:
- "Why did returning customers drop?"
- "Which product caused this?"
- "Will this trend continue?"
- "What should I change?"

Natural upsell appears:
> "Store Copilot can answer this automatically"

### Never Paywall

- Daily diagnosis
- Weekly comparison
- Cause detection
- Suggested checks

If you lock the core brain → trust breaks.

### Premium Triggers (Store Copilot)

- Product-level cause detection
- Customer behavior insights
- Predictions & forecasting
- Alerts (email/WhatsApp/Slack)
- Automated recommendations
- Conversational assistant
- Cohort analysis

---

## 20. Current Codebase Impact

### What Gets Preserved

| Component | Path | Adaptation |
|-----------|------|------------|
| Plugin bootstrap | `sales-pulse.php`, `loader.php` | Modify to register new classes |
| REST API infrastructure | `inc/services/router.php`, `inc/traits/` | Reuse for new endpoints |
| Build system | `webpack.config.js`, Tailwind, PostCSS | Keep, update entry points |
| Radix UI components | `src/dashboard/Components/ui/` | Reuse for new UI |
| Utility layer | `inc/utils/sanitizer.php`, `helper.php` | Reuse as-is |
| Singleton trait | `inc/traits/get-instance.php` | Reuse |
| Admin menu shell | `admin/menu.php` | Restructure submenus |

### What Gets Rebuilt / Replaced

| Component | Current | New |
|-----------|---------|-----|
| Backend controllers | Minimal (1 model dispatcher) | 5+ new service classes |
| Database | None (reads WC tables directly) | 4 custom tables |
| Frontend pages | 4 report pages + dashboard | Morning Briefing + History + Campaigns + Settings |
| Routes | 1 endpoint (submit-topic) | 5+ new REST endpoints |
| Cron jobs | None | 3 scheduled jobs |

### New Backend Classes to Build

```
core/services/
├── DataCollector.php        - Reads WC analytics tables
├── SnapshotBuilder.php      - Aggregates day → inserts snapshot
├── DiagnosisEngine.php      - Revenue decomposition math
├── ActionEngine.php         - Rule-based recommendations
├── CampaignManager.php      - Campaign CRUD + active check
├── EmailDigest.php          - Morning briefing email
├── BackfillRunner.php       - Progressive history builder
└── SchemaManager.php        - Table creation + migrations
```

### New REST Endpoints

```
GET  /matriq-store-analytics/v1/overview/       - Diagnosis + metrics for dashboard
GET  /matriq-store-analytics/v1/history/        - List of daily explanations
POST /matriq-store-analytics/v1/campaigns/      - Create campaign
GET  /matriq-store-analytics/v1/campaigns/      - List campaigns
PUT  /matriq-store-analytics/v1/campaigns/{id}  - End campaign
GET  /matriq-store-analytics/v1/settings/       - Get settings
POST /matriq-store-analytics/v1/settings/       - Update settings
POST /matriq-store-analytics/v1/snapshot/       - Manual snapshot trigger (admin)
```

### What Gets Removed (Phase 2, after v2 is stable)

- Old report pages: Physical, Variable, ProductWise, Subscription
- Old dashboard homepage (HomePage.js)
- Traffic Analysis tab
- Unused Redux store patterns
- Old report-specific API endpoints

---

## 21. Four-Week Sprint Plan

### Week 1 - Data Foundation ("Make numbers exist")

**Goal**: Store one correct daily snapshot.

| # | Task | Details |
|---|------|---------|
| 1 | Plugin schema setup | Create 4 DB tables via `dbDelta` in activation hook |
| 2 | Schema migration system | `db_version` tracking for future updates |
| 3 | `DataCollector` class | Read from `wc_order_stats`, `wc_customer_lookup` |
| 4 | `SnapshotBuilder` class | Aggregate day metrics → insert into `daily_stats` |
| 5 | Manual snapshot button | Temporary admin tool: "Run snapshot for yesterday" |
| 6 | Accuracy verification | Compare snapshot values against WC Analytics reports |

**Milestone**: Click button → yesterday's stats saved correctly in DB.

---

### Week 2 - Automatic Intelligence ("Make meaning exist")

**Goal**: Plugin auto-explains revenue change.

| # | Task | Details |
|---|------|---------|
| 1 | `DiagnosisEngine` class | Midpoint decomposition math + confidence scoring |
| 2 | `ActionEngine` class | Rule-based recommendations from scenario mapping |
| 3 | Nightly cron job | Register `matriq_msa_nightly_snapshot` at 02:10 AM |
| 4 | Dirty date hooks | Hook order create/update/status/refund → mark dates |
| 5 | Simple admin page | Plain text diagnosis output on Overview page |
| 6 | System state tracking | `last_snapshot_date`, `backfill_complete` |

**Milestone**: Every morning, plugin auto-explains yesterday. No manual button needed.

---

### Week 3 - Product Experience ("Make it usable")

**Goal**: Real product that works on a live store.

| # | Task | Details |
|---|------|---------|
| 1 | Overview page UI | Headline + metric cards + what changed + suggested action |
| 2 | Period toggle | [Yesterday] [Last 7 Days] - same engine, different window |
| 3 | Campaign CRUD | Start/stop/view campaigns + tone adjustment |
| 4 | `BackfillRunner` class | Progressive reverse-chronological + safety limits |
| 5 | History page | Daily explanation list (date + headline + direction) |
| 6 | Restructure admin menu | Overview, History, Campaigns, Settings |

**Milestone**: Installable on real WooCommerce store with working daily insights.

---

### Week 4 - Trust & Retention ("Make it shippable")

**Goal**: Plugin is safe for public users.

| # | Task | Details |
|---|------|---------|
| 1 | `EmailDigest` class | Toggle, template, scheduled send at 08:00 AM |
| 2 | Data readiness checks | WC detected, analytics tables exist, orders found |
| 3 | Cron fallback | If snapshot missing when admin visits → background build |
| 4 | Empty store handling | Graceful messaging when <2 days of data |
| 5 | Settings page | Timezone, revenue basis, snapshot time, email toggle |
| 6 | Admin bar shortcut | "Sales Pulse: Revenue ↓12%" in wp-admin bar |
| 7 | Performance testing | Test on 50, 5K, 100K+ order stores |
| 8 | Debug logging | Optional, disabled by default |

**Milestone**: Launch candidate ready for real-world testing.

---

## 22. Future Roadmap - Store Copilot

### Store Copilot = Premium Plugin (Requires Sales Pulse)

| Feature | Description |
|---------|-------------|
| Product-level cause detection | "Jackets category caused 70% of revenue drop" |
| Customer behavior insights | "42% of revenue comes from 8% of customers" |
| Refund intelligence | "Refund rate increased 18% on Size L of Product X" |
| Predictive warnings | "If churn continues, MRR will decline 9% in 45 days" |
| Automated actions | Create retention coupons, draft emails, tag customers |
| Conversational AI | "Why are profits down?" → structured answer |
| Alerts | Email, WhatsApp, Slack notifications |
| Cohort analysis | Retention by acquisition month |
| Revenue forecasting | 30-day projection with confidence interval |

### Long-Term Vision (2-3 Years)

```
Sales Pulse = Store sensor (data truth engine)
Store Copilot = Store brain (AI reasoning + actions)

Together = Shopify-grade commerce intelligence inside WordPress

Eventually connect:
→ Payments → Shipping APIs → CRM → Ads → Inventory → Subscriptions
```

### The Ultimate Position

> "Sales Pulse monitors, diagnoses, and fixes your store automatically."

Not analytics. Not AI. Not reporting.
**Store performance understanding** → evolving into **Autonomous Commerce Operator**.

---

## 23. SQL Query Reference

All queries read from WooCommerce Analytics tables. Never touch `wp_posts` or `wp_postmeta`.

### Core Revenue Metrics (per day)

```sql
SELECT
    COUNT(DISTINCT order_id) as orders,
    SUM(net_total) as revenue,
    SUM(num_items_sold) as items_sold,
    SUM(total_sales - net_total) as discount_total
FROM {prefix}wc_order_stats
WHERE date_created BETWEEN %s AND %s
AND parent_id = 0
AND status IN ('wc-processing','wc-on-hold','wc-completed')
```

### Refund Total (per day)

```sql
SELECT
    ABS(SUM(net_total)) as refund_total
FROM {prefix}wc_order_stats
WHERE date_created BETWEEN %s AND %s
AND parent_id > 0
AND status = 'wc-refunded'
```

### New vs Returning Customers (per day)

```sql
SELECT
    SUM(CASE WHEN cl.date_registered >= %s THEN 1 ELSE 0 END) as new_customers,
    SUM(CASE WHEN cl.date_registered < %s THEN 1 ELSE 0 END) as returning_customers
FROM {prefix}wc_order_stats os
LEFT JOIN {prefix}wc_customer_lookup cl ON os.customer_id = cl.customer_id
WHERE os.date_created BETWEEN %s AND %s
AND os.parent_id = 0
AND os.status IN ('wc-processing','wc-on-hold','wc-completed')
```

### Important Notes

- Always use `{$wpdb->prefix}` for table names
- Always check if analytics tables exist before querying
- Use `net_total` (not `total_sales`) for revenue
- Filter `parent_id = 0` for order counts (excludes refund children)
- Include refund children separately for refund_total

---

## 24. Testing Strategy

### Recommended Test Stores

| Store Type | Orders | Tests |
|------------|--------|-------|
| Small | ~50 orders | Basic snapshot, diagnosis, empty day handling |
| Medium | ~5,000 orders | Accuracy, weekly comparison, backfill speed |
| Large | 100K+ orders | Performance, cron timing, memory usage |
| Refund-heavy | Mixed | Refund rate calculation, dirty date repair |
| Subscription | WC Subscriptions active | Graceful handling (v1 doesn't analyze subscriptions) |
| New store | <5 orders | Empty state, onboarding, minimum data messaging |

### Accuracy Verification

For each test store, compare Sales Pulse snapshots against:
1. WooCommerce Analytics → Revenue report (same date range)
2. WooCommerce Analytics → Orders report (same date range)
3. Manual calculation from order list

All values must match WooCommerce Analytics exactly.

### Performance Benchmarks

| Store Size | Snapshot Build | Dashboard Load | Target |
|------------|---------------|----------------|--------|
| 50 orders/day | < 0.2s | < 100ms | Pass |
| 500 orders/day | < 0.5s | < 100ms | Pass |
| 2,000 orders/day | < 1.5s | < 100ms | Pass |
| 10,000 orders/day | < 3s | < 100ms | Pass |

### Edge Case Tests

- [ ] Zero orders day → diagnosis says "No orders placed"
- [ ] Very small revenue → suppress strong diagnosis
- [ ] WC Analytics tables disabled → graceful error message
- [ ] WP Cron not running → fallback triggers on admin visit
- [ ] Plugin deactivated + reactivated → data preserved, backfill resumes
- [ ] Order edited after snapshot → dirty date detected, repaired next night
- [ ] Multi-currency store → base currency only, clearly labeled
- [ ] Campaign active → tone adjusted, no false alarms

---

*This document is the single source of truth for Sales Pulse v2 development.
Generated from strategic analysis sessions (1.txt-4.txt).*
