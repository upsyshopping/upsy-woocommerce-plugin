<?php
/*
	Plugin Name: UPSY for WooCommerce
	Description: Implements tagging blocks for WooCommerce. This version of the plugin is made by Upseller Oy.
	Author: Upseller Oy
	Version: 2.0.0
	License: GPLv2
*/

/*	Copyright 2020 Upseller Oy

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/*  Original v1.x plugin https://wordpress.org/plugins/nosto-for-woocommerce/ */

/**
 * Main plugin class.
 *
 * @package WooCommerce Upsy Tagging
 * @since   1.0.0
 */
class WC_upsy_Tagging
{
	/**
	 * Plugin version.
	 * Used for dependency checks.
	 *
	 * @since 1.0.0
	 */
	const VERSION = '2.0.0';

	/**
	 * Minimum WordPress version this plugin works with.
	 * Used for dependency checks.
	 *
	 * @since 1.0.0
	 */
	const MIN_WP_VERSION = '4.4';

	/**
	 * Minimum WooCommerce plugin version this plugin works with.
	 * Used for dependency checks.
	 *
	 * @since 1.0.0
	 */
	const MIN_WC_VERSION = '2.6.0';

	/**
	 * Value for marking a product that is in stock.
	 * Used in product tagging.
	 *
	 * @since 1.0.0
	 */
	const PRODUCT_IN_STOCK = 'InStock';

	/**
	 * Value for marking a product that is not in stock.
	 * Used in product tagging.
	 *
	 * @since 1.0.0
	 */
	const PRODUCT_OUT_OF_STOCK = 'OutOfStock';

	/**
	 * The working instance of the plugin.
	 *
	 * @since 1.0.0
	 * @var WC_upsy_Tagging|null
	 */
	private static $instance = null;

	/*
	 * Variation product type
	 */
	const PRODUCT_TYPE_VARIATION = 'variable';

	/*
	 * Variation product type
	 */
	const PRODUCT_TYPE_SIMPLE = 'simple';

	/*
	 * Variation product type
	 */
	const PRODUCT_TYPE_GROUPED = 'grouped';

	/**
	 * Default server address for the upsy marketing automation service.
	 * Used on plugin config page.
	 *
	 * @since 1.0.0
	 */
	const DEFAULT_upsy_SERVER_ADDRESS = 'undefined.upsy.com';

	/**
	 * upsy page types
	 */
	const PAGE_TYPE_FRONT_PAGE = 'front';
	const PAGE_TYPE_CART = 'cart';
	const PAGE_TYPE_PRODUCT = 'product';
	const PAGE_TYPE_CATEGORY = 'category';
	const PAGE_TYPE_SEARCH = 'search';
	const PAGE_TYPE_NOTFOUND = 'generic'; //'notfound'
	const PAGE_TYPE_ORDER = 'checkout'; //'order'

	/**
	 * Templates
	 */
	const TEMPLATE_upsy_ELEMENTS = 'upsy-elements';
	const TEMPLATE_PRODUCT_TAGGING = 'product-tagging';
	const TEMPLATE_CATEGORY_TAGGING = 'category-tagging';
	const TEMPLATE_CUSTOMER_TAGGING = 'customer-tagging';
	const TEMPLATE_CART_TAGGING = 'cart-tagging';
	const TEMPLATE_ORDER_TAGGING = 'order-tagging';
	const TEMPLATE_CUSTOMER_SETTINGS = 'upsy-customer-settings';

	/**
	 * Elements / slots
	 */
	const ELEMENT_upsy_PAGE_PRODUCT_1 = 'upsy-page-product1';
	const ELEMENT_upsy_PAGE_PRODUCT_2 = 'upsy-page-product2';
	const ELEMENT_upsy_PAGE_PRODUCT_3 = 'upsy-page-product3';
	const ELEMENT_upsy_PAGE_CATEGORY_1 = 'upsy-page-category1';
	const ELEMENT_upsy_PAGE_CATEGORY_2 = 'upsy-page-category2';
	const ELEMENT_upsy_PAGE_CART_1 = 'upsy-page-cart1';
	const ELEMENT_upsy_PAGE_CART_2 = 'upsy-page-cart2';
	const ELEMENT_upsy_PAGE_CART_3 = 'upsy-page-cart3';
	const ELEMENT_upsy_PAGE_SEARCH_1 = 'upsy-page-search1';
	const ELEMENT_upsy_PAGE_SEARCH_2 = 'upsy-page-search2';
	const ELEMENT_upsy_PAGE_TOP = 'upsy-page-top';
	const ELEMENT_upsy_PAGE_BOTTOM = 'upsy-page-bottom';
	const ELEMENT_FRONTPAGE_upsy_1 = 'frontpage-upsy-1';
	const ELEMENT_FRONTPAGE_upsy_2 = 'frontpage-upsy-2';
	const ELEMENT_FRONTPAGE_upsy_3 = 'frontpage-upsy-3';
	const ELEMENT_FRONTPAGE_upsy_4 = 'frontpage-upsy-4';
	const ELEMENT_NOTFOUND_upsy_1 = 'notfound-upsy-1';
	const ELEMENT_NOTFOUND_upsy_2 = 'notfound-upsy-2';
	const ELEMENT_NOTFOUND_upsy_3 = 'notfound-upsy-3';

	/**
	 * Whitelist of product types that are allowed in product tagging.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected static $product_type_whitelist = array(
		self::PRODUCT_TYPE_SIMPLE,
		self::PRODUCT_TYPE_VARIATION,
		self::PRODUCT_TYPE_GROUPED,
	);

	/**
	 * The plugin directory path.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_dir = '';

	/**
	 * The URL to the plugin directory.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_url = '';

	/**
	 * The plugin base name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_name = '';

	/**
	 * The upsy server address.
	 * This is a setting configured on the admin page.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $server_address = '';

	/**
	 * The upsy account id.
	 * This is a setting configured on the admin page.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $account_id = '';

	/**
	 * If the default upsy elements should be outputted.
	 * This is a setting configured on the admin page.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $use_default_elements = '';

	/**
	 * The plugin base name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_display_name = 'Upsy';

	/**
	 * Gets the working instance of the plugin.
	 *
	 * @since 1.0.0
	 * @return WC_upsy_Tagging|null
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new WC_upsy_Tagging();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * Plugin uses Singleton pattern, hence the constructor is private.
	 *
	 * @since 1.0.0
	 * @return WC_upsy_Tagging
	 */
	private function __construct() {
		$this->plugin_dir  = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugin_dir_url( __FILE__ );
		$this->plugin_name = plugin_basename( __FILE__ );
		
		register_activation_hook( $this->plugin_name, array( $this, 'activate' ) );
		register_deactivation_hook( $this->plugin_name, array( $this, 'deactivate' ) );
		// The uninstall hook callback needs to be a static class method or function.
		register_uninstall_hook( $this->plugin_name, array( __CLASS__, 'uninstall' ) );
		// Add Upsy Menu and submenu to Sidebar
		add_action('admin_menu', array( $this, 'add_menu' ), 9);   
		add_action('admin_init', array( $this, 'populate_setting_fields' ));  
		
	}
	
	/**
	 * Adding Plugin to Sidebar.
	 *
	 * Handles the backend settings page integration for upsy.
	 *
	 * @since 1.0.0
	 */
	function add_menu()
	{
	    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
	    add_menu_page(  $this->plugin_name, $this->plugin_display_name, 'administrator', $this->plugin_name, array( $this, 'upsy_admin_page' ), 'dashicons-chart-area', 26 );

	    //add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
     	add_submenu_page( $this->plugin_name,$this->plugin_display_name, 'Settings', 'administrator', 'upsy-settings', array( $this, 'upsy_customer_settings' ));
	}

	/**
	 * Loading Customer settings page.
	 *
	 * Handles the backend settings page integration for upsy.
	 *
	 * @since 1.0.0
	 */
	function upsy_customer_settings()
	{
		// set this var to be used in the settings-display view
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
		if(isset($_GET['error_message'])){
				add_action('admin_notices', array($this,'upsy_settings_messages'));
				do_action( 'admin_notices', $_GET['error_message'] );
		}
	   $this->render(self::TEMPLATE_CUSTOMER_SETTINGS,[]);
	  
	}

	public function upsy_settings_messages($error_message){
		switch ($error_message) {
			case '1':
				$message = __( 'There is an error occured please try again.', '' );                 
				$err_code = esc_attr( 'upsy_settings_customer_id' );                 
				$setting_field = 'upsy_settings_customer_id';                 
				break;
		}
		$type = 'error';
		add_settings_error(
			   $setting_field,
			   $err_code,
			   $message,
			   $type
		   );
	}

	public function upsy_display_customer_settings() {
	
		echo '<p>Enter your upseller customer id.</p>';
	} 

	public function populate_setting_fields() {
		/**
		 * First, we add_settings_section. This is necessary since all future settings must belong to one.
		 * Second, add_settings_field
		 * Third, register_setting
		 */     
	
		add_settings_section(
			// ID used to identify this section and with which to register options
			'upsy_general_section', 
			// Title to be displayed on the administration page
			'',  
			// Callback used to render the description of the section
				array( $this, 'upsy_display_customer_settings' ),    
			// Page on which to add this section of options
			'upsy_customer_general_settings'                   
		);
	
		unset($args);
		$args = array (
			'type'      => 'input',
			'subtype'   => 'text',
			'id'    => 'upsy_settings_customer_id',
			'name'      => 'upsy_settings_customer_id',
			'required' => 'true',
			'get_options_list' => '',
			'value_type'=>'normal',
			'wp_data' => 'option'
		);

		add_settings_field(
			'upsy_settings_customer_id',
			'Upsy Customer ID',
			array( $this, 'upsy_customer_settings_field' ),
			'upsy_customer_general_settings',
			'upsy_general_section',
			$args
		);


		register_setting(
			'upsy_customer_general_settings',
			'upsy_settings_customer_id'
		);

	}


	public function upsy_customer_settings_field($args) {

		if($args['wp_data'] == 'option'){
			$wp_data_value = get_option($args['name']);
		} elseif($args['wp_data'] == 'post_meta'){
			$wp_data_value = get_post_meta($args['post_id'], $args['name'], true );
		}
	
		switch ($args['type']) {

			case 'input':
				$value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;
				if($args['subtype'] != 'checkbox'){
					$prependStart = (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">'.$args['prepend_value'].'</span>' : '';
					$prependEnd = (isset($args['prepend_value'])) ? '</div>' : '';
					$step = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
					$min = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
					$max = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
					if(isset($args['disabled'])){
						// hide the actual input bc if it was just a disabled input the informaiton saved in the database would be wrong - bc it would pass empty values and wipe the actual information
						echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'_disabled" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'_disabled" size="40" disabled value="' . esc_attr($value) . '" /><input type="hidden" id="'.$args['id'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
					} else {
						echo $prependStart.'<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" '.$step.' '.$max.' '.$min.' name="'.$args['name'].'" size="40" value="' . esc_attr($value) . '" />'.$prependEnd;
					}
					/*<input required="required" '.$disabled.' type="number" step="any" id="'.$this->plugin_name.'_cost2" name="'.$this->plugin_name.'_cost2" value="' . esc_attr( $cost ) . '" size="25" /><input type="hidden" id="'.$this->plugin_name.'_cost" step="any" name="'.$this->plugin_name.'_cost" value="' . esc_attr( $cost ) . '" />*/

				} else {
					$checked = ($value) ? 'checked' : '';
					echo '<input type="'.$args['subtype'].'" id="'.$args['id'].'" "'.$args['required'].'" name="'.$args['name'].'" size="40" value="1" '.$checked.' />';
				}
				break;
			default:
				# code...
				break;
		}
	}
	
	/**
	 * Initializes the plugin.
	 *
	 * Register hooks outputting tagging blocks and upsy elements in frontend.
	 * Handles the backend admin page integration.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		if ( is_admin() ) {
			$this->init_admin();
		} else {
			$this->init_frontend();
		}

		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	/**
	 * Hook callback function for activating the plugin.
	 *
	 * Checks WP and WC dependencies for plugin compatibility.
	 * Creates the Top Sellers page or only publishes it if it already exists.
	 *
	 * @since 1.0.0
	 */
	public function activate() {
		/*
		if ( $this->check_dependencies() ) {
			$this->load_class( 'WC_upsy_Tagging_Top_Sellers_Page' );
			$page_id = get_option( 'woocommerce_upsy_tagging_top_sellers_page_id', null );
			$page    = new WC_upsy_Tagging_Top_Sellers_Page( $page_id );
			$page->publish();
			if ( null === $page_id ) {
				add_option( 'woocommerce_upsy_tagging_top_sellers_page_id', $page->get_id() );
			} else {
				update_option( 'woocommerce_upsy_tagging_top_sellers_page_id', $page->get_id() );
			}
		}
		*/
	}

	/**
	 * Hook callback function for deactivating the plugin.
	 *
	 * Un-publishes the Top Sellers page.
	 *
	 * @since 1.0.0
	 */
	public function deactivate() {
		/*
		$page_id = get_option( 'woocommerce_upsy_tagging_top_sellers_page_id' );
		if ( $page_id ) {
			$this->load_class( 'WC_upsy_Tagging_Top_Sellers_Page' );
			$page = new WC_upsy_Tagging_Top_Sellers_Page( $page_id );
			$page->unpublish();
		}
		*/
	}

	/**
	 * Hook callback function for uninstalling the plugin.
	 *
	 * Deletes the Top Sellers page and plugin config values.
	 *
	 * @since 1.0.0
	 */
	public static function uninstall() {
		/*
		$page_id = get_option( 'woocommerce_upsy_tagging_top_sellers_page_id' );
		if ( $page_id ) {
			// This has to be a static method, so we load the top sellers class through
			// the main plugin instance. The instance will already exist at this point,
			// so there will be no unnecessary instantiation.
			// This is just to avoid duplicating the code in WC_upsy_Tagging::load_class().
			WC_upsy_Tagging::get_instance()->load_class( 'WC_upsy_Tagging_Top_Sellers_Page' );
			$page = new WC_upsy_Tagging_Top_Sellers_Page( $page_id );
			$page->remove();
		}
		*/

		delete_option( 'woocommerce_upsy_tagging_settings' );
		//delete_option( 'woocommerce_upsy_tagging_top_sellers_page_id' );
		//delete_option( 'widget_upsy_element' );
	}

	/**
	 * Getter for the plugin base name.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Hook callback function for tagging products.
	 *
	 * Gathers necessary data and renders the product tagging template ( templates/product-tagging.php ).
	 *
	 * @since 1.0.0
	 */
	public function tag_product() {
		if ( is_product() ) {
			/** @var $product WC_Product */
			global $product;

			if ( $product instanceof WC_Product && $product->is_type( self::$product_type_whitelist ) ) {
				$data       = array();
				$product_id = (int) $product->id;

				$data['url']        = (string) get_permalink();
				$data['product_id'] = $product_id;
				$data['name']       = (string) $product->get_title();

				$image_url = wp_get_attachment_url( get_post_thumbnail_id() );
				if ( ! empty( $image_url ) ) {
					$data['image_url'] = (string) $image_url;
				}

				$data['price']               = $this->format_price( $product->get_price_including_tax() );
				$data['price_currency_code'] = get_woocommerce_currency();
				$data['availability']        = $product->is_in_stock() ? self::PRODUCT_IN_STOCK : self::PRODUCT_OUT_OF_STOCK;

				$data['categories'] = array();
				$terms              = get_the_terms( $product->post->ID, 'product_cat' );
				if ( is_array( $terms ) ) {
					foreach ( $terms as $term ) {
						$category_path = $this->build_category_path( $term );
						if ( ! empty( $category_path ) ) {
							$data['categories'][] = $category_path;
						}
					}
				}

				$data['description']    = (string) $product->post->post_content;
				$data['list_price']     = $this->format_price( $this->get_list_price_including_tax( $product ) );

				if ( ! empty( $data ) ) {
					$this->render( self::TEMPLATE_PRODUCT_TAGGING, array( 'product' => $data ), self::PAGE_TYPE_PRODUCT );
				}
			}
		}
	}

	/**
	 * Hook callback function for tagging categories.
	 *
	 * Gathers necessary data and renders the category tagging template ( templates/category-tagging.php ).
	 *
	 * @since 1.0.0
	 */
	public function tag_category() {
		if ( is_product_category() ) {
			$term          = get_term_by( 'slug', esc_attr( get_query_var( 'product_cat' ) ), 'product_cat' );
			$category_path = $this->build_category_path( $term );

			$category = get_term_by( 'slug', esc_attr( get_query_var( 'product_cat' ) ), 'product_cat' );
			$cat_id = $category->term_id;

			$category_id = $cat_id;
			if ( ! empty( $category_path ) ) {
				$this->render( self::TEMPLATE_CATEGORY_TAGGING, array( 'category_path' => $category_path, 'category_id' => $category_id ), self::PAGE_TYPE_CATEGORY );
			}
		}
	}


	/**
	 * Hook callback function for tagging pages.
	 *
	 * Gathers necessary data and renders the category tagging template ( templates/category-tagging.php ).
	 *
	 * @since 1.0.0
	 */

	public function tag_page() {

		if (is_front_page()) {
			//echo 'FRONT';
			$page_type = 'front';
			if ( !empty( $page_type ) ) {
				require( $this->plugin_dir . 'templates/page-type.php');
			}
		}else if (is_product()){
				//echo 'PRODUCT: DONOTHING';
		}else if (is_product_category()){
				//echo 'CAT: DONOTHING';
		}else if (is_cart()){
				//echo 'CART';
				$page_type = 'cart';
				if ( !empty( $page_type ) ) {
					require( $this->plugin_dir . 'templates/page-type.php');
				}
		}else if (is_checkout()){
				//echo 'ORDER';
				$page_type = 'checkout'; //'order';
				if ( !empty( $page_type ) ) {
					require( $this->plugin_dir . 'templates/page-type.php');
				}

		}else{
			//echo 'NOTFOUND';
			$page_type = 'generic'; //'notfound';
			if ( !empty( $page_type ) ) {
				require( $this->plugin_dir . 'templates/page-type.php');
			}
		}
	}


	/**
	 * Hook callback function for tagging logged in customers.
	 *
	 * Gathers necessary data and renders the customer tagging template ( templates/customer-tagging.php ).
	 *
	 * @since 1.0.0
	 */
	public function tag_customer() {
		if ( is_user_logged_in() ) {
			$user     = wp_get_current_user();
			$customer = $this->get_customer_data( $user );
			if ( ! empty( $customer ) ) {
				$this->render( self::TEMPLATE_CUSTOMER_TAGGING, array( 'customer' => $customer ) );
			}
		}
	}

	/**
	 * Hook callback function for tagging cart content.
	 *
	 * Gathers necessary data and renders the cart tagging template ( templates/cart-tagging.php ).
	 *
	 * @since 1.0.0
	 */
	public function tag_cart() {
		/** @var $woocommerce Woocommerce */
		global $woocommerce;

		if ( $woocommerce->cart instanceof WC_Cart && 0 < count( $woocommerce->cart->get_cart() ) ) {
			$cart_items    = $woocommerce->cart->get_cart();
			$currency_code = get_woocommerce_currency();
			$line_items    = array();

			foreach ( $cart_items as $cart_item ) {
				if ( isset( $cart_item['data'] ) && $cart_item['data'] instanceof WC_Product ) {
					/** @var $product WC_Product */
					$product   = $cart_item['data'];
					$line_item = array(
						'product_id'          => (int) $cart_item['product_id'],
						'quantity'            => (int) $cart_item['quantity'],
						'name'                => (string) $product->get_title(),
						'unit_price'          => $this->format_price( $product->get_price_including_tax() ),
						'price_currency_code' => $currency_code,
					);

					$line_items[] = $line_item;
				}
			}

			if ( ! empty( $line_items ) ) {
				$this->render( self::TEMPLATE_CART_TAGGING, array( 'line_items' => $line_items ) );
			}
		}
	}

	/**
	 * Hook callback function for tagging successful orders.
	 *
	 * Gathers necessary data and renders the order tagging template ( templates/order-tagging.php ).
	 *
	 * @since 1.0.0
	 * @param int $order_id The id of the placed order
	 */
	public function tag_order( $order_id ) {
		if ( is_numeric( $order_id ) && 0 < $order_id ) {
			$order = new WC_Order( $order_id );

			$buyer = array(
				'first_name' => $order->billing_first_name,
				'last_name'  => $order->billing_last_name,
				'email'      => $order->billing_email,
			);

			$currency_code = get_woocommerce_currency();

			$data = array(
				'order_number' => $order->id,
				'buyer'        => $buyer,
				'line_items'   => array(),
			);

			foreach ( (array) $order->get_items() as $item ) {
				$line_item = array(
					'product_id'          => (int) $item['product_id'],
					'quantity'            => (int) $item['qty'],
					'name'                => (string) $item['name'],
					'unit_price'          => $this->format_price( $order->get_item_total( $item, true ) ),
					'price_currency_code' => $currency_code,
				);

				$data['line_items'][] = $line_item;
			}

			// Add special line items for discounts, shipping and "fees".
			if ( ! empty( $data['line_items'] ) ) {
				// All discounts applied to the order.
				$discount = $order->get_total_discount();
				if ( 0 < $discount ) {
					$data['line_items'][] = array(
						'product_id'          => - 1,
						'quantity'            => 1,
						'name'                => 'Discount',
						'unit_price'          => $this->format_price( - $discount ),
						'price_currency_code' => $currency_code,
					);
				}

				// Shipping costs.
				// Try the new getter first, that was introduced in WooCommerce 2.1.0 and replaced the old getter.
				if ( method_exists( $order, 'get_total_shipping' ) ) {
					$shipping = $order->get_total_shipping();
				} else {
					$shipping = $order->get_shipping();
				}
				if ( 0 < $shipping ) {
					// Shipping tax needs to be added manually, as there are no getters for the calculated value.
					if ( 0 < ( $shipping_tax = $order->get_shipping_tax() ) ) {
						// Calculating monetary values as floats is not the correct way to do it, due to the lack
						// of precision in floating point. We do it here anyway because WooCommerce does it internally,
						// and changing it here will only cause inconsistencies.
						$shipping = (float) $shipping + (float) $shipping_tax;
					}
					$data['line_items'][] = array(
						'product_id'          => - 1,
						'quantity'            => 1,
						'name'                => 'Shipping',
						'unit_price'          => $this->format_price( $shipping ),
						'price_currency_code' => $currency_code,
					);
				}

				// There might be some additional fees for the order, so we just add them all to the tagging.
				$fees = $order->get_fees();
				if ( is_array( $fees ) ) {
					foreach ( $fees as $fee ) {
						// The tax needs to be added manually, as there are no getters for the calculated value.
						// Calculating monetary values as floats is not the correct way to do it, due to the lack
						// of precision in floating point. We do it here anyway because WooCommerce does it internally,
						// and changing it here will only cause inconsistencies.
						$unit_price = (float) $fee['line_total'] + (float) $fee['line_tax'];
						if ( 0 < $unit_price ) {
							$data['line_items'][] = array(
								'product_id'          => - 1,
								'quantity'            => 1,
								'name'                => isset( $fee['name'] ) ? $fee['name'] : 'Fee',
								'unit_price'          => $this->format_price( $unit_price ),
								'price_currency_code' => $currency_code,
							);
						}
					}
				}

				$this->render( self::TEMPLATE_ORDER_TAGGING, array( 'order' => $data ), self::PAGE_TYPE_ORDER );
			}
		}
	}

	/**
	 * Hook callback function for outputting the upsy elements at the bottom of the product page.
	 *
	 * @since 1.0.0
	 */
	public function add_product_page_bottom_elements() {
		if ( is_product() ) {
			$default_element_ids = array(
				self::ELEMENT_upsy_PAGE_PRODUCT_1,
				self::ELEMENT_upsy_PAGE_PRODUCT_2,
				self::ELEMENT_upsy_PAGE_PRODUCT_3,
			);
			$element_ids         = apply_filters( 'wcnt_add_product_page_bottom_elements', $default_element_ids );
			$this->renderElements( $element_ids );
		}
	}

	/**
	 * Hook callback function for outputting the upsy elements at the top of the category pages.
	 *
	 * @since 1.0.0
	 */
	public function add_category_page_top_elements() {
		if ( is_product_category() ) {
			$default_element_ids = array(
				self::ELEMENT_upsy_PAGE_CATEGORY_1,
			);
			$element_ids         = apply_filters( 'wcnt_add_category_page_top_elements', $default_element_ids );
			$this->renderElements( $element_ids );
		}
	}

	/**
	 * Hook callback function for outputting the upsy elements at the bottom of the category page.
	 *
	 * @since 1.0.0
	 */
	public function add_category_page_bottom_elements() {
		if ( is_product_category() ) {
			$default_element_ids = array(
				self::ELEMENT_upsy_PAGE_CATEGORY_2,
			);
			$element_ids         = apply_filters( 'wcnt_add_category_page_bottom_elements', $default_element_ids );
			$this->renderElements( $element_ids );
		}
	}

	/**
	 * Hook callback function for outputting the upsy elements at the bottom of the shopping cart page.
	 *
	 * @since 1.0.0
	 */
	public function add_cart_page_bottom_elements() {
		if ( is_cart() ) {
			$default_element_ids = array(
				self::ELEMENT_upsy_PAGE_CART_1,
				self::ELEMENT_upsy_PAGE_CART_2,
				self::ELEMENT_upsy_PAGE_CART_3,
			);
			$element_ids         = apply_filters( 'wcnt_add_cart_page_bottom_elements', $default_element_ids );
			$this->renderElements( $element_ids, self::PAGE_TYPE_CART );
		}
	}

	/**
	 * Hook callback function for outputting the upsy elements at the top of the search result page.
	 *
	 * @since 1.0.0
	 */
	public function add_search_page_top_elements() {
		if ( is_search() ) {
			$default_element_ids = array(
				self::ELEMENT_upsy_PAGE_SEARCH_1,
			);
			$element_ids         = apply_filters( 'wcnt_add_search_page_top_elements', $default_element_ids );
			$this->renderElements( $element_ids, self::PAGE_TYPE_SEARCH );
		}
	}

	/**
	 * Hook callback function for outputting the upsy elements at the bottom of the search result page.
	 *
	 * @since 1.0.0
	 */
	public function add_search_page_bottom_elements() {
		if ( is_search() ) {
			$default_element_ids = array(
				self::ELEMENT_upsy_PAGE_SEARCH_2,
			);
			$element_ids         = apply_filters( 'wcnt_add_search_page_bottom_elements', $default_element_ids );
			$this->renderElements( $element_ids );
		}
	}

	/**
	 * Hook callback function for outputting the upsy elements at the top of all pages.
	 *
	 * @since 1.0.0
	 */
	public function add_page_top_elements() {
		$default_element_ids = array(
			self::ELEMENT_upsy_PAGE_TOP,
		);
		$element_ids         = apply_filters( 'wcnt_add_page_top_elements', $default_element_ids );
		$this->renderElements( $element_ids );
	}

	/**
	 * Hook callback function for outputting the upsy elements at the bottom of all pages.
	 *
	 * @since 1.0.0
	 */
	public function add_page_bottom_elements() {
		$default_element_ids = array(
			self::ELEMENT_upsy_PAGE_BOTTOM,
		);
		$element_ids = apply_filters( 'wcnt_add_page_bottom_elements', $default_element_ids );
		$this->renderElements( $element_ids );
	}

	/**
	 * Add top slots to home page
	 *
	 * @return array
	 */
	public function add_homepage_top_elements()
	{
		if (is_shop()) {
			$default_element_ids = array(
				self::ELEMENT_FRONTPAGE_upsy_1,
				self::ELEMENT_FRONTPAGE_upsy_2,
			);
			$element_ids = apply_filters('wcnt_add_page_top_elements', $default_element_ids);
			$this->renderElements( $element_ids, self::PAGE_TYPE_FRONT_PAGE );
		}
	}

	/**
	 * Add bottom slots to home page
	 *
	 * @return array
	 */
	public function add_homepage_bottom_elements()
	{
		if (is_shop()) {
			$default_element_ids = array(
				self::ELEMENT_FRONTPAGE_upsy_3,
				self::ELEMENT_FRONTPAGE_upsy_4,
			);
			$element_ids = apply_filters( 'wcnt_add_page_bottom_elements', $default_element_ids );
			$this->renderElements( $element_ids );
		}
	}

	/**
	 * Add slots to 404 page
	 *
	 * @return array
	 */
	public function add_notfoundpage_elements()
	{
		$default_element_ids = array(
			self::ELEMENT_NOTFOUND_upsy_1,
			self::ELEMENT_NOTFOUND_upsy_2,
			self::ELEMENT_NOTFOUND_upsy_3,
		);
		$this->renderElements( $default_element_ids, self::PAGE_TYPE_NOTFOUND );
	}

	/**
	 * Renders a template file.
	 *
	 * The file is expected to be located in the plugin "templates" directory.
	 *
	 * @since 1.0.0
	 * @param string $template The name of the template
	 * @param array $data The data to pass to the template file
	 */
	public function render( $template, $data = array(), $page_type=null ) {
		if ( is_array( $data ) ) {
			extract( $data );
		}
		$file = $template . '.php';
		require( $this->plugin_dir . 'templates/' . $file );
		if ( !empty( $page_type ) ) {
			require( $this->plugin_dir . 'templates/page-type.php');
		}
	}

	/**
	 * Renders upsy slots / elements
	 *
	 * @param $element_ids
	 * @param null $page_type
	 */
	public function renderElements( $element_ids = array(), $page_type = null ) {
		if ( is_array($element_ids) && count($element_ids) > 0 ) {
			$this->render( self::TEMPLATE_upsy_ELEMENTS, array( 'element_ids' => $element_ids ), $page_type );
		}
	}

	/**
	 * Load class file based on class name.
	 *
	 * The file are expected to be located in the plugin "classes" directory.
	 *
	 * @since 1.0.0
	 * @param string $class_name The name of the class to load
	 */
	public function load_class( $class_name = '' ) {
		$file = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';
		require_once( $this->plugin_dir . 'classes/' . $file );
	}

	/**
	 * Registers widget for showing upsy elements in the shop sidebars.
	 *
	 * @since 1.0.0
	 */
	public function register_widgets() {
		/*
		$this->load_class( 'WP_Widget_upsy_Element' );
		register_widget( 'WP_Widget_upsy_Element' );
		*/
	}

	/**
	 * Get customer data for tagging for the WP_User object.
	 *
	 * @since 1.0.0
	 * @param WP_User $user The user for which to get the data
	 * @return array
	 */
	protected function get_customer_data( $user ) {
		$customer = array();

		if ( $user instanceof WP_User ) {
			$customer['first_name'] = ! empty( $user->user_firstname ) ? $user->user_firstname : '';

			if ( ! empty( $user->user_lastname ) ) {
				$customer['last_name'] = $user->user_lastname;
			} elseif ( ! empty( $user->user_login ) ) {
				// Fallback on the users login name if there is no last name.
				$customer['last_name'] = $user->user_login;
			} else {
				$customer['last_name'] = '';
			}

			$customer['email'] = ! empty( $user->user_email ) ? $user->user_email : '';
		}

		return $customer;
	}

	/**
	 * Gets the list price including tax for the given product.
	 *
	 * @since 1.0.0
	 * @param WC_Product $product The product object
	 * @return string|int
	 */
	protected function get_list_price_including_tax( $product ) {
		if ( $product instanceof WC_Product_Variable ) {
			$list_price = $product->get_variation_regular_price('min', true);
		} elseif ( $product instanceof WC_Product ) {
			if ( $product->is_on_sale() && isset( $product->regular_price ) ) {
				// If the product is on sale, then we create a new instance of
				// it to avoid breaking things when we assign it a new price attribute.
				// We do this in order to use the internal WooCommerce tax calculations.

				/** @var $new_product WC_Product */
				$new_product = get_product( $product->id );
				$new_product->set_price( $product->regular_price );
				$list_price = $new_product->get_price_including_tax();
			} else {
				$list_price = $product->get_price_including_tax();
			}
		} else {
			$list_price = 0;
		}

		return $list_price;
	}

	/**
	 * Formats price into upsy format, e.g. 1000.99.
	 *
	 * @since 1.0.0
	 * @param string|int|float $price The price to format
	 * @return string
	 */
	protected function format_price( $price ) {
		return number_format( $price, 2, '.', '' );
	}

	/**
	 * Builds a category path string for given term including all its parents.
	 *
	 * @since 1.0.0
	 * @param object $term The term object to build the category path string from
	 * @return string
	 */
	protected function build_category_path( $term ) {
		$category_path = '';

		if ( is_object( $term ) && ! empty( $term->term_id ) ) {
			$terms   = $this->get_parent_terms( $term );
			$terms[] = $term;

			$term_names = array();
			foreach ( $terms as $term ) {
				$term_names[] = $term->name;
			}

			if ( ! empty( $term_names ) ) {
				$category_path = DIRECTORY_SEPARATOR . implode( DIRECTORY_SEPARATOR, $term_names );
			}
		}

		return $category_path;
	}

	/**
	 * Get a list of all parent terms for given term.
	 *
	 * The list is sorted starting from the most distant parent.
	 *
	 * @since 1.0.0
	 * @param object $term     The term object to find parent terms for
	 * @param string $taxonomy The taxonomy type for the terms
	 * @return array
	 */
	protected function get_parent_terms( $term, $taxonomy = 'product_cat' ) {
		if ( empty( $term->parent ) ) {
			return array();
		}

		$parent = get_term( $term->parent, $taxonomy );

		if ( is_wp_error( $parent ) ) {
			return array();
		}

		$parents = array( $parent );

		if ( $parent->parent && ( $parent->parent !== $parent->term_id ) ) {
			$parents = array_merge( $parents, $this->get_parent_terms( $parent, $taxonomy ) );
		}

		return array_reverse( $parents );
	}

	/**
	 * Initializes the plugin admin part.
	 *
	 * Adds a new integration into the WooCommerce settings structure.
	 *
	 * @since 1.0.0
	 */
	protected function init_admin() {
		//$this->load_class( 'WC_Integration_upsy_Tagging' );
		//add_filter( 'woocommerce_integrations', array( 'WC_Integration_upsy_Tagging', 'add_integration' ) );
	}

	/**
	 * Initializes the plugin frontend part.
	 *
	 * Adds all hooks needed by the plugin in the frontend.
	 *
	 * @since 1.0.0
	 */
	protected function init_frontend() {
		$this->init_settings();
		//add_action( 'woocommerce_before_single_product', array( $this, 'tag_product' ), 20, 0 );




		//add_action( 'wp_head', array( $this, 'add_upsy_js_stub' ), 10, 0 );
		//add_action( 'wp_head', array( $this, 'add_upsy_js' ), 11, 0 );


		add_action( 'wp_footer', array( $this, 'load_upsy_customer_script' ), 11, 0 );


		add_action( 'loop_start', array( $this, 'tag_page' ), 20, 0 );
		//add_action( 'wp_head', array( $this, 'tag_page_cart' ), 20, 0 );
		//add_action( 'wp_head', array( $this, 'tag_page_notfound' ), 20, 0 );
		//add_action( 'wp_head', array( $this, 'order' ), 20, 0 );



		add_action( 'woocommerce_before_single_product', array( $this, 'tag_product' ), 20, 0 );
		add_action( 'woocommerce_before_main_content', array( $this, 'tag_category' ), 30, 0 );
		add_action( 'woocommerce_thankyou', array( $this, 'tag_order' ), 10, 1 );
		add_action( 'wp_footer', array( $this, 'tag_customer' ), 10, 0 );
		add_action( 'wp_footer', array( $this, 'tag_cart' ), 10, 0 );

		//add_action( 'wp_footer', array( $this, 'add_upseller_js' ), 12, 0 );

		if( (bool)$this->use_default_elements ) {
			add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_product_page_bottom_elements' ), 30, 0 );
			add_action( 'woocommerce_before_main_content', array( $this, 'add_category_page_top_elements' ), 40, 0 );
			add_action( 'woocommerce_after_main_content', array( $this, 'add_category_page_bottom_elements' ), 5, 0 );
			add_action( 'woocommerce_after_cart', array( $this, 'add_cart_page_bottom_elements' ), 10, 0 );
			add_action( 'woocommerce_before_main_content', array( $this, 'add_search_page_top_elements' ), 30, 0 );
			add_action( 'woocommerce_after_main_content', array( $this, 'add_search_page_bottom_elements' ), 5, 0 );
			add_action( 'woocommerce_before_main_content', array( $this, 'add_homepage_top_elements' ), 30, 0 );
			add_action( 'woocommerce_after_main_content', array( $this, 'add_homepage_bottom_elements' ), 5, 0 );
			// Custom hooks
			add_action( 'wcnt_before_search_result', array( $this, 'add_search_page_top_elements' ), 10, 0 );
			add_action( 'wcnt_after_search_result', array( $this, 'add_search_page_bottom_elements' ), 10, 0 );
			add_action( 'wcnt_notfound_content', array( $this, 'add_notfoundpage_elements' ), 10, 0 );
			add_action( 'wcnt_before_main_content', array( $this, 'add_page_top_elements' ), 10, 0 );
			add_action( 'wcnt_after_main_content', array( $this, 'add_page_bottom_elements' ), 10, 0 );
		}

		
	}


	function load_upsy_customer_script() {
		$upsy_id = get_option('upsy_settings_customer_id');
		?>
			<script>
			
				(function () {
				var e = function (c, b, d) {
					var a = document.createElement("script");
					a.src = c;
					a.onload = b;
					a.onreadystatechange = b;
					d.appendChild(a)
				}, f = function () {
					upsy_sdk.init(<?php echo $upsy_id  ?>);
				};
				e("https://d6d3bk3t0wue8.cloudfront.net/static/static/upsy.js", f, document.body)
				})()
			</script>
   		<?php
	}
	

	/**
	 *
	 * Loads the plugin settings from WP options table.
	 *
	 * Applies the settings as member variables to $this.
	 *
	 * @since 1.0.0
	 */
	protected function init_settings() {
		$settings = get_option( 'woocommerce_upsy_tagging_settings' );
		if( is_array( $settings ) ) {
			foreach ( $settings as $key => $value ) {
				if( isset( $this->$key ) ) {
					$this->$key = $value;
				}
			}
		}
	}

	/**
	 * Checks plugin dependencies.
	 *
	 * Mainly that the WordPress and WooCommerce versions are equal to or greater than
	 * the defined minimums.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	protected function check_dependencies() {
		global $wp_version;

		$title = sprintf( __( 'WooCommerce upsy Tagging %s not compatible.' ), self::VERSION );
		$error = '';
		$args  = array(
			'back_link' => true,
		);

		if ( version_compare( $wp_version, self::MIN_WP_VERSION, '<' ) ) {
			$error = sprintf(
				__( 'Looks like you\'re running an older version of WordPress, you need to be running at least
					WordPress %1$s to use WooCommerce upsy Tagging %2$s.' ),
				self::MIN_WP_VERSION,
				self::VERSION
			);
		}

		if ( ! defined( 'WOOCOMMERCE_VERSION' ) ) {
			$error = sprintf(
				__( 'Looks like you\'re not running any version of WooCommerce, you need to be running at least
					WooCommerce %1$s to use WooCommerce upsy Tagging %2$s.' ),
				self::MIN_WC_VERSION,
				self::VERSION
			);
		} else if ( version_compare( WOOCOMMERCE_VERSION, self::MIN_WC_VERSION, '<' ) ) {
			$error = sprintf(
				__( 'Looks like you\'re running an older version of WooCommerce, you need to be running at least
					WooCommerce %1$s to use WooCommerce upsy Tagging %2$s.' ),
				self::MIN_WC_VERSION,
				self::VERSION
			);
		}

		if ( ! empty( $error ) ) {
			deactivate_plugins( $this->plugin_name );
			wp_die( $error, $title, $args );
			return false;
		}

		return true;
	}

	/**
	 * Returns the arguments of a method
	 *
	 * @param $class
	 * @param $func_name
	 * @return array
	 */
	public static function get_method_args($class, $func_name) {
		$reflection = new ReflectionMethod($class, $func_name);
		$result = array();
		foreach ($reflection->getParameters() as $param) {
			$result[] = $param->name;
		}
		return $result;
	}

	/**
	 * Add slots to 404 page
	 *
	 * @return array
	 */
	public function add_notfound_elements() {
		if ( is_404() ) {
			$default_element_ids = array(
				self::ELEMENT_NOTFOUND_upsy_1,
				self::ELEMENT_NOTFOUND_upsy_2,
				self::ELEMENT_NOTFOUND_upsy_3,
			);
			$this->renderElements( $default_element_ids, self::PAGE_TYPE_FRONT_PAGE );
		}
	}

	/**
	 * Returns the url for upsy script
	 *
	 * @param string $account_id
	 * @return string
	 */
	public static function get_upsy_embed_src($account_id) {
		return sprintf(
			'//%s/include/%s',
			self::get_upsy_server_address(),
			$account_id
		);
	}

	/**
	 * Returns the server address for upsy
	 *
	 * @return string
	 */
	public static function get_upsy_server_address() {
		$server_address = self::DEFAULT_upsy_SERVER_ADDRESS;
		return  $server_address;
	}

	/**
	 * Hook callback function for outputting the upsy include javascript.
	 *
	 * @since 1.1.0
	 */
	public function add_upsy_js() {
		if ( ! empty( $this->account_id ) ) {
			$this->render( 'upsy-js', array( 'server' => self::get_upsy_server_address(), 'account' => $this->account_id ) );
		}
	}

	/**
	 * Hook callback function for outputting the upsy "javascript stub".
	 *
	 * @since 1.1.0
	 */
	public function add_upsy_js_stub() {
		if ( ! empty( $this->account_id ) ) {
			$this->render( 'upsy-js-stub');
		}
	}

	public function add_upseller_js() {
		if (false) {
			$this->render( 'upseller-js');
		}
	}

}

add_action( 'plugins_loaded', array( WC_upsy_Tagging::get_instance(), 'init' ) );
