<?php
/**
 * Campaigns Controller.
 *
 * CRUD for campaign context layer.
 * Campaigns affect diagnosis tone (suppress false alarms during sales).
 *
 * @package Matriq\MSA\Core\Controllers
 */

namespace Matriq\MSA\Core\Controllers;

use Matriq\MSA\Core\Database\Campaigns;

defined( 'ABSPATH' ) || exit;

/**
 * REST controller for tagging, listing, and ending campaigns. Mounted at
 * `matriq-store-analytics/v2/campaigns/*`. Campaigns colour the diagnosis but never
 * change `daily_stats` numbers.
 */
class CampaignsController extends BaseController {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'campaigns';

	/**
	 * Register routes.
	 */
	public function register_routes(): void {
		// List all campaigns.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'get_campaigns' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
			]
		);

		// Create a new campaign.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'create_campaign' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
				'args'                => [
					'name'       => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'goal'       => [
						'type'              => 'string',
						'required'          => true,
						'enum'              => array_keys( Campaigns::get_valid_goals() ),
						'sanitize_callback' => 'sanitize_text_field',
					],
					'start_date' => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'end_date'   => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		// End a campaign early.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)/end',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'end_campaign' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
				'args'                => [
					'id' => [
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
				],
			]
		);

		// Delete a campaign.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'delete_campaign' ],
				'permission_callback' => [ $this, 'admin_permission_check' ],
				'args'                => [
					'id' => [
						'type'              => 'integer',
						'required'          => true,
						'sanitize_callback' => 'absint',
					],
				],
			]
		);
	}

	/**
	 * List all campaigns (most recent first).
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_campaigns( \WP_REST_Request $request ): \WP_REST_Response {
		$campaigns = Campaigns::get_instance();
		$all       = $campaigns->get_all();

		$items = array_map(
			static function ( $row ) {
				return [
					'id'         => (int) $row->id,
					'name'       => $row->name,
					'goal'       => $row->goal,
					'start_date' => $row->start_date,
					'end_date'   => $row->end_date,
					'is_active'  => $row->end_date >= gmdate( 'Y-m-d' ) && $row->start_date <= gmdate( 'Y-m-d' ),
				];
			},
			$all
		);

		return $this->success( $items );
	}

	/**
	 * Create a new campaign.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function create_campaign( \WP_REST_Request $request ): \WP_REST_Response {
		$name       = sanitize_text_field( $request->get_param( 'name' ) );
		$goal       = sanitize_text_field( $request->get_param( 'goal' ) );
		$start_date = $this->get_date_param( $request, 'start_date' );
		$end_date   = $this->get_date_param( $request, 'end_date' );

		// Validate required fields.
		if ( empty( $name ) || empty( $goal ) || empty( $start_date ) || empty( $end_date ) ) {
			return $this->error( __( 'All fields are required.', 'matriq-store-analytics' ) );
		}

		// Validate goal.
		if ( ! array_key_exists( $goal, Campaigns::get_valid_goals() ) ) {
			return $this->error( __( 'Invalid campaign goal.', 'matriq-store-analytics' ) );
		}

		// Validate date order.
		if ( $end_date < $start_date ) {
			return $this->error( __( 'End date must be after start date.', 'matriq-store-analytics' ) );
		}

		$campaigns = Campaigns::get_instance();
		$id        = $campaigns->create( $name, $goal, $start_date, $end_date );

		if ( ! $id ) {
			return $this->error( __( 'Failed to create campaign.', 'matriq-store-analytics' ), 500 );
		}

		return $this->success(
			[
				'id'         => $id,
				'name'       => $name,
				'goal'       => $goal,
				'start_date' => $start_date,
				'end_date'   => $end_date,
			],
			201
		);
	}

	/**
	 * End a campaign early (set end_date to today).
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function end_campaign( \WP_REST_Request $request ): \WP_REST_Response {
		$id = absint( $request->get_param( 'id' ) );

		$campaigns = Campaigns::get_instance();
		$campaign  = $campaigns->find( $id );

		if ( ! $campaign ) {
			return $this->error( __( 'Campaign not found.', 'matriq-store-analytics' ), 404 );
		}

		$result = $campaigns->end_campaign( $id );

		if ( ! $result ) {
			return $this->error( __( 'Failed to end campaign.', 'matriq-store-analytics' ), 500 );
		}

		return $this->success(
			[
				'id'    => $id,
				'ended' => true,
			]
		);
	}

	/**
	 * Delete a campaign permanently.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function delete_campaign( \WP_REST_Request $request ): \WP_REST_Response {
		$id = absint( $request->get_param( 'id' ) );

		$campaigns = Campaigns::get_instance();
		$campaign  = $campaigns->find( $id );

		if ( ! $campaign ) {
			return $this->error( __( 'Campaign not found.', 'matriq-store-analytics' ), 404 );
		}

		$campaigns->delete( [ 'id' => $id ] );

		return $this->success(
			[
				'id'      => $id,
				'deleted' => true,
			]
		);
	}
}
