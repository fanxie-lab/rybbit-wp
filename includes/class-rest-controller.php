<?php
/**
 * REST API Controller
 *
 * Registers and handles REST API endpoints for the plugin.
 *
 * @package RybbitAnalytics
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * REST API Controller class.
 *
 * Provides REST API endpoints for managing plugin settings and data.
 *
 * @since 1.0.0
 */
class Rybbit_Rest_Controller extends WP_REST_Controller {

	/**
	 * Namespace for REST API.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $namespace = 'rybbit/v1';

	/**
	 * Register REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		// GET /rybbit/v1/settings - Get plugin settings.
		register_rest_route(
			$this->namespace,
			'/settings',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		// POST /rybbit/v1/settings - Update plugin settings.
		register_rest_route(
			$this->namespace,
			'/settings',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => $this->get_settings_schema(),
			)
		);

		// POST /rybbit/v1/test-connection - Test Site ID connection.
		register_rest_route(
			$this->namespace,
			'/test-connection',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'test_connection' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'site_id' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// GET /rybbit/v1/posts - Get posts/pages for exclusions.
		register_rest_route(
			$this->namespace,
			'/posts',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_posts' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'post_type' => array(
						'default'           => 'page',
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'search'    => array(
						'default'           => '',
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'per_page'  => array(
						'default'           => 20,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// GET /rybbit/v1/woocommerce/sample-events - Get sample WooCommerce event data.
		if ( class_exists( 'WooCommerce' ) ) {
			register_rest_route(
				$this->namespace,
				'/woocommerce/sample-events',
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_sample_woocommerce_events' ),
					'permission_callback' => array( $this, 'check_permission' ),
				)
			);
		}

		// POST /rybbit/v1/validate-pattern - Validate URL pattern.
		register_rest_route(
			$this->namespace,
			'/validate-pattern',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'validate_pattern' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'pattern' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'description'       => __( 'URL pattern to validate (e.g., /admin/**, /user/*)' , 'rybbit-analytics' ),
					),
				),
			)
		);

		// POST /rybbit/v1/test-pattern - Test if a URL matches a pattern.
		register_rest_route(
			$this->namespace,
			'/test-pattern',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'test_pattern' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'pattern' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'description'       => __( 'URL pattern to test against' , 'rybbit-analytics' ),
					),
					'url'     => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'description'       => __( 'URL to test (can be full URL or just path)' , 'rybbit-analytics' ),
					),
				),
			)
		);
	}

	/**
	 * Check if user has permission to access endpoints.
	 *
	 * @since 1.0.0
	 * @return bool True if user can manage options, false otherwise.
	 */
	public function check_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get plugin settings.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_settings( $request ) {
		$settings = Rybbit_Settings_Manager::get_settings();

		return new WP_REST_Response( $settings, 200 );
	}

	/**
	 * Update plugin settings.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function update_settings( $request ) {
		$new_settings = $request->get_json_params();

		// Validate settings.
		$validation = Rybbit_Settings_Manager::validate_settings( $new_settings );
		if ( is_wp_error( $validation ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => $validation->get_error_message(),
					'errors'  => $validation->get_error_data(),
				),
				400
			);
		}

		// Update settings.
		$updated = Rybbit_Settings_Manager::update_settings( $new_settings );

		if ( $updated ) {
			return new WP_REST_Response(
				array(
					'success'  => true,
					'message'  => __( 'Settings updated successfully.', 'rybbit-analytics' ),
					'settings' => Rybbit_Settings_Manager::get_settings(),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'success' => false,
				'message' => __( 'Failed to update settings.', 'rybbit-analytics' ),
			),
			500
		);
	}

	/**
	 * Test connection to Rybbit.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function test_connection( $request ) {
		$site_id = $request->get_param( 'site_id' );

		if ( empty( $site_id ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Site ID is required.', 'rybbit-analytics' ),
				),
				400
			);
		}

		// For now, we'll just validate the site_id format.
		// In a real implementation, you might ping the Rybbit API.
		$valid = preg_match( '/^[a-zA-Z0-9\-_]+$/', $site_id );

		if ( $valid ) {
			return new WP_REST_Response(
				array(
					'success' => true,
					'message' => __( 'Connection test successful!', 'rybbit-analytics' ),
				),
				200
			);
		}

		return new WP_REST_Response(
			array(
				'success' => false,
				'message' => __( 'Invalid Site ID format.', 'rybbit-analytics' ),
			),
			400
		);
	}

	/**
	 * Get posts for exclusion selector.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_posts( $request ) {
		$post_type = $request->get_param( 'post_type' );
		$search    = $request->get_param( 'search' );
		$per_page  = $request->get_param( 'per_page' );

		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => $per_page,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		if ( ! empty( $search ) ) {
			$args['s'] = $search;
		}

		$query = new WP_Query( $args );
		$posts = array();

		foreach ( $query->posts as $post ) {
			$posts[] = array(
				'id'    => $post->ID,
				'title' => $post->post_title,
				'type'  => $post->post_type,
				'url'   => get_permalink( $post->ID ),
			);
		}

		return new WP_REST_Response( $posts, 200 );
	}

	/**
	 * Get sample WooCommerce event data.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function get_sample_woocommerce_events( $request ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'WooCommerce is not active.', 'rybbit-analytics' ),
				),
				400
			);
		}

		$sample_events = array(
			'view_item'      => array(
				'event_name' => 'view_item',
				'properties' => array(
					'item_id'   => 'SAMPLE-123',
					'item_name' => 'Sample Product',
					'price'     => 29.99,
					'currency'  => get_woocommerce_currency(),
				),
			),
			'add_to_cart'    => array(
				'event_name' => 'add_to_cart',
				'properties' => array(
					'item_id'   => 'SAMPLE-123',
					'item_name' => 'Sample Product',
					'price'     => 29.99,
					'quantity'  => 1,
					'currency'  => get_woocommerce_currency(),
				),
			),
			'begin_checkout' => array(
				'event_name' => 'begin_checkout',
				'properties' => array(
					'value'    => 59.98,
					'currency' => get_woocommerce_currency(),
					'items'    => array(
						array(
							'item_id'   => 'SAMPLE-123',
							'item_name' => 'Sample Product',
							'price'     => 29.99,
							'quantity'  => 2,
						),
					),
				),
			),
			'purchase'       => array(
				'event_name' => 'purchase',
				'properties' => array(
					'transaction_id' => 'ORDER-12345',
					'value'          => 69.98,
					'tax'            => 7.00,
					'shipping'       => 5.00,
					'currency'       => get_woocommerce_currency(),
					'items'          => array(
						array(
							'item_id'   => 'SAMPLE-123',
							'item_name' => 'Sample Product',
							'price'     => 29.99,
							'quantity'  => 2,
						),
					),
				),
			),
		);

		return new WP_REST_Response( $sample_events, 200 );
	}

	/**
	 * Validate a URL pattern.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function validate_pattern( $request ) {
		$pattern = $request->get_param( 'pattern' );

		if ( empty( $pattern ) ) {
			return new WP_REST_Response(
				array(
					'valid'   => false,
					'message' => __( 'Pattern is required.', 'rybbit-analytics' ),
				),
				400
			);
		}

		$result = Rybbit_Settings_Manager::validate_pattern( $pattern );

		return new WP_REST_Response( $result, $result['valid'] ? 200 : 400 );
	}

	/**
	 * Test if a URL matches a pattern.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function test_pattern( $request ) {
		$pattern = $request->get_param( 'pattern' );
		$url     = $request->get_param( 'url' );

		if ( empty( $pattern ) || empty( $url ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Both pattern and URL are required.', 'rybbit-analytics' ),
				),
				400
			);
		}

		// First validate the pattern.
		$validation = Rybbit_Settings_Manager::validate_pattern( $pattern );
		if ( ! $validation['valid'] ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'matches' => false,
					'message' => $validation['message'],
				),
				400
			);
		}

		// Test if the URL matches the pattern.
		$matches = Rybbit_Settings_Manager::url_matches_pattern( $url, $validation['normalized'] );

		return new WP_REST_Response(
			array(
				'success' => true,
				'matches' => $matches,
				'pattern' => $validation['normalized'],
				'url'     => $url,
				'message' => $matches
					? __( 'URL matches the pattern.', 'rybbit-analytics' )
					: __( 'URL does not match the pattern.', 'rybbit-analytics' ),
			),
			200
		);
	}

	/**
	 * Get settings schema for validation.
	 *
	 * @since  1.0.0
	 * @return array Schema definition.
	 */
	private function get_settings_schema() {
		return array(
			'site_id'            => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'script_url'         => array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
			),
			'connected'          => array(
				'type' => 'boolean',
			),
			'skip_patterns'      => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'string',
				),
			),
			'mask_patterns'      => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'string',
				),
			),
			'debounce_delay'     => array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			),
			'woocommerce'        => array(
				'type' => 'object',
			),
			'dashboard_features' => array(
				'type' => 'object',
			),
			'user_identification' => array(
				'type' => 'object',
			),
			'exclusions'         => array(
				'type' => 'array',
			),
		);
	}
}
