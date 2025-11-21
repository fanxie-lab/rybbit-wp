<?php
/**
 * WooCommerce Handler
 *
 * Handles WooCommerce event tracking integration for Rybbit Analytics.
 * Implements standard ecommerce events: view_item, add_to_cart, begin_checkout, purchase.
 *
 * @package RybbitAnalytics
 * @since   1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * WooCommerce Handler class.
 *
 * Hooks into WooCommerce actions and generates tracking events for Rybbit Analytics.
 * Supports both standard page-based events and AJAX cart operations.
 *
 * @since 1.0.0
 */
class Rybbit_Woocommerce_Handler {

	/**
	 * Settings array.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $settings;

	/**
	 * WooCommerce events configuration.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $events;

	/**
	 * Flag to track if purchase event has been output on this page.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	private $purchase_event_output = false;

	/**
	 * Initialize the WooCommerce handler.
	 *
	 * Checks for WooCommerce availability and enabled settings before registering hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {
		// Only proceed if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$this->settings = Rybbit_Settings_Manager::get_settings();

		// Check if WooCommerce tracking is enabled.
		if ( empty( $this->settings['woocommerce']['enabled'] ) ) {
			return;
		}

		$this->events = $this->settings['woocommerce']['events'];

		// Register event hooks based on settings.
		$this->register_event_hooks();

		// Enqueue frontend script for AJAX cart events.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_script' ) );

		// Add product data to page for AJAX handling.
		add_action( 'wp_footer', array( $this, 'output_product_data_script' ), 5 );
	}

	/**
	 * Register WooCommerce event hooks based on settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	private function register_event_hooks() {
		// view_item event on single product pages.
		if ( ! empty( $this->events['view_item'] ) ) {
			add_action( 'woocommerce_after_single_product', array( $this, 'track_view_item' ) );
		}

		// add_to_cart event - hooks for both AJAX and non-AJAX scenarios.
		if ( ! empty( $this->events['add_to_cart'] ) ) {
			// Non-AJAX fallback: fires after redirect when AJAX is disabled.
			add_action( 'woocommerce_add_to_cart', array( $this, 'store_add_to_cart_data' ), 10, 6 );
			add_action( 'wp_footer', array( $this, 'output_stored_add_to_cart_event' ), 20 );
		}

		// begin_checkout event on checkout page.
		if ( ! empty( $this->events['begin_checkout'] ) ) {
			add_action( 'woocommerce_before_checkout_form', array( $this, 'track_begin_checkout' ) );
		}

		// purchase event on thank you page.
		if ( ! empty( $this->events['purchase'] ) ) {
			add_action( 'woocommerce_thankyou', array( $this, 'track_purchase' ), 10, 1 );
		}
	}

	/**
	 * Track view_item event on product pages.
	 *
	 * Fires on single product pages to track product views.
	 * Includes full product data including variations for variable products.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function track_view_item() {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$event_data = $this->get_product_data( $product );

		// Add category information.
		$categories = $this->get_product_categories( $product );
		if ( ! empty( $categories ) ) {
			$event_data['item_category'] = $categories[0];
			if ( isset( $categories[1] ) ) {
				$event_data['item_category2'] = $categories[1];
			}
		}

		// Add stock status.
		$event_data['in_stock'] = $product->is_in_stock();

		// For variable products, add price range.
		if ( $product->is_type( 'variable' ) ) {
			$event_data['price_min'] = (float) $product->get_variation_price( 'min' );
			$event_data['price_max'] = (float) $product->get_variation_price( 'max' );
		}

		$this->output_tracking_event( 'view_item', $event_data );
	}

	/**
	 * Store add_to_cart data in session for non-AJAX scenarios.
	 *
	 * When AJAX add-to-cart is disabled, this stores the data to be output on the next page load.
	 *
	 * @since 1.0.0
	 * @param string $cart_item_key   Cart item key.
	 * @param int    $product_id      Product ID.
	 * @param int    $quantity        Quantity added.
	 * @param int    $variation_id    Variation ID (0 for simple products).
	 * @param array  $variation       Variation attributes.
	 * @param array  $cart_item_data  Additional cart item data.
	 * @return void
	 */
	public function store_add_to_cart_data( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		// Only store if this is a non-AJAX request.
		if ( wp_doing_ajax() ) {
			return;
		}

		$product = wc_get_product( $variation_id ? $variation_id : $product_id );

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$event_data = $this->get_product_data( $product, $quantity );

		// Add variation attributes if present.
		if ( $variation_id && ! empty( $variation ) ) {
			$event_data['variation_id'] = $variation_id;
			$event_data['variation']    = $this->format_variation_attributes( $variation );
		}

		// Store in transient for output on next page load.
		set_transient(
			'rybbit_add_to_cart_' . WC()->session->get_customer_id(),
			$event_data,
			60 // 1 minute expiry.
		);
	}

	/**
	 * Output stored add_to_cart event data.
	 *
	 * Called on wp_footer to output any stored add_to_cart events from non-AJAX operations.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_stored_add_to_cart_event() {
		if ( ! WC()->session ) {
			return;
		}

		$transient_key = 'rybbit_add_to_cart_' . WC()->session->get_customer_id();
		$event_data    = get_transient( $transient_key );

		if ( $event_data ) {
			delete_transient( $transient_key );
			$this->output_tracking_event( 'add_to_cart', $event_data );
		}
	}

	/**
	 * Track begin_checkout event.
	 *
	 * Fires on checkout page load with full cart contents.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function track_begin_checkout() {
		$cart = WC()->cart;

		if ( ! $cart || $cart->is_empty() ) {
			return;
		}

		$items = array();

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = $cart_item['data'];

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$item_data = $this->get_product_data( $product, $cart_item['quantity'] );

			// Add variation data if present.
			if ( ! empty( $cart_item['variation_id'] ) && ! empty( $cart_item['variation'] ) ) {
				$item_data['variation_id'] = $cart_item['variation_id'];
				$item_data['variation']    = $this->format_variation_attributes( $cart_item['variation'] );
			}

			$items[] = $item_data;
		}

		$event_data = array(
			'value'       => (float) $cart->get_cart_contents_total(),
			'currency'    => get_woocommerce_currency(),
			'items_count' => $cart->get_cart_contents_count(),
			'items'       => $items,
		);

		// Add coupon information if present.
		$applied_coupons = $cart->get_applied_coupons();
		if ( ! empty( $applied_coupons ) ) {
			$event_data['coupon'] = implode( ',', $applied_coupons );
		}

		$this->output_tracking_event( 'begin_checkout', $event_data );
	}

	/**
	 * Track purchase event on thank you page.
	 *
	 * Fires on order received page with complete order data.
	 * Includes deduplication to prevent duplicate tracking on page refresh.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID.
	 * @return void
	 */
	public function track_purchase( $order_id ) {
		// Prevent multiple outputs on same page load.
		if ( $this->purchase_event_output ) {
			return;
		}

		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		// Check if we've already tracked this order (deduplication).
		$already_tracked = $order->get_meta( '_rybbit_tracked' );
		if ( $already_tracked ) {
			return;
		}

		$items = array();

		foreach ( $order->get_items() as $item_id => $item ) {
			$product = $item->get_product();

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$item_data = array(
				'item_id'   => $this->get_product_identifier( $product ),
				'item_name' => $item->get_name(),
				'price'     => (float) $order->get_item_total( $item, false ),
				'quantity'  => $item->get_quantity(),
			);

			// Add variation data if present.
			$variation_id = $item->get_variation_id();
			if ( $variation_id ) {
				$item_data['variation_id'] = $variation_id;

				// Get variation attributes from meta.
				$meta_data = $item->get_meta_data();
				$variation = array();
				foreach ( $meta_data as $meta ) {
					$data = $meta->get_data();
					// Check if this is a variation attribute.
					if ( strpos( $data['key'], 'pa_' ) === 0 || taxonomy_is_product_attribute( $data['key'] ) ) {
						$variation[ $data['key'] ] = $data['value'];
					}
				}
				if ( ! empty( $variation ) ) {
					$item_data['variation'] = $this->format_variation_attributes( $variation );
				}
			}

			$items[] = $item_data;
		}

		$event_data = array(
			'transaction_id' => $order->get_order_number(),
			'value'          => (float) $order->get_total(),
			'tax'            => (float) $order->get_total_tax(),
			'shipping'       => (float) $order->get_shipping_total(),
			'currency'       => $order->get_currency(),
			'items_count'    => $order->get_item_count(),
			'items'          => $items,
		);

		// Add coupon information if present.
		$coupons = $order->get_coupon_codes();
		if ( ! empty( $coupons ) ) {
			$event_data['coupon'] = implode( ',', $coupons );
		}

		// Add discount amount if present.
		$discount = $order->get_total_discount();
		if ( $discount > 0 ) {
			$event_data['discount'] = (float) $discount;
		}

		// Add payment method.
		$event_data['payment_method'] = $order->get_payment_method();

		$this->output_tracking_event( 'purchase', $event_data );

		// Mark order as tracked to prevent duplicates.
		$order->update_meta_data( '_rybbit_tracked', true );
		$order->update_meta_data( '_rybbit_tracked_at', current_time( 'mysql' ) );
		$order->save();

		$this->purchase_event_output = true;
	}

	/**
	 * Output tracking event JavaScript.
	 *
	 * Generates inline JavaScript that calls window.rybbit.event() with the provided data.
	 *
	 * @since 1.0.0
	 * @param string $event_name Event name (view_item, add_to_cart, begin_checkout, purchase).
	 * @param array  $event_data Event properties.
	 * @return void
	 */
	private function output_tracking_event( $event_name, $event_data ) {
		// Sanitize event name.
		$event_name = sanitize_key( $event_name );

		// JSON encode with proper escaping.
		$json_data = wp_json_encode( $event_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );

		if ( false === $json_data ) {
			return;
		}
		?>
		<script type="text/javascript">
		(function() {
			function trackEvent() {
				if (window.rybbit && typeof window.rybbit.event === 'function') {
					window.rybbit.event(<?php echo wp_json_encode( $event_name ); ?>, <?php echo $json_data; ?>);
				}
			}
			// Try immediately, then retry after a short delay if rybbit not loaded yet.
			if (window.rybbit && typeof window.rybbit.event === 'function') {
				trackEvent();
			} else {
				// Wait for rybbit to load.
				var checkInterval = setInterval(function() {
					if (window.rybbit && typeof window.rybbit.event === 'function') {
						clearInterval(checkInterval);
						trackEvent();
					}
				}, 100);
				// Stop checking after 5 seconds.
				setTimeout(function() { clearInterval(checkInterval); }, 5000);
			}
		})();
		</script>
		<?php
	}

	/**
	 * Enqueue frontend script for AJAX cart events.
	 *
	 * Loads JavaScript that handles AJAX add-to-cart events in WooCommerce.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_frontend_script() {
		// Only load on frontend, not admin.
		if ( is_admin() ) {
			return;
		}

		// Check if add_to_cart event is enabled.
		if ( empty( $this->events['add_to_cart'] ) ) {
			return;
		}

		wp_enqueue_script(
			'rybbit-woocommerce-events',
			RYBBIT_PLUGIN_URL . 'assets/js/woocommerce-events.js',
			array( 'jquery', 'wc-add-to-cart' ),
			RYBBIT_VERSION,
			true
		);

		// Pass settings to JavaScript.
		wp_localize_script(
			'rybbit-woocommerce-events',
			'rybbitWooCommerce',
			array(
				'currency' => get_woocommerce_currency(),
				'events'   => array(
					'add_to_cart' => ! empty( $this->events['add_to_cart'] ),
				),
				'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'rybbit_wc_nonce' ),
			)
		);
	}

	/**
	 * Output product data script for AJAX add-to-cart.
	 *
	 * Outputs JavaScript object containing product data for the current page,
	 * allowing the AJAX handler to access product information.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_product_data_script() {
		// Only on pages where we need product data.
		if ( ! is_shop() && ! is_product_category() && ! is_product_tag() && ! is_product() && ! is_cart() ) {
			return;
		}

		// Check if add_to_cart event is enabled.
		if ( empty( $this->events['add_to_cart'] ) ) {
			return;
		}

		global $wp_query;

		$product_data = array();

		// Get products from current query.
		if ( is_shop() || is_product_category() || is_product_tag() ) {
			// Archive pages - get all products in the loop.
			$products = wc_get_products(
				array(
					'limit'  => -1,
					'return' => 'objects',
					'status' => 'publish',
				)
			);

			// Limit to reasonable number for performance.
			$product_ids = array();
			if ( $wp_query->posts ) {
				foreach ( $wp_query->posts as $post ) {
					$product_ids[] = $post->ID;
				}
			}

			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$product_data[ $product_id ] = $this->get_product_data( $product );

					// For variable products, add variations.
					if ( $product->is_type( 'variable' ) ) {
						$variations = $product->get_available_variations();
						foreach ( $variations as $variation ) {
							$var_product = wc_get_product( $variation['variation_id'] );
							if ( $var_product ) {
								$product_data[ $variation['variation_id'] ] = $this->get_product_data( $var_product );
								$product_data[ $variation['variation_id'] ]['parent_id'] = $product_id;
							}
						}
					}
				}
			}
		} elseif ( is_product() ) {
			// Single product page.
			global $product;
			if ( $product instanceof WC_Product ) {
				$product_data[ $product->get_id() ] = $this->get_product_data( $product );

				// Add variation data for variable products.
				if ( $product->is_type( 'variable' ) ) {
					$variations = $product->get_available_variations();
					foreach ( $variations as $variation ) {
						$var_product = wc_get_product( $variation['variation_id'] );
						if ( $var_product ) {
							$product_data[ $variation['variation_id'] ] = $this->get_product_data( $var_product );
							$product_data[ $variation['variation_id'] ]['parent_id']  = $product->get_id();
							$product_data[ $variation['variation_id'] ]['attributes'] = $variation['attributes'];
						}
					}
				}
			}
		}

		// Only output if we have product data.
		if ( empty( $product_data ) ) {
			return;
		}

		$json_data = wp_json_encode( $product_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );

		if ( false === $json_data ) {
			return;
		}
		?>
		<script type="text/javascript">
		window.rybbitProductData = <?php echo $json_data; ?>;
		</script>
		<?php
	}

	/**
	 * Get standardized product data array.
	 *
	 * Extracts common product data fields used across all events.
	 *
	 * @since 1.0.0
	 * @param WC_Product $product  Product object.
	 * @param int        $quantity Quantity (default 1).
	 * @return array Product data array.
	 */
	private function get_product_data( $product, $quantity = 1 ) {
		$data = array(
			'item_id'   => $this->get_product_identifier( $product ),
			'item_name' => $product->get_name(),
			'price'     => (float) $product->get_price(),
			'quantity'  => (int) $quantity,
			'currency'  => get_woocommerce_currency(),
		);

		// Add product type.
		$data['item_type'] = $product->get_type();

		// Add brand if available (common meta field).
		$brand = $product->get_meta( '_brand' );
		if ( empty( $brand ) ) {
			$brand = $product->get_attribute( 'brand' );
		}
		if ( ! empty( $brand ) ) {
			$data['item_brand'] = $brand;
		}

		return $data;
	}

	/**
	 * Get product identifier (SKU or fallback to product ID).
	 *
	 * @since 1.0.0
	 * @param WC_Product $product Product object.
	 * @return string Product identifier.
	 */
	private function get_product_identifier( $product ) {
		$sku = $product->get_sku();
		return $sku ? $sku : 'product-' . $product->get_id();
	}

	/**
	 * Get product categories.
	 *
	 * @since 1.0.0
	 * @param WC_Product $product Product object.
	 * @return array Category names.
	 */
	private function get_product_categories( $product ) {
		$categories = array();
		$terms      = get_the_terms( $product->get_id(), 'product_cat' );

		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$categories[] = $term->name;
			}
		}

		return $categories;
	}

	/**
	 * Format variation attributes for event data.
	 *
	 * Converts WooCommerce variation attribute array to a clean string format.
	 *
	 * @since 1.0.0
	 * @param array $variation Variation attributes array.
	 * @return string Formatted variation string (e.g., "Color: Red, Size: Large").
	 */
	private function format_variation_attributes( $variation ) {
		$formatted = array();

		foreach ( $variation as $attribute => $value ) {
			// Clean up attribute name.
			$attribute_name = str_replace( array( 'attribute_pa_', 'attribute_', 'pa_' ), '', $attribute );
			$attribute_name = ucwords( str_replace( array( '-', '_' ), ' ', $attribute_name ) );

			// Clean up value.
			$value = ucwords( str_replace( array( '-', '_' ), ' ', $value ) );

			if ( ! empty( $value ) ) {
				$formatted[] = $attribute_name . ': ' . $value;
			}
		}

		return implode( ', ', $formatted );
	}

	/**
	 * Check if WooCommerce AJAX add-to-cart is enabled.
	 *
	 * @since 1.0.0
	 * @return bool True if AJAX add-to-cart is enabled.
	 */
	private function is_ajax_add_to_cart_enabled() {
		return 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart' );
	}
}
