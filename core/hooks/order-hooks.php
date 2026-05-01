<?php
/**
 * Order Hooks.
 *
 * Listens for WooCommerce order events and marks affected dates as dirty
 * so the nightly snapshot can repair them.
 *
 * Lightweight - only writes to dirty_dates, never aggregates.
 *
 * @package EC_Sales_Pulse\Core\Hooks
 */

namespace EC_Sales_Pulse\Core\Hooks;

use EC_Sales_Pulse\Core\Database\DirtyDates;
use EC_Sales_Pulse\Inc\Traits\Get_Instance;

defined( 'ABSPATH' ) || exit;

class OrderHooks {
	use Get_Instance;

	/**
	 * Constructor - register WooCommerce hooks.
	 */
	public function __construct() {
		// New order created.
		add_action( 'woocommerce_new_order', [ $this, 'on_order_created' ], 10, 2 );

		// Order updated (edit, status change, etc.).
		add_action( 'woocommerce_update_order', [ $this, 'on_order_updated' ], 10, 2 );

		// Order status changed explicitly.
		add_action( 'woocommerce_order_status_changed', [ $this, 'on_status_changed' ], 10, 4 );

		// Order refunded.
		add_action( 'woocommerce_order_refunded', [ $this, 'on_order_refunded' ], 10, 2 );
	}

	/**
	 * Handle new order creation.
	 *
	 * @param int            $order_id Order ID.
	 * @param \WC_Order|null $order    Order object.
	 */
	public function on_order_created( $order_id, $order = null ): void {
		$this->mark_order_date_dirty( $order_id, $order );
	}

	/**
	 * Handle order update.
	 *
	 * @param int            $order_id Order ID.
	 * @param \WC_Order|null $order    Order object.
	 */
	public function on_order_updated( $order_id, $order = null ): void {
		$this->mark_order_date_dirty( $order_id, $order );
	}

	/**
	 * Handle order status change.
	 *
	 * @param int       $order_id   Order ID.
	 * @param string    $old_status Old status.
	 * @param string    $new_status New status.
	 * @param \WC_Order $order      Order object.
	 */
	public function on_status_changed( $order_id, $old_status, $new_status, $order ): void {
		$this->mark_order_date_dirty( $order_id, $order );
	}

	/**
	 * Handle order refund.
	 * Marks the ORIGINAL order date dirty (not the refund date).
	 *
	 * @param int $order_id  Original order ID.
	 * @param int $refund_id Refund ID.
	 */
	public function on_order_refunded( $order_id, $refund_id ): void {
		$this->mark_order_date_dirty( $order_id );
	}

	/**
	 * Extract order creation date and mark it dirty.
	 *
	 * @param int            $order_id Order ID.
	 * @param \WC_Order|null $order    Order object (optional - will be loaded if null).
	 */
	private function mark_order_date_dirty( int $order_id, $order = null ): void {
		// Skip refund child orders - we handle these via the parent.
		if ( ! $order ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		// Skip refund objects - only process parent orders.
		if ( $order->get_type() === 'shop_order_refund' ) {
			return;
		}

		$date_created = $order->get_date_created();
		if ( ! $date_created ) {
			return;
		}

		$stat_date = $date_created->date( 'Y-m-d' );

		DirtyDates::get_instance()->mark_dirty( $stat_date );
	}
}
