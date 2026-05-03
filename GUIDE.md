# Sales Pulse — debugging recipes

Practical wp-cli / SQL recipes for poking at Sales Pulse during dev. Run these from the **Local site shell** (Local app → "Open site shell"), not from your host terminal — `wp` isn't on the host PATH.

> All examples assume the default `wp_` table prefix. Replace if yours differs.

---

## 1. State inspection

### Is the data layer ready?

```bash
wp db query "SELECT meta_key, meta_value FROM wp_salespulse_system_state"
```

Key fields to look for:
- `backfill_complete = yes` — historical backfill finished. Pro features (forecaster, anomaly detector) refuse to run unless this is `yes`.
- `last_snapshot_date` — date of the most recent successful nightly snapshot.
- `db_version` (Sales Pulse), `pro_db_version` (Store Copilot).

### How many days of daily_stats are recorded?

```bash
wp db query "SELECT COUNT(*) AS days, MIN(stat_date) AS oldest, MAX(stat_date) AS newest FROM wp_salespulse_daily_stats"
```

### Inspect a specific day's snapshot

```bash
wp db query "SELECT * FROM wp_salespulse_daily_stats WHERE stat_date = '2026-05-02'"
```

### Pending dirty dates (queued for repair on next nightly run)

```bash
wp db query "SELECT * FROM wp_salespulse_dirty_dates ORDER BY marked_at DESC"
```

---

## 2. Force a snapshot rebuild

### Rebuild yesterday only

```bash
wp eval '
$date = (new DateTime("yesterday", wp_timezone()))->format("Y-m-d");
\EC_Sales_Pulse\Core\Services\SnapshotBuilder::get_instance()->build_snapshot($date);
echo "rebuilt $date\n";
'
```

### Rebuild a date range

```bash
wp eval '
$builder = \EC_Sales_Pulse\Core\Services\SnapshotBuilder::get_instance();
$tz = wp_timezone();
for ($i = 30; $i >= 1; $i--) {
    $d = (new DateTime("yesterday", $tz))->modify("-$i days")->format("Y-m-d");
    $builder->build_snapshot($d);
}
echo "done\n";
'
```

### Trigger the full nightly run (snapshot + post-snapshot hooks)

This is what cron fires at 02:10 site-time. Pro's `Forecaster` and `AnomalyDetector` listen on the `salespulse_after_nightly_snapshot` hook this emits.

```bash
wp eval '\EC_Sales_Pulse\Core\Services\SnapshotBuilder::get_instance()->run_nightly();'
```

---

## 3. Mark backfill complete (dev only)

If you bypassed the normal backfill (e.g. you direct-inserted into `wc_order_stats` to unblock testing), Pro features will silently skip because `backfill_complete != yes`. Flip it:

```bash
wp db query "INSERT INTO wp_salespulse_system_state (meta_key, meta_value)
VALUES ('backfill_complete', 'yes')
ON DUPLICATE KEY UPDATE meta_value = 'yes'"
```

To revert:

```bash
wp db query "DELETE FROM wp_salespulse_system_state WHERE meta_key = 'backfill_complete'"
```

---

## 4. Seed synthetic daily_stats (dev only)

Fast way to give Pro features something to chew on without waiting for real orders. Seeds 60 days with a weekly cycle so Holt-Winters has data to fit.

```bash
wp eval '
global $wpdb;
$tz = wp_timezone();
for ($i = 60; $i >= 1; $i--) {
    $d = (new DateTime("yesterday", $tz))->modify("-$i days")->format("Y-m-d");
    $wave   = 200 + 50 * sin($i / 7 * M_PI);
    $orders = max(1, (int) round($wave / 50));
    $rev    = round($wave + ($i % 7 === 0 ? 80 : 0), 2);
    $wpdb->replace("{$wpdb->prefix}salespulse_daily_stats", [
        "stat_date" => $d, "revenue" => $rev, "orders" => $orders,
        "items_sold" => $orders * 2, "avg_order_value" => $rev / $orders,
        "items_per_order" => 2, "avg_item_price" => $rev / ($orders * 2),
        "new_customers" => 0, "returning_customers" => $orders,
        "discount_total" => 0, "refund_total" => 0,
        "created_at" => current_time("mysql"), "updated_at" => current_time("mysql"),
    ]);
}
echo "seeded\n";
'
```

To inject an anomaly for AnomalyDetector to find, overwrite yesterday with an outlier value before running detect:

```bash
wp db query "UPDATE wp_salespulse_daily_stats
SET revenue = 1500, orders = 25
WHERE stat_date = (SELECT MAX(stat_date) FROM (SELECT stat_date FROM wp_salespulse_daily_stats) t)"
```

---

## 5. WooCommerce analytics sync gotcha

Orders created through wp-admin appear in `wp_posts` immediately but may NOT appear in `wp_wc_order_stats` until WC's analytics sync runs. `DataCollector` reads from `wp_wc_order_stats`, so the dashboard shows $0 / 0 orders despite real orders existing.

### Diagnose

```bash
wp db query "SELECT COUNT(*) AS post_orders FROM wp_posts WHERE post_type='shop_order' AND post_status='wc-completed'"
wp db query "SELECT COUNT(*) AS analytics_rows FROM wp_wc_order_stats"
```

If those don't match, sync hasn't caught up.

### Force WC's scheduler

```bash
wp wc admin tools clear_actionscheduler
wp action-scheduler run
```

### Workaround: direct insert from `wc_get_orders` (when WC's scheduler refuses)

```bash
wp eval '
global $wpdb;
$orders = wc_get_orders([ "limit" => -1, "status" => [ "completed", "processing" ] ]);
foreach ($orders as $order) {
    $wpdb->replace("{$wpdb->prefix}wc_order_stats", [
        "order_id"     => $order->get_id(),
        "parent_id"    => $order->get_parent_id() ?: 0,
        "date_created" => $order->get_date_created()->date("Y-m-d H:i:s"),
        "date_created_gmt" => $order->get_date_created()->date("Y-m-d H:i:s"),
        "num_items_sold"   => $order->get_item_count(),
        "total_sales"      => (float) $order->get_total(),
        "tax_total"        => (float) $order->get_total_tax(),
        "shipping_total"   => (float) $order->get_shipping_total(),
        "net_total"        => (float) $order->get_total() - (float) $order->get_total_tax() - (float) $order->get_shipping_total(),
        "returning_customer" => 0,
        "status"             => "wc-" . $order->get_status(),
        "customer_id"        => $order->get_customer_id(),
    ]);
}
echo count($orders) . " rows synced\n";
'
```

After this, rebuild snapshots (section 2) so daily_stats picks up the values.

---

## 6. REST endpoint quick-checks

```bash
# Overview payload (uses the active period from query string).
wp eval 'echo json_encode((array) wp_remote_retrieve_body(wp_remote_get(rest_url("sales-pulse/v2/overview?period=daily"), [ "headers" => [ "X-WP-Nonce" => wp_create_nonce("wp_rest") ] ])));'

# Data readiness gate.
wp eval 'echo json_encode((array) wp_remote_retrieve_body(wp_remote_get(rest_url("sales-pulse/v2/data-readiness"), [ "headers" => [ "X-WP-Nonce" => wp_create_nonce("wp_rest") ] ])));'
```

Easier alternative: hit them logged-in via the browser at
`http://localhost:10018/wp-json/sales-pulse/v2/overview?period=daily`.

---

## 7. Email digest

### Send the digest now (bypass schedule)

```bash
wp eval '\EC_Sales_Pulse\Core\Services\DigestMailer::get_instance()->maybe_send_nightly();'
```

### Force-send regardless of "already sent today" guard

```bash
wp eval '\EC_Sales_Pulse\Core\Services\DigestMailer::get_instance()->send_test();'
```

---

## 8. Reset for clean test runs

> **Destructive.** Only on dev installs.

```bash
# Wipe daily snapshots + dirty queue + system state. Schema stays.
wp db query "TRUNCATE TABLE wp_salespulse_daily_stats"
wp db query "TRUNCATE TABLE wp_salespulse_dirty_dates"
wp db query "DELETE FROM wp_salespulse_system_state WHERE meta_key IN ('backfill_complete','last_snapshot_date')"
```

Then re-trigger backfill or seed synthetic data (sections 2 / 4).
