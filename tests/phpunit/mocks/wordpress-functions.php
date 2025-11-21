<?php
/**
 * Mock WordPress Functions
 *
 * Provides mock implementations of WordPress functions for unit testing
 * when WordPress test library is not available.
 *
 * @package RybbitAnalytics
 */

// Mock option storage.
$GLOBALS['wp_options'] = array();
$GLOBALS['wp_transients'] = array();

if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Mock get_option function.
	 *
	 * @param string $option  Option name.
	 * @param mixed  $default Default value.
	 * @return mixed Option value or default.
	 */
	function get_option( $option, $default = false ) {
		return isset( $GLOBALS['wp_options'][ $option ] ) ? $GLOBALS['wp_options'][ $option ] : $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	/**
	 * Mock update_option function.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Option value.
	 * @return bool True on success.
	 */
	function update_option( $option, $value ) {
		$GLOBALS['wp_options'][ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'add_option' ) ) {
	/**
	 * Mock add_option function.
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Option value.
	 * @return bool True on success.
	 */
	function add_option( $option, $value ) {
		$GLOBALS['wp_options'][ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	/**
	 * Mock delete_option function.
	 *
	 * @param string $option Option name.
	 * @return bool True on success.
	 */
	function delete_option( $option ) {
		unset( $GLOBALS['wp_options'][ $option ] );
		return true;
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	/**
	 * Mock wp_parse_args function.
	 *
	 * @param array $args     Values to merge.
	 * @param array $defaults Default values.
	 * @return array Merged array.
	 */
	function wp_parse_args( $args, $defaults = array() ) {
		if ( is_object( $args ) ) {
			$args = get_object_vars( $args );
		}
		return array_merge( $defaults, $args );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/**
	 * Mock sanitize_text_field function.
	 *
	 * @param string $str String to sanitize.
	 * @return string Sanitized string.
	 */
	function sanitize_text_field( $str ) {
		return strip_tags( trim( $str ) );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	/**
	 * Mock esc_url_raw function.
	 *
	 * @param string $url URL to sanitize.
	 * @return string Sanitized URL.
	 */
	function esc_url_raw( $url ) {
		return filter_var( $url, FILTER_SANITIZE_URL );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	/**
	 * Mock esc_url function.
	 *
	 * @param string $url URL to escape.
	 * @return string Escaped URL.
	 */
	function esc_url( $url ) {
		return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	/**
	 * Mock esc_attr function.
	 *
	 * @param string $text Text to escape.
	 * @return string Escaped text.
	 */
	function esc_attr( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	/**
	 * Mock esc_html function.
	 *
	 * @param string $text Text to escape.
	 * @return string Escaped text.
	 */
	function esc_html( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'absint' ) ) {
	/**
	 * Mock absint function.
	 *
	 * @param mixed $value Value to convert.
	 * @return int Absolute integer.
	 */
	function absint( $value ) {
		return abs( intval( $value ) );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	/**
	 * Mock wp_json_encode function.
	 *
	 * @param mixed $data Data to encode.
	 * @return string JSON string.
	 */
	function wp_json_encode( $data ) {
		return json_encode( $data );
	}
}

if ( ! function_exists( 'wp_parse_url' ) ) {
	/**
	 * Mock wp_parse_url function.
	 *
	 * @param string $url URL to parse.
	 * @return array Parsed URL components.
	 */
	function wp_parse_url( $url ) {
		return parse_url( $url );
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * Mock translation function.
	 *
	 * @param string $text   Text to translate.
	 * @param string $domain Text domain.
	 * @return string Translated text.
	 */
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	/**
	 * Mock is_admin function.
	 *
	 * @return bool False by default.
	 */
	function is_admin() {
		return false;
	}
}

if ( ! function_exists( 'is_singular' ) ) {
	/**
	 * Mock is_singular function.
	 *
	 * @return bool False by default.
	 */
	function is_singular() {
		return false;
	}
}

if ( ! function_exists( 'is_user_logged_in' ) ) {
	/**
	 * Mock is_user_logged_in function.
	 *
	 * @return bool False by default or value from global.
	 */
	function is_user_logged_in() {
		return isset( $GLOBALS['is_user_logged_in'] ) ? $GLOBALS['is_user_logged_in'] : false;
	}
}

if ( ! function_exists( 'wp_get_current_user' ) ) {
	/**
	 * Mock wp_get_current_user function.
	 *
	 * @return object Mock user object.
	 */
	function wp_get_current_user() {
		if ( isset( $GLOBALS['current_user'] ) ) {
			return $GLOBALS['current_user'];
		}
		return (object) array(
			'ID'    => 0,
			'roles' => array(),
		);
	}
}

if ( ! function_exists( 'home_url' ) ) {
	/**
	 * Mock home_url function.
	 *
	 * @param string $path Path to append.
	 * @return string URL.
	 */
	function home_url( $path = '' ) {
		return 'http://example.com' . $path;
	}
}

if ( ! function_exists( 'add_query_arg' ) ) {
	/**
	 * Mock add_query_arg function.
	 *
	 * @param array $args Query args.
	 * @return string Empty string for mock.
	 */
	function add_query_arg( $args ) {
		return '';
	}
}

if ( ! function_exists( 'set_transient' ) ) {
	/**
	 * Mock set_transient function.
	 *
	 * @param string $transient  Transient name.
	 * @param mixed  $value      Value.
	 * @param int    $expiration Expiration in seconds.
	 * @return bool True on success.
	 */
	function set_transient( $transient, $value, $expiration = 0 ) {
		$GLOBALS['wp_transients'][ $transient ] = $value;
		return true;
	}
}

if ( ! function_exists( 'flush_rewrite_rules' ) ) {
	/**
	 * Mock flush_rewrite_rules function.
	 */
	function flush_rewrite_rules() {
		// No-op for mock.
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	/**
	 * Mock WP_Error class.
	 */
	class WP_Error {
		/**
		 * Error code.
		 *
		 * @var string
		 */
		private $code;

		/**
		 * Error message.
		 *
		 * @var string
		 */
		private $message;

		/**
		 * Error data.
		 *
		 * @var mixed
		 */
		private $data;

		/**
		 * Constructor.
		 *
		 * @param string $code    Error code.
		 * @param string $message Error message.
		 * @param mixed  $data    Error data.
		 */
		public function __construct( $code = '', $message = '', $data = '' ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}

		/**
		 * Get error code.
		 *
		 * @return string Error code.
		 */
		public function get_error_code() {
			return $this->code;
		}

		/**
		 * Get error message.
		 *
		 * @return string Error message.
		 */
		public function get_error_message() {
			return $this->message;
		}

		/**
		 * Get error data.
		 *
		 * @return mixed Error data.
		 */
		public function get_error_data() {
			return $this->data;
		}
	}
}

if ( ! function_exists( 'is_wp_error' ) ) {
	/**
	 * Check if variable is WP_Error.
	 *
	 * @param mixed $thing Variable to check.
	 * @return bool True if WP_Error.
	 */
	function is_wp_error( $thing ) {
		return $thing instanceof WP_Error;
	}
}

if ( ! class_exists( 'WP_REST_Server' ) ) {
	/**
	 * Mock WP_REST_Server class.
	 */
	class WP_REST_Server {
		const READABLE  = 'GET';
		const EDITABLE  = 'POST';
		const CREATABLE = 'POST';
		const DELETABLE = 'DELETE';
	}
}

if ( ! class_exists( 'WP_REST_Controller' ) ) {
	/**
	 * Mock WP_REST_Controller class.
	 */
	class WP_REST_Controller {
		/**
		 * REST API namespace.
		 *
		 * @var string
		 */
		protected $namespace = '';
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	/**
	 * Mock WP_REST_Response class.
	 */
	class WP_REST_Response {
		/**
		 * Response data.
		 *
		 * @var mixed
		 */
		public $data;

		/**
		 * Response status code.
		 *
		 * @var int
		 */
		public $status;

		/**
		 * Constructor.
		 *
		 * @param mixed $data   Response data.
		 * @param int   $status Response status code.
		 */
		public function __construct( $data = null, $status = 200 ) {
			$this->data   = $data;
			$this->status = $status;
		}

		/**
		 * Get data.
		 *
		 * @return mixed Response data.
		 */
		public function get_data() {
			return $this->data;
		}

		/**
		 * Get status.
		 *
		 * @return int Response status.
		 */
		public function get_status() {
			return $this->status;
		}
	}
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
	/**
	 * Mock WP_REST_Request class.
	 */
	class WP_REST_Request {
		/**
		 * Request parameters.
		 *
		 * @var array
		 */
		private $params = array();

		/**
		 * JSON parameters.
		 *
		 * @var array
		 */
		private $json_params = array();

		/**
		 * Set parameter.
		 *
		 * @param string $key   Parameter key.
		 * @param mixed  $value Parameter value.
		 */
		public function set_param( $key, $value ) {
			$this->params[ $key ] = $value;
		}

		/**
		 * Get parameter.
		 *
		 * @param string $key Parameter key.
		 * @return mixed Parameter value.
		 */
		public function get_param( $key ) {
			return isset( $this->params[ $key ] ) ? $this->params[ $key ] : null;
		}

		/**
		 * Set JSON parameters.
		 *
		 * @param array $params JSON parameters.
		 */
		public function set_json_params( $params ) {
			$this->json_params = $params;
		}

		/**
		 * Get JSON parameters.
		 *
		 * @return array JSON parameters.
		 */
		public function get_json_params() {
			return $this->json_params;
		}
	}
}

if ( ! class_exists( 'WP_Query' ) ) {
	/**
	 * Mock WP_Query class.
	 */
	class WP_Query {
		/**
		 * Posts array.
		 *
		 * @var array
		 */
		public $posts = array();

		/**
		 * Constructor.
		 *
		 * @param array $args Query arguments.
		 */
		public function __construct( $args = array() ) {
			// Return mock posts for testing.
			$this->posts = array();
		}
	}
}

if ( ! function_exists( 'register_rest_route' ) ) {
	/**
	 * Mock register_rest_route function.
	 *
	 * @param string $namespace REST API namespace.
	 * @param string $route     REST API route.
	 * @param array  $args      Route arguments.
	 */
	function register_rest_route( $namespace, $route, $args ) {
		$GLOBALS['registered_rest_routes'][] = array(
			'namespace' => $namespace,
			'route'     => $route,
			'args'      => $args,
		);
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	/**
	 * Mock current_user_can function.
	 *
	 * @param string $capability Capability to check.
	 * @return bool True by default or value from global.
	 */
	function current_user_can( $capability ) {
		return isset( $GLOBALS['current_user_can'] ) ? $GLOBALS['current_user_can'] : true;
	}
}

if ( ! function_exists( 'get_permalink' ) ) {
	/**
	 * Mock get_permalink function.
	 *
	 * @param int $post_id Post ID.
	 * @return string Post permalink.
	 */
	function get_permalink( $post_id ) {
		return 'http://example.com/?p=' . $post_id;
	}
}

if ( ! function_exists( 'get_woocommerce_currency' ) ) {
	/**
	 * Mock get_woocommerce_currency function.
	 *
	 * @return string Currency code.
	 */
	function get_woocommerce_currency() {
		return 'USD';
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	/**
	 * Mock esc_html__ function.
	 *
	 * @param string $text   Text to translate.
	 * @param string $domain Text domain.
	 * @return string Escaped and translated text.
	 */
	function esc_html__( $text, $domain = 'default' ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'get_transient' ) ) {
	/**
	 * Mock get_transient function.
	 *
	 * @param string $transient Transient name.
	 * @return mixed Transient value or false.
	 */
	function get_transient( $transient ) {
		return isset( $GLOBALS['wp_transients'][ $transient ] ) ? $GLOBALS['wp_transients'][ $transient ] : false;
	}
}

if ( ! function_exists( 'delete_transient' ) ) {
	/**
	 * Mock delete_transient function.
	 *
	 * @param string $transient Transient name.
	 * @return bool True on success.
	 */
	function delete_transient( $transient ) {
		unset( $GLOBALS['wp_transients'][ $transient ] );
		return true;
	}
}

if ( ! function_exists( 'current_time' ) ) {
	/**
	 * Mock current_time function.
	 *
	 * @param string $type Type of time to return.
	 * @return string Current time.
	 */
	function current_time( $type ) {
		if ( 'mysql' === $type ) {
			return date( 'Y-m-d H:i:s' );
		}
		return time();
	}
}

/**
 * Mock WC_Product base class for type checking.
 */
if ( ! class_exists( 'WC_Product' ) ) {
	class WC_Product {
		protected $id;
		protected $sku = '';
		protected $name = '';
		protected $price = 0;
		protected $type = 'simple';
		protected $meta = array();
		protected $in_stock = true;
		protected $attributes = array();
		protected $variation_price_min = 0;
		protected $variation_price_max = 0;

		public function __construct( $id = 0 ) {
			$this->id = $id;
		}

		public function get_id() {
			return $this->id;
		}

		public function get_sku() {
			return $this->sku;
		}

		public function set_sku( $sku ) {
			$this->sku = $sku;
		}

		public function get_name() {
			return $this->name;
		}

		public function set_name( $name ) {
			$this->name = $name;
		}

		public function get_price() {
			return $this->price;
		}

		public function set_price( $price ) {
			$this->price = $price;
		}

		public function get_type() {
			return $this->type;
		}

		public function is_type( $type ) {
			return $this->type === $type;
		}

		public function is_in_stock() {
			return $this->in_stock;
		}

		public function set_in_stock( $in_stock ) {
			$this->in_stock = $in_stock;
		}

		public function get_meta( $key ) {
			return isset( $this->meta[ $key ] ) ? $this->meta[ $key ] : '';
		}

		public function set_meta( $key, $value ) {
			$this->meta[ $key ] = $value;
		}

		public function get_attribute( $attribute ) {
			return isset( $this->attributes[ $attribute ] ) ? $this->attributes[ $attribute ] : '';
		}

		public function set_attribute( $attribute, $value ) {
			$this->attributes[ $attribute ] = $value;
		}

		public function get_variation_price( $minmax = 'min' ) {
			return $minmax === 'min' ? $this->variation_price_min : $this->variation_price_max;
		}

		public function set_variation_prices( $min, $max ) {
			$this->variation_price_min = $min;
			$this->variation_price_max = $max;
		}

		public function get_available_variations() {
			return array();
		}
	}
}

/**
 * Mock WC_Order base class for type checking.
 */
if ( ! class_exists( 'WC_Order' ) ) {
	class WC_Order {
		protected $id;
		protected $number;
		protected $total = 0;
		protected $tax = 0;
		protected $shipping = 0;
		protected $currency = 'USD';
		protected $items = array();
		protected $meta = array();
		protected $coupons = array();
		protected $discount = 0;
		protected $payment = 'credit_card';
		protected $item_count = 0;

		public function __construct( $id = 0 ) {
			$this->id = $id;
			$this->number = 'ORDER-' . $id;
		}

		public function get_id() {
			return $this->id;
		}

		public function get_order_number() {
			return $this->number;
		}

		public function set_order_number( $number ) {
			$this->number = $number;
		}

		public function get_total() {
			return $this->total;
		}

		public function set_total( $total ) {
			$this->total = $total;
		}

		public function get_total_tax() {
			return $this->tax;
		}

		public function set_total_tax( $tax ) {
			$this->tax = $tax;
		}

		public function get_shipping_total() {
			return $this->shipping;
		}

		public function set_shipping_total( $shipping ) {
			$this->shipping = $shipping;
		}

		public function get_currency() {
			return $this->currency;
		}

		public function set_currency( $currency ) {
			$this->currency = $currency;
		}

		public function get_items() {
			return $this->items;
		}

		public function add_item( $item ) {
			$this->items[] = $item;
			$this->item_count += $item->get_quantity();
		}

		public function get_item_total( $item, $inc_tax = true ) {
			return $item->get_price();
		}

		public function get_item_count() {
			return $this->item_count;
		}

		public function get_meta( $key ) {
			return isset( $this->meta[ $key ] ) ? $this->meta[ $key ] : null;
		}

		public function update_meta_data( $key, $value ) {
			$this->meta[ $key ] = $value;
		}

		public function get_coupon_codes() {
			return $this->coupons;
		}

		public function set_coupon_codes( $coupons ) {
			$this->coupons = $coupons;
		}

		public function get_total_discount() {
			return $this->discount;
		}

		public function set_total_discount( $discount ) {
			$this->discount = $discount;
		}

		public function get_payment_method() {
			return $this->payment;
		}

		public function set_payment_method( $method ) {
			$this->payment = $method;
		}

		public function save() {
			// No-op for mock.
		}
	}
}

/**
 * Mock WC_Order_Item_Product class for order items.
 */
if ( ! class_exists( 'WC_Order_Item_Product' ) ) {
	class WC_Order_Item_Product {
		protected $name;
		protected $quantity = 1;
		protected $price = 0;
		protected $product;
		protected $variation_id = 0;
		protected $meta_data = array();

		public function __construct() {}

		public function get_name() {
			return $this->name;
		}

		public function set_name( $name ) {
			$this->name = $name;
		}

		public function get_quantity() {
			return $this->quantity;
		}

		public function set_quantity( $quantity ) {
			$this->quantity = $quantity;
		}

		public function get_price() {
			return $this->price;
		}

		public function set_price( $price ) {
			$this->price = $price;
		}

		public function get_product() {
			return $this->product;
		}

		public function set_product( $product ) {
			$this->product = $product;
		}

		public function get_variation_id() {
			return $this->variation_id;
		}

		public function set_variation_id( $id ) {
			$this->variation_id = $id;
		}

		public function get_meta_data() {
			return $this->meta_data;
		}

		public function set_meta_data( $meta ) {
			$this->meta_data = $meta;
		}
	}
}

/**
 * Mock WC_Cart class for cart operations.
 */
if ( ! class_exists( 'WC_Cart' ) ) {
	class WC_Cart {
		private $contents = array();
		private $total = 0;
		private $contents_count = 0;
		private $coupons = array();

		public function is_empty() {
			return empty( $this->contents );
		}

		public function get_cart() {
			return $this->contents;
		}

		public function add_item( $key, $data ) {
			$this->contents[ $key ] = $data;
			$this->contents_count += isset( $data['quantity'] ) ? $data['quantity'] : 1;
		}

		public function get_cart_contents_total() {
			return $this->total;
		}

		public function set_cart_contents_total( $total ) {
			$this->total = $total;
		}

		public function get_cart_contents_count() {
			return $this->contents_count;
		}

		public function get_applied_coupons() {
			return $this->coupons;
		}

		public function set_applied_coupons( $coupons ) {
			$this->coupons = $coupons;
		}
	}
}

/**
 * Mock WC_Session class for session operations.
 */
if ( ! class_exists( 'WC_Session' ) ) {
	class WC_Session {
		private $customer_id = 'test_customer_123';

		public function get_customer_id() {
			return $this->customer_id;
		}
	}
}

/**
 * Mock WooCommerce main class instance.
 */
if ( ! class_exists( 'Mock_WooCommerce_Instance' ) ) {
	class Mock_WooCommerce_Instance {
		public $cart;
		public $session;

		public function __construct() {
			$this->cart = new WC_Cart();
			$this->session = new WC_Session();
		}
	}
}

/**
 * Mock WC() function.
 */
if ( ! function_exists( 'WC' ) ) {
	function WC() {
		if ( ! isset( $GLOBALS['wc_instance'] ) ) {
			$GLOBALS['wc_instance'] = new Mock_WooCommerce_Instance();
		}
		return $GLOBALS['wc_instance'];
	}
}

/**
 * Mock wc_get_product function.
 */
if ( ! function_exists( 'wc_get_product' ) ) {
	function wc_get_product( $product_id ) {
		// Allow returning null for specific test cases.
		if ( isset( $GLOBALS['wc_get_product_returns_null'] ) && $GLOBALS['wc_get_product_returns_null'] ) {
			return null;
		}

		// Return custom mock if set.
		if ( isset( $GLOBALS['wc_test_product'] ) ) {
			return $GLOBALS['wc_test_product'];
		}

		$product = new WC_Product( $product_id );
		$product->set_sku( 'SKU-' . $product_id );
		$product->set_name( 'Test Product ' . $product_id );
		$product->set_price( 29.99 );

		return $product;
	}
}

/**
 * Mock wc_get_order function.
 */
if ( ! function_exists( 'wc_get_order' ) ) {
	function wc_get_order( $order_id ) {
		// Return custom mock if set.
		if ( isset( $GLOBALS['wc_test_order'] ) ) {
			return $GLOBALS['wc_test_order'];
		}

		$order = new WC_Order( $order_id );
		$order->set_order_number( 'ORDER-' . $order_id );
		$order->set_total( 99.99 );
		$order->set_total_tax( 10.00 );
		$order->set_shipping_total( 5.00 );

		return $order;
	}
}

/**
 * Mock get_the_terms function for product categories.
 */
if ( ! function_exists( 'get_the_terms' ) ) {
	function get_the_terms( $post_id, $taxonomy ) {
		if ( isset( $GLOBALS['wc_product_terms'][ $taxonomy ] ) ) {
			return $GLOBALS['wc_product_terms'][ $taxonomy ];
		}
		return false;
	}
}

/**
 * Mock taxonomy_is_product_attribute function.
 */
if ( ! function_exists( 'taxonomy_is_product_attribute' ) ) {
	function taxonomy_is_product_attribute( $taxonomy ) {
		return strpos( $taxonomy, 'pa_' ) === 0;
	}
}

/**
 * Mock wp_doing_ajax function.
 */
if ( ! function_exists( 'wp_doing_ajax' ) ) {
	function wp_doing_ajax() {
		return isset( $GLOBALS['wp_doing_ajax'] ) ? $GLOBALS['wp_doing_ajax'] : false;
	}
}

/**
 * Mock is_shop function.
 */
if ( ! function_exists( 'is_shop' ) ) {
	function is_shop() {
		return isset( $GLOBALS['is_shop'] ) ? $GLOBALS['is_shop'] : false;
	}
}

/**
 * Mock is_product function.
 */
if ( ! function_exists( 'is_product' ) ) {
	function is_product() {
		return isset( $GLOBALS['is_product'] ) ? $GLOBALS['is_product'] : false;
	}
}

/**
 * Mock is_product_category function.
 */
if ( ! function_exists( 'is_product_category' ) ) {
	function is_product_category() {
		return isset( $GLOBALS['is_product_category'] ) ? $GLOBALS['is_product_category'] : false;
	}
}

/**
 * Mock is_product_tag function.
 */
if ( ! function_exists( 'is_product_tag' ) ) {
	function is_product_tag() {
		return isset( $GLOBALS['is_product_tag'] ) ? $GLOBALS['is_product_tag'] : false;
	}
}

/**
 * Mock is_cart function.
 */
if ( ! function_exists( 'is_cart' ) ) {
	function is_cart() {
		return isset( $GLOBALS['is_cart'] ) ? $GLOBALS['is_cart'] : false;
	}
}

/**
 * Mock sanitize_key function.
 */
if ( ! function_exists( 'sanitize_key' ) ) {
	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) );
	}
}

if ( ! function_exists( 'file_exists' ) === false ) {
	// file_exists is a PHP function, no need to mock.
}
