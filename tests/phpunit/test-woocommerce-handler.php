<?php
/**
 * WooCommerce Handler Tests
 *
 * Tests for the Rybbit WooCommerce integration including
 * view_item, add_to_cart, begin_checkout, and purchase events.
 *
 * @package RybbitAnalytics
 */

/**
 * Test the WooCommerce Handler class.
 */
class Test_Rybbit_Woocommerce_Handler extends PHPUnit\Framework\TestCase {

	/**
	 * WooCommerce Handler instance.
	 *
	 * @var Rybbit_Woocommerce_Handler
	 */
	private $handler;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Reset globals.
		$GLOBALS['wp_options']              = array();
		$GLOBALS['wp_transients']           = array();
		$GLOBALS['product']                 = null;
		$GLOBALS['wc_test_product']         = null;
		$GLOBALS['wc_test_order']           = null;
		$GLOBALS['wc_instance']             = null;
		$GLOBALS['wc_currency']             = 'USD';
		$GLOBALS['wc_product_terms']        = array();
		$GLOBALS['wp_doing_ajax']           = false;
		$GLOBALS['is_admin']                = false;
		$GLOBALS['is_shop']                 = false;
		$GLOBALS['is_product']              = false;
		$GLOBALS['is_product_category']     = false;
		$GLOBALS['is_product_tag']          = false;
		$GLOBALS['is_cart']                 = false;
		$GLOBALS['wc_get_product_returns_null'] = false;

		$this->handler = new Rybbit_Woocommerce_Handler();
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();
		unset( $GLOBALS['wp_options'] );
		unset( $GLOBALS['wp_transients'] );
		unset( $GLOBALS['product'] );
		unset( $GLOBALS['wc_test_product'] );
		unset( $GLOBALS['wc_test_order'] );
		unset( $GLOBALS['wc_instance'] );
	}

	/**
	 * Helper to create a test product.
	 *
	 * @param int    $id    Product ID.
	 * @param string $sku   Product SKU.
	 * @param string $name  Product name.
	 * @param float  $price Product price.
	 * @param string $type  Product type.
	 * @return WC_Product
	 */
	private function create_product( $id, $sku, $name, $price, $type = 'simple' ) {
		$product = new WC_Product( $id );
		$product->set_sku( $sku );
		$product->set_name( $name );
		$product->set_price( $price );

		// Access protected property via reflection if needed for type.
		$reflection = new ReflectionClass( $product );
		$prop       = $reflection->getProperty( 'type' );
		$prop->setAccessible( true );
		$prop->setValue( $product, $type );

		return $product;
	}

	/**
	 * Helper to create a test order.
	 *
	 * @param int    $id       Order ID.
	 * @param string $number   Order number.
	 * @param float  $total    Order total.
	 * @param float  $tax      Order tax.
	 * @param float  $shipping Order shipping.
	 * @return WC_Order
	 */
	private function create_order( $id, $number, $total, $tax, $shipping ) {
		$order = new WC_Order( $id );
		$order->set_order_number( $number );
		$order->set_total( $total );
		$order->set_total_tax( $tax );
		$order->set_shipping_total( $shipping );
		return $order;
	}

	/**
	 * Helper to create a test order item.
	 *
	 * @param string     $name     Item name.
	 * @param int        $quantity Item quantity.
	 * @param float      $price    Item price.
	 * @param WC_Product $product  Product object.
	 * @return WC_Order_Item_Product
	 */
	private function create_order_item( $name, $quantity, $price, $product ) {
		$item = new WC_Order_Item_Product();
		$item->set_name( $name );
		$item->set_quantity( $quantity );
		$item->set_price( $price );
		$item->set_product( $product );
		return $item;
	}

	/**
	 * Test init doesn't register hooks when WooCommerce not active.
	 */
	public function test_init_without_woocommerce() {
		// Ensure WooCommerce class doesn't exist for this test.
		if ( class_exists( 'WooCommerce' ) ) {
			$this->markTestSkipped( 'WooCommerce class exists, skipping test' );
		}

		$this->handler->init();

		// Since WooCommerce is not active, no hooks should be registered.
		$this->assertTrue( true );
	}

	/**
	 * Test init doesn't register hooks when tracking disabled.
	 */
	public function test_init_when_tracking_disabled() {
		$settings = array(
			'woocommerce' => array(
				'enabled' => false,
			),
		);

		update_option( 'rybbit_settings', $settings );

		$this->handler->init();

		// Should exit early without error.
		$this->assertTrue( true );
	}

	/**
	 * Test track_view_item generates correct event data.
	 */
	public function test_track_view_item() {
		$GLOBALS['product'] = $this->create_product( 123, 'TEST-SKU-123', 'Test Product', 49.99 );

		ob_start();
		$this->handler->track_view_item();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<script', $output );
		$this->assertStringContainsString( 'window.rybbit.event', $output );
		$this->assertStringContainsString( '"view_item"', $output );
		$this->assertStringContainsString( 'TEST-SKU-123', $output );
		$this->assertStringContainsString( 'Test Product', $output );
		$this->assertStringContainsString( '49.99', $output );
		$this->assertStringContainsString( 'USD', $output );
	}

	/**
	 * Test track_view_item uses product ID when SKU is empty.
	 */
	public function test_track_view_item_without_sku() {
		$GLOBALS['product'] = $this->create_product( 456, '', 'Test Product', 29.99 );

		ob_start();
		$this->handler->track_view_item();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'product-456', $output );
	}

	/**
	 * Test track_view_item returns early when no product.
	 */
	public function test_track_view_item_no_product() {
		$GLOBALS['product'] = null;

		ob_start();
		$this->handler->track_view_item();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test track_view_item includes category information.
	 */
	public function test_track_view_item_with_categories() {
		$GLOBALS['product'] = $this->create_product( 123, 'SKU-123', 'Test Product', 29.99 );

		// Mock category terms.
		$term1       = new stdClass();
		$term1->name = 'Electronics';
		$term2       = new stdClass();
		$term2->name = 'Phones';

		$GLOBALS['wc_product_terms']['product_cat'] = array( $term1, $term2 );

		ob_start();
		$this->handler->track_view_item();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'item_category', $output );
		$this->assertStringContainsString( 'Electronics', $output );
		$this->assertStringContainsString( 'item_category2', $output );
	}

	/**
	 * Test track_view_item includes stock status.
	 */
	public function test_track_view_item_includes_stock_status() {
		$product = $this->create_product( 123, 'SKU-123', 'Test Product', 29.99 );
		$product->set_in_stock( true );
		$GLOBALS['product'] = $product;

		ob_start();
		$this->handler->track_view_item();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'in_stock', $output );
		$this->assertStringContainsString( 'true', $output );
	}

	/**
	 * Test track_view_item for variable product includes price range.
	 */
	public function test_track_view_item_variable_product() {
		$product = $this->create_product( 123, 'VAR-SKU', 'Variable Product', 0, 'variable' );
		$product->set_variation_prices( 19.99, 49.99 );
		$GLOBALS['product'] = $product;

		ob_start();
		$this->handler->track_view_item();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'price_min', $output );
		$this->assertStringContainsString( '19.99', $output );
		$this->assertStringContainsString( 'price_max', $output );
		$this->assertStringContainsString( '49.99', $output );
	}

	/**
	 * Test store_add_to_cart_data stores data in transient.
	 */
	public function test_store_add_to_cart_data() {
		// Ensure not AJAX request.
		$GLOBALS['wp_doing_ajax'] = false;

		// Initialize WC session.
		WC();

		$this->handler->store_add_to_cart_data( 'cart_key_1', 123, 2, 0, array(), array() );

		// Check transient was set.
		$transient = get_transient( 'rybbit_add_to_cart_test_customer_123' );
		$this->assertIsArray( $transient );
		$this->assertEquals( 2, $transient['quantity'] );
		$this->assertStringContainsString( 'SKU-123', $transient['item_id'] );
	}

	/**
	 * Test store_add_to_cart_data does not store during AJAX.
	 */
	public function test_store_add_to_cart_data_during_ajax() {
		$GLOBALS['wp_doing_ajax'] = true;

		$this->handler->store_add_to_cart_data( 'cart_key_1', 123, 2, 0, array(), array() );

		// Transient should not be set during AJAX.
		$transient = get_transient( 'rybbit_add_to_cart_test_customer_123' );
		$this->assertFalse( $transient );
	}

	/**
	 * Test store_add_to_cart_data with variation.
	 */
	public function test_store_add_to_cart_data_with_variation() {
		$GLOBALS['wp_doing_ajax'] = false;
		WC();

		$variation = array(
			'attribute_pa_color' => 'red',
			'attribute_pa_size'  => 'large',
		);

		$this->handler->store_add_to_cart_data( 'cart_key_1', 123, 1, 456, $variation, array() );

		$transient = get_transient( 'rybbit_add_to_cart_test_customer_123' );
		$this->assertIsArray( $transient );
		$this->assertEquals( 456, $transient['variation_id'] );
		$this->assertStringContainsString( 'Color', $transient['variation'] );
		$this->assertStringContainsString( 'Red', $transient['variation'] );
	}

	/**
	 * Test output_stored_add_to_cart_event outputs stored event.
	 */
	public function test_output_stored_add_to_cart_event() {
		WC();

		// Store event data.
		$event_data = array(
			'item_id'   => 'SKU-123',
			'item_name' => 'Test Product',
			'price'     => 29.99,
			'quantity'  => 2,
			'currency'  => 'USD',
		);
		set_transient( 'rybbit_add_to_cart_test_customer_123', $event_data, 60 );

		ob_start();
		$this->handler->output_stored_add_to_cart_event();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'add_to_cart', $output );
		$this->assertStringContainsString( 'SKU-123', $output );
		$this->assertStringContainsString( '29.99', $output );

		// Transient should be deleted.
		$this->assertFalse( get_transient( 'rybbit_add_to_cart_test_customer_123' ) );
	}

	/**
	 * Test track_begin_checkout generates correct event data.
	 */
	public function test_track_begin_checkout() {
		$wc   = WC();
		$cart = $wc->cart;

		// Add items to cart.
		$product = $this->create_product( 123, 'SKU-123', 'Test Product', 29.99 );
		$cart->add_item(
			'cart_item_1',
			array(
				'data'     => $product,
				'quantity' => 2,
			)
		);
		$cart->set_cart_contents_total( 59.98 );

		ob_start();
		$this->handler->track_begin_checkout();
		$output = ob_get_clean();

		$this->assertStringContainsString( '<script', $output );
		$this->assertStringContainsString( 'begin_checkout', $output );
		$this->assertStringContainsString( '59.98', $output );
		$this->assertStringContainsString( 'SKU-123', $output );
		$this->assertStringContainsString( 'items_count', $output );
	}

	/**
	 * Test track_begin_checkout with coupons.
	 */
	public function test_track_begin_checkout_with_coupons() {
		$wc   = WC();
		$cart = $wc->cart;

		$product = $this->create_product( 123, 'SKU-123', 'Test Product', 29.99 );
		$cart->add_item(
			'cart_item_1',
			array(
				'data'     => $product,
				'quantity' => 1,
			)
		);
		$cart->set_cart_contents_total( 24.99 );
		$cart->set_applied_coupons( array( 'SAVE5', 'WELCOME' ) );

		ob_start();
		$this->handler->track_begin_checkout();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'coupon', $output );
		$this->assertStringContainsString( 'SAVE5,WELCOME', $output );
	}

	/**
	 * Test track_begin_checkout with variation items.
	 */
	public function test_track_begin_checkout_with_variations() {
		$wc   = WC();
		$cart = $wc->cart;

		$product = $this->create_product( 456, 'VAR-SKU', 'Variable Product - Red', 34.99 );
		$cart->add_item(
			'cart_item_1',
			array(
				'data'         => $product,
				'quantity'     => 1,
				'variation_id' => 456,
				'variation'    => array(
					'attribute_pa_color' => 'red',
				),
			)
		);
		$cart->set_cart_contents_total( 34.99 );

		ob_start();
		$this->handler->track_begin_checkout();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'variation_id', $output );
		$this->assertStringContainsString( '456', $output );
		$this->assertStringContainsString( 'variation', $output );
		$this->assertStringContainsString( 'Color', $output );
	}

	/**
	 * Test track_begin_checkout returns early for empty cart.
	 */
	public function test_track_begin_checkout_empty_cart() {
		WC(); // Initialize empty cart.

		ob_start();
		$this->handler->track_begin_checkout();
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test track_purchase generates correct event data.
	 */
	public function test_track_purchase() {
		$order   = $this->create_order( 789, 'ORDER-789', 99.99, 10.00, 5.00 );
		$product = $this->create_product( 123, 'SKU-123', 'Test Product', 29.99 );
		$item    = $this->create_order_item( 'Test Product', 2, 29.99, $product );
		$order->add_item( $item );

		$GLOBALS['wc_test_order'] = $order;

		ob_start();
		$this->handler->track_purchase( 789 );
		$output = ob_get_clean();

		$this->assertStringContainsString( '<script', $output );
		$this->assertStringContainsString( 'purchase', $output );
		$this->assertStringContainsString( 'ORDER-789', $output );
		$this->assertStringContainsString( '99.99', $output );
		$this->assertStringContainsString( '"tax":10', $output );
		$this->assertStringContainsString( '"shipping":5', $output );
		$this->assertStringContainsString( 'USD', $output );
		$this->assertStringContainsString( 'items_count', $output );
	}

	/**
	 * Test track_purchase includes coupon and discount.
	 */
	public function test_track_purchase_with_coupon_and_discount() {
		$order = $this->create_order( 789, 'ORDER-789', 89.99, 9.00, 5.00 );
		$order->set_coupon_codes( array( 'SAVE10' ) );
		$order->set_total_discount( 10.00 );

		$product = $this->create_product( 123, 'SKU-123', 'Test Product', 44.995 );
		$item    = $this->create_order_item( 'Test Product', 2, 44.995, $product );
		$order->add_item( $item );

		$GLOBALS['wc_test_order'] = $order;

		ob_start();
		$this->handler->track_purchase( 789 );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'coupon', $output );
		$this->assertStringContainsString( 'SAVE10', $output );
		$this->assertStringContainsString( 'discount', $output );
		$this->assertStringContainsString( '10', $output );
	}

	/**
	 * Test track_purchase includes payment method.
	 */
	public function test_track_purchase_includes_payment_method() {
		$order = $this->create_order( 789, 'ORDER-789', 99.99, 10.00, 5.00 );
		$order->set_payment_method( 'paypal' );

		$product = $this->create_product( 123, 'SKU-123', 'Test Product', 99.99 );
		$item    = $this->create_order_item( 'Test Product', 1, 99.99, $product );
		$order->add_item( $item );

		$GLOBALS['wc_test_order'] = $order;

		ob_start();
		$this->handler->track_purchase( 789 );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'payment_method', $output );
		$this->assertStringContainsString( 'paypal', $output );
	}

	/**
	 * Test track_purchase returns early when no order_id.
	 */
	public function test_track_purchase_no_order_id() {
		ob_start();
		$this->handler->track_purchase( null );
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test track_purchase deduplication.
	 */
	public function test_track_purchase_prevents_duplicate() {
		$order = $this->create_order( 789, 'ORDER-789', 99.99, 10.00, 5.00 );
		$order->update_meta_data( '_rybbit_tracked', true );

		$GLOBALS['wc_test_order'] = $order;

		ob_start();
		$this->handler->track_purchase( 789 );
		$output = ob_get_clean();

		// Should return early - no output.
		$this->assertEmpty( $output );
	}

	/**
	 * Test track_purchase marks order as tracked.
	 */
	public function test_track_purchase_marks_order_tracked() {
		$order   = $this->create_order( 789, 'ORDER-789', 99.99, 10.00, 5.00 );
		$product = $this->create_product( 123, 'SKU-123', 'Test Product', 99.99 );
		$item    = $this->create_order_item( 'Test Product', 1, 99.99, $product );
		$order->add_item( $item );

		$GLOBALS['wc_test_order'] = $order;

		ob_start();
		$this->handler->track_purchase( 789 );
		ob_get_clean();

		// Order should be marked as tracked.
		$this->assertTrue( $order->get_meta( '_rybbit_tracked' ) );
		$this->assertNotEmpty( $order->get_meta( '_rybbit_tracked_at' ) );
	}

	/**
	 * Test output_tracking_event formats JSON correctly.
	 */
	public function test_output_tracking_event_json_format() {
		$reflection = new ReflectionClass( $this->handler );
		$method     = $reflection->getMethod( 'output_tracking_event' );
		$method->setAccessible( true );

		$event_data = array(
			'item_id'   => 'TEST-123',
			'item_name' => 'Test Product',
			'price'     => 29.99,
		);

		ob_start();
		$method->invoke( $this->handler, 'test_event', $event_data );
		$output = ob_get_clean();

		$this->assertStringContainsString( '"test_event"', $output );
		$this->assertStringContainsString( '"item_id":"TEST-123"', $output );
		$this->assertStringContainsString( '"price":29.99', $output );
		$this->assertStringContainsString( 'if (window.rybbit', $output );
	}

	/**
	 * Test output_tracking_event includes retry logic.
	 */
	public function test_output_tracking_event_includes_retry() {
		$reflection = new ReflectionClass( $this->handler );
		$method     = $reflection->getMethod( 'output_tracking_event' );
		$method->setAccessible( true );

		ob_start();
		$method->invoke( $this->handler, 'test_event', array() );
		$output = ob_get_clean();

		// Should contain retry logic with setInterval.
		$this->assertStringContainsString( 'setInterval', $output );
		$this->assertStringContainsString( 'clearInterval', $output );
	}

	/**
	 * Test event data uses correct numeric types.
	 */
	public function test_event_data_numeric_types() {
		$GLOBALS['product'] = $this->create_product( 123, 'SKU-123', 'Test', 29.99 );

		ob_start();
		$this->handler->track_view_item();
		$output = ob_get_clean();

		// Price should be numeric (not string with quotes).
		$this->assertStringContainsString( '"price":29.99', $output );
		$this->assertStringNotContainsString( '"price":"29.99"', $output );
	}

	/**
	 * Test format_variation_attributes helper.
	 */
	public function test_format_variation_attributes() {
		$reflection = new ReflectionClass( $this->handler );
		$method     = $reflection->getMethod( 'format_variation_attributes' );
		$method->setAccessible( true );

		$variation = array(
			'attribute_pa_color' => 'blue',
			'attribute_pa_size'  => 'medium',
		);

		$result = $method->invoke( $this->handler, $variation );

		$this->assertStringContainsString( 'Color: Blue', $result );
		$this->assertStringContainsString( 'Size: Medium', $result );
	}

	/**
	 * Test get_product_data includes item_type.
	 */
	public function test_get_product_data_includes_type() {
		$reflection = new ReflectionClass( $this->handler );
		$method     = $reflection->getMethod( 'get_product_data' );
		$method->setAccessible( true );

		$product = $this->create_product( 123, 'SKU-123', 'Test Product', 29.99, 'simple' );
		$result  = $method->invoke( $this->handler, $product );

		$this->assertEquals( 'simple', $result['item_type'] );
	}

	/**
	 * Test get_product_data includes brand if available.
	 */
	public function test_get_product_data_includes_brand() {
		$reflection = new ReflectionClass( $this->handler );
		$method     = $reflection->getMethod( 'get_product_data' );
		$method->setAccessible( true );

		$product = $this->create_product( 123, 'SKU-123', 'Test Product', 29.99 );
		$product->set_meta( '_brand', 'TestBrand' );

		$result = $method->invoke( $this->handler, $product );

		$this->assertEquals( 'TestBrand', $result['item_brand'] );
	}

	/**
	 * Test track_purchase with variation items.
	 */
	public function test_track_purchase_with_variation_items() {
		$order   = $this->create_order( 789, 'ORDER-789', 99.99, 10.00, 5.00 );
		$product = $this->create_product( 456, 'VAR-SKU', 'Variable Product - Red', 49.99 );

		$item = $this->create_order_item( 'Variable Product - Red', 1, 49.99, $product );
		$item->set_variation_id( 456 );
		$order->add_item( $item );

		$GLOBALS['wc_test_order'] = $order;

		ob_start();
		$this->handler->track_purchase( 789 );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'variation_id', $output );
		$this->assertStringContainsString( '456', $output );
	}
}
