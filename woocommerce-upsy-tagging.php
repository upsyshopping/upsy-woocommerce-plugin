<?php
/*
	Plugin Name: UPSY for WooCommerce
	Plugin URI: https://upsyshopping.com
	Description: Enables UPSY for WooCommerce.
	Author: Upsy Company Oy
	Text Domain: upsy-for-wooCommerce
	Version: 3.4.0
	License: GPL3
*/

/**
 * Main plugin class.
 *
 * @package WooCommerce Upsy Tagging
 * @since   1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;
class WC_upsy_Tagging
{
	/**
	 * Plugin version.
	 * Used for dependency checks.
	 *
	 * @since 1.0.0
	 */
	const VERSION = '3.4.0';
	
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

	/*
	 * Woocommerce Bundle product type
	 */
	const PRODUCT_TYPE_BUNDLE = 'bundle';
	
	/*
	 * Woocommerce Event type
	 */
	
	const PURCHASE_EVENT_TYPE= 'purchase'; 

	/*
	 * Random characters for generating random strings.
	 */

	const RANDOM_CHARACTERS = 'abcdefghijklmnopqrstuvwxyz1234567890';


	/**
	 * upsy default js URLs - use plugin settings to override these
	 */
	const UPSYJS_URL_PRODUCTION = 'https://upsy-widget.upsyshopping.com/static/upsy.js';
	const UPSYJS_URL_STAGING = 'https://upsy-widget-staging.upsyshopping.com/static/upsy.js';
	const UPSYJS_URL_LOCAL = 'http://localhost:3000/static/upsy.js';
	const UPSYJS_EVENT_URL_LOCAL = 'http://localhost:3000/';
	const UPSYJS_PLUGIN_VERSION_DETECTOR_URL = 'https://raw.githubusercontent.com/upsyshopping/upsy-woocommerce-plugin/release/manifest.json';	
	const UPSYJS_EVENT_URL_PRODUCTION = 'https://upsy-backend-prod.azurewebsites.net';
	const UPSYJS_ANALYTICS_KEY_PRODUCTION = 'XaQ8cBzy6MqfL2tARCTaPnnaNxJQaL9zYsTUIhLm43ztWwXgRvL0Mw==';
	const UPSYJS_EVENT_URL_STAGING = 'https://upsy-backend-dev.azurewebsites.net';
    const UPSYJS_ANALYTICS_KEY_STAGING = 'oYDWXeqNpuDQeNrWq9AKwHZKsNPN7WTApOKanRXuQ3kEOBds2YQQvg==';	
	
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
		self::PRODUCT_TYPE_BUNDLE,
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
	 * @return WC_upsy_Tagging|null
	 * @since 1.0.0
	 */

	protected $is_discount_plugin_found = false;

	/**
	 * A variable to track we should hide upsy widget or not.
	 *
	 * @since 1.0.0
	 * @var boolean
	 */



	public static function get_instance()
	{
		if (null === self::$instance) {
			self::$instance = new WC_upsy_Tagging();
		}
		
		return self::$instance;
	}
	
	/**
	 * Constructor.
	 *
	 * Plugin uses Singleton pattern, hence the constructor is private.
	 *
	 * @return WC_upsy_Tagging
	 * @since 1.0.0
	 */
	private function __construct()
	{
		$this->plugin_dir = plugin_dir_path(__FILE__);
		$this->plugin_url = plugin_dir_url(__FILE__);
		$this->plugin_name = plugin_basename(__FILE__);
		$this->should_upsy_widget_hide();
		
		register_activation_hook($this->plugin_name, array($this, 'activate'));
		register_deactivation_hook($this->plugin_name, array($this, 'deactivate'));
		// The uninstall hook callback needs to be a static class method or function.
		register_uninstall_hook($this->plugin_name, array(__CLASS__, 'uninstall'));
		// Add Upsy Menu and submenu to Sidebar
		add_action('admin_menu', array($this, 'add_menu'), 9);
		add_action('admin_init', array($this, 'populate_setting_fields'));
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts_callback'));
		
	}
	
	/**
	 * Adding Plugin to Sidebar.
	 *
	 * Handles the backend settings page integration for upsy.
	 *
	 * @since 1.0.0
	 */
	function admin_enqueue_scripts_callback($hook){
		wp_enqueue_style('upsy-plugin-style', plugin_dir_url( __FILE__ ). "assets/css/upsy-plugin-style.css", array(), self::VERSION);
		wp_enqueue_script( 'upsy_plugin_main_js', plugin_dir_url( __FILE__ ). "assets/js/upsy_plugin_main.js" , array('jquery'), self::VERSION);
		wp_localize_script('upsy_plugin_main_js', 'upsy_wc_auth', array('ajax_url' => admin_url('admin-ajax.php'), 'host' => get_site_url(), 'environment' => wp_get_environment_type(), 'return_url' => esc_url(menu_page_url($this->get_plugin_name(), false))));
		}

	function add_menu()
	{
		//add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
		add_menu_page($this->plugin_name, $this->plugin_display_name, 'administrator', $this->plugin_name, array($this, 'upsy_customer_settings'), 'dashicons-chart-area', 26);
		
		//add_submenu_page( '$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		//add_submenu_page($this->plugin_name, $this->plugin_display_name, 'Settings', 'administrator', 'upsy-settings', array($this, 'upsy_customer_settings'));
	}

	public function should_upsy_widget_hide() {

		$is_logged_in_user = $this->check_logged_in_user();

		if(!$is_logged_in_user){
			return;
		}

		$this->check_wc_discount_related_plugins('Woo Discount Rules', $is_logged_in_user);		
	}
	

	public function check_logged_in_user() {

		require_once ABSPATH . 'wp-includes/pluggable.php';

		$logged_in_user = is_user_logged_in();

		return $logged_in_user;

	}

	public function check_wc_discount_related_plugins($target_plugin_name='Woo Discount Rules', $logged_in_user=false) {
		if ( ! function_exists( 'get_plugins' ) ) {
       		 require_once ABSPATH . 'wp-admin/includes/plugin.php';
    	}

		$plugins = get_plugins();

		foreach ( $plugins as $plugin_path => $plugin_info ) {
			$plugin_name = $plugin_info['Name'];
			if($plugin_name	== $target_plugin_name && is_plugin_active($plugin_path) && $logged_in_user){
				$this->is_discount_plugin_found = true;
				return;
			}
		}
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
		$page = sanitize_text_field($_GET['page']);
		$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
		$is_wc_auth_redirect = isset($page) && $page == $this->plugin_name && isset($_GET['user_id']);
		add_action('admin_notices', array($this, 'upsy_settings_messages'));
		// set this var to be used in the settings-display view
		if (isset($_GET['error_message'])) {
			do_action('admin_notices', ['type' => 'error']);
		}else if($is_wc_auth_redirect && $_GET['success'] == '0'){
			do_action('admin_notices', ['type' => 'error', 'message' => 'error']);
		}else if($is_wc_auth_redirect && $_GET['success'] == '1'){
			if($_GET['user_id']){
				update_option('upsy_settings_customer_id', sanitize_text_field($_GET['user_id']));
			}
			update_option('isUpsyWcAuthSuccess', '1');
			do_action('admin_notices', ['type' => 'success', 'message' => 'Store successfully authorize - welcome to using Upsy! Your Upsy installation is now being progressed by the team, we will get back to you when it is ready to be used in your store']);
		}
		$this->render(self::TEMPLATE_CUSTOMER_SETTINGS, array('plugin_dir_url'=> plugin_dir_url(__FILE__)));
		
	}
	
	public function upsy_settings_messages($data)
	{
		$err_code = esc_attr('upsy_settings_customer_id');
		$setting_field = 'upsy_settings_customer_id';
		$type = is_array($data) && $data['type'] ? $data['type'] : 'error';
		$message = is_array($data) && $data['message'] ? __('Upsy WooCommerce Authentication Failed.Please try again', 'upsy-for-wooCommerce') : __('There is an error occurred. Please try again.', 'upsy-for-wooCommerce');
		add_settings_error(
			$setting_field,
			$err_code,
			$message,
			$type
		);
	}
	
	public function upsy_display_customer_settings()
	{
		
		echo '<p class="form-title">Enter your upsy customer id.</p>';
	}
	
	public function populate_setting_fields()
	{
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
			array($this, 'upsy_display_customer_settings'),
			// Page on which to add this section of options
			'upsy_customer_general_settings'
		);
		
		unset($args);
		$customer_id_args = array(
			'type' => 'input',
			'subtype' => 'text',
			'id' => 'upsy_settings_customer_id',
			'name' => 'upsy_settings_customer_id',
			'required' => 'true',
			'get_options_list' => '',
			'value_type' => 'normal',
			'wp_data' => 'option'
		);
		
		add_settings_field(
			'upsy_settings_customer_id',
			'Upsy Customer ID',
			array($this, 'upsy_customer_settings_field'),
			'upsy_customer_general_settings',
			'upsy_general_section',
			$customer_id_args
		);

		register_setting(
			'upsy_customer_general_settings',
			'upsy_settings_customer_id'
		);

		if (wp_get_environment_type() !== "production") {

			//env
			$environment_args = array(
				'type' => 'input',
				'subtype' => 'text',
				'id' => 'upsy_settings_environment',
				'name' => 'upsy_settings_environment',
				'required' => 'false',
				'get_options_list' => '',
				'value_type' => 'normal',
				'wp_data' => 'option'
			);
	
			add_settings_field(
				'upsy_settings_environment',
				'Upsy environment',
				array($this, 'upsy_customer_settings_field'),
				'upsy_customer_general_settings',
				'upsy_general_section',
				$environment_args
			);
			
			register_setting(
				'upsy_customer_general_settings',
				'upsy_settings_environment'
			);

			//jsurl
			$jsurl_args = array(
				'type' => 'input',
				'subtype' => 'text',
				'id' => 'upsy_settings_jsurl',
				'name' => 'upsy_settings_jsurl',
				'required' => 'false',
				'get_options_list' => '',
				'value_type' => 'normal',
				'wp_data' => 'option'
			);
	
			add_settings_field(
				'upsy_settings_jsurl',
				'Override Upsy js-url',
				array($this, 'upsy_customer_settings_field'),
				'upsy_customer_general_settings',
				'upsy_general_section',
				$jsurl_args
			);
			
			register_setting(
				'upsy_customer_general_settings',
				'upsy_settings_jsurl'
			);

		}
		
	}
	
	
	public function upsy_customer_settings_field($args)
	{
		
		if ($args['wp_data'] == 'option') {
			$wp_data_value = get_option($args['name']);
		} elseif ($args['wp_data'] == 'post_meta') {
			$wp_data_value = get_post_meta($args['post_id'], $args['name'], true);
		}
		
		//only input=text for now

		$value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;

		if($args['id'] == 'upsy_settings_environment'){
			//if wp-config var set

			if($value == ''){
				$value = wp_get_environment_type();
			}
			//disabled for now to work with URL
			echo '<input type="' . esc_attr($args['subtype']) . '" id="' . esc_attr($args['id']) . '"' . ' name="' . esc_attr($args['name']) . '" size="40" value="' . esc_attr($value) . '" disabled="disabled" />';
			echo "<small>Variable defined at wp-config [production/staging/local]. Example: define('WP_ENVIRONMENT_TYPE', 'staging')<small/>";
		}else if($args['id'] == 'upsy_settings_jsurl'){

			/*
			if($value == ''){
				if(wp_get_environment_type() == 'local'){
					$value = self::UPSYJS_URL_LOCAL;
				}else if(wp_get_environment_type() == 'staging'){
					$value = self::UPSYJS_URL_STAGING;
				}else{
					//production
					$value = self::UPSYJS_URL_PRODUCTION;					
				}
			}
			*/
			echo '<input type="' . esc_attr($args['subtype']) . '" id="' . esc_attr($args['id']) . '"' . ' name="' . esc_attr($args['name']) . '" size="40" value="' . esc_attr($value) . '"/>';
			if(wp_get_environment_type() == 'local'){
				echo "<small>Default for this: " . esc_url(self::UPSYJS_URL_LOCAL) . "<small/>";
			}else if(wp_get_environment_type() == 'staging'){
				echo "<small>Default for this: " . esc_url(self::UPSYJS_URL_STAGING) . "<small/>";
			} else {
				echo "<small>Default for this: " . esc_url(self::UPSYJS_URL_PRODUCTION) . "<small/>";
			}

		} else {
			//normal input
			$disabled = !empty($value) ? 'disabled':'';
			echo sprintf("<input %s type='%s' id='%s' name='%s' size='40' value='%s' />",
						esc_attr($disabled),
						esc_attr($args['subtype']),
						esc_attr($args['id']),
						esc_attr($args['name']),
						esc_attr($value)
					);

			echo '<small>No special characters and space are allowed<small/>';
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
	public function init()
	{
		if (is_admin()) {
			$this->init_admin();
		} else {
			$this->init_frontend();
		}
		
		add_action('widgets_init', array($this, 'register_widgets'));
	}
	
	/**
	 * Hook callback function for activating the plugin.
	 *
	 * Checks WP and WC dependencies for plugin compatibility.
	 * Creates the Top Sellers page or only publishes it if it already exists.
	 *
	 * @since 1.0.0
	 */
	public function activate()
	{
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
	public function deactivate()
	{
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
	public static function uninstall()
	{
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
		
		delete_option('woocommerce_upsy_tagging_settings');
		//delete_option( 'woocommerce_upsy_tagging_top_sellers_page_id' );
		//delete_option( 'widget_upsy_element' );
	}
	
	/**
	 * Getter for the plugin base name.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}
	
	/**
	 * Hook callback function for tagging products.
	 *
	 * Gathers necessary data and renders the product tagging template ( templates/product-tagging.php ).
	 *
	 * @since 1.0.0
	 */
	public function tag_product()
	{
		if (is_product()) {
			/** @var $product WC_Product */
			global $product;
			
			if ($product instanceof WC_Product && $product->is_type(self::$product_type_whitelist)) {
				$data = array();
				$product_id = (int)$product->id;
				
				$data['url'] = (string)get_permalink();
				$data['product_id'] = $product_id;
				$data['name'] = (string)$product->get_title();
				
				$image_url = wp_get_attachment_url(get_post_thumbnail_id());
				if (!empty($image_url)) {
					$data['image_url'] = (string)$image_url;
				}
				
				$data['price'] = $this->format_price($product->get_price_including_tax());
				$data['price_currency_code'] = get_woocommerce_currency();
				$data['availability'] = $product->is_in_stock() ? self::PRODUCT_IN_STOCK : self::PRODUCT_OUT_OF_STOCK;
				
				$data['categories'] = array();
				$terms = get_the_terms($product->post->ID, 'product_cat');
				if (is_array($terms)) {
					foreach ($terms as $term) {
						$category_path = $this->build_category_path($term);
						if (!empty($category_path)) {
							$data['categories'][] = $category_path;
						}
					}
				}
				
				$data['description'] = (string)$product->post->post_content;
				$data['list_price'] = $this->format_price($this->get_list_price_including_tax($product));
				
				if (!empty($data)) {
					$this->render(self::TEMPLATE_PRODUCT_TAGGING, array('product' => $data), self::PAGE_TYPE_PRODUCT);
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
	public function tag_category()
	{
		if (is_product_category()) {
			$term = get_term_by('slug', esc_attr(get_query_var('product_cat')), 'product_cat');
			$category_path = $this->build_category_path($term);
			
			$category = get_term_by('slug', esc_attr(get_query_var('product_cat')), 'product_cat');
			$cat_id = $category->term_id;
			
			$category_id = $cat_id;
			if (!empty($category_path)) {
				$this->render(self::TEMPLATE_CATEGORY_TAGGING, array('category_path' => $category_path, 'category_id' => $category_id), self::PAGE_TYPE_CATEGORY);
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
	
	public function tag_page()
	{
		
		if (is_front_page()) {
			//echo 'FRONT';
			$page_type = 'front';
			if (!empty($page_type)) {
				require($this->plugin_dir . 'templates/page-type.php');
			}
		} else if (is_product()) {
			//echo 'PRODUCT: DONOTHING';
		} else if (is_product_category()) {
			//echo 'CAT: DONOTHING';
		} else if (is_cart()) {
			//echo 'CART';
			$page_type = 'cart';
			if (!empty($page_type)) {
				require($this->plugin_dir . 'templates/page-type.php');
			}
		} else if (is_checkout()) {
			//echo 'ORDER';
			$page_type = 'checkout'; //'order';
			if (!empty($page_type)) {
				require($this->plugin_dir . 'templates/page-type.php');
			}
			
		} else {
			//echo 'NOTFOUND';
			$page_type = 'generic'; //'notfound';
			if (!empty($page_type)) {
				require($this->plugin_dir . 'templates/page-type.php');
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
	public function tag_customer()
	{
		if (is_user_logged_in()) {
			$user = wp_get_current_user();
			$customer = $this->get_customer_data($user);
			if (!empty($customer)) {
				$this->render(self::TEMPLATE_CUSTOMER_TAGGING, array('customer' => $customer));
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
	public function tag_cart()
	{
		/** @var $woocommerce Woocommerce */
		global $woocommerce;
		
		if ($woocommerce->cart instanceof WC_Cart && 0 < count($woocommerce->cart->get_cart())) {
			$cart_items = $woocommerce->cart->get_cart();
			$currency_code = get_woocommerce_currency();
			$line_items = array();
			
			foreach ($cart_items as $cart_item) {
				if (isset($cart_item['data']) && $cart_item['data'] instanceof WC_Product) {
					/** @var $product WC_Product */
					$product = $cart_item['data'];
					$line_item = array(
						'product_id' => (int)$cart_item['product_id'],
						'quantity' => (int)$cart_item['quantity'],
						'name' => (string)$product->get_title(),
						'unit_price' => $this->format_price($product->get_price_including_tax()),
						'price_currency_code' => $currency_code,
					);
					
					$line_items[] = $line_item;
				}
			}
			
			if (!empty($line_items)) {
				$this->render(self::TEMPLATE_CART_TAGGING, array('line_items' => $line_items));
			}
		}
	}
	
	/**
	 * Hook callback function for tagging successful orders.
	 *
	 * Gathers necessary data and renders the order tagging template ( templates/order-tagging.php ).
	 *
	 * @param int $order_id The id of the placed order
	 * @since 1.0.0
	 */
	public function tag_order($order_id)
	{
		if (is_numeric($order_id) && 0 < $order_id) {
			$order = new WC_Order($order_id);
			
			$buyer = array(
				'first_name' => $order->billing_first_name,
				'last_name' => $order->billing_last_name,
				'email' => $order->billing_email,
			);
			
			$currency_code = get_woocommerce_currency();
			
			$data = array(
				'order_number' => $order->id,
				'buyer' => $buyer,
				'line_items' => array(),
			);
			
			foreach ((array)$order->get_items() as $item) {
				$line_item = array(
					'product_id' => (int)$item['product_id'],
					'quantity' => (int)$item['qty'],
					'name' => (string)$item['name'],
					'unit_price' => $this->format_price($order->get_item_total($item, true)),
					'price_currency_code' => $currency_code,
				);
				
				$data['line_items'][] = $line_item;
			}
			
			// Add special line items for discounts, shipping and "fees".
			if (!empty($data['line_items'])) {
				// All discounts applied to the order.
				$discount = $order->get_total_discount();
				if (0 < $discount) {
					$data['line_items'][] = array(
						'product_id' => -1,
						'quantity' => 1,
						'name' => 'Discount',
						'unit_price' => $this->format_price(-$discount),
						'price_currency_code' => $currency_code,
					);
				}
				
				// Shipping costs.
				// Try the new getter first, that was introduced in WooCommerce 2.1.0 and replaced the old getter.
				if (method_exists($order, 'get_total_shipping')) {
					$shipping = $order->get_total_shipping();
				} else {
					$shipping = $order->get_shipping();
				}
				if (0 < $shipping) {
					// Shipping tax needs to be added manually, as there are no getters for the calculated value.
					if (0 < ($shipping_tax = $order->get_shipping_tax())) {
						// Calculating monetary values as floats is not the correct way to do it, due to the lack
						// of precision in floating point. We do it here anyway because WooCommerce does it internally,
						// and changing it here will only cause inconsistencies.
						$shipping = (float)$shipping + (float)$shipping_tax;
					}
					$data['line_items'][] = array(
						'product_id' => -1,
						'quantity' => 1,
						'name' => 'Shipping',
						'unit_price' => $this->format_price($shipping),
						'price_currency_code' => $currency_code,
					);
				}
				
				// There might be some additional fees for the order, so we just add them all to the tagging.
				$fees = $order->get_fees();
				if (is_array($fees)) {
					foreach ($fees as $fee) {
						// The tax needs to be added manually, as there are no getters for the calculated value.
						// Calculating monetary values as floats is not the correct way to do it, due to the lack
						// of precision in floating point. We do it here anyway because WooCommerce does it internally,
						// and changing it here will only cause inconsistencies.
						$unit_price = (float)$fee['line_total'] + (float)$fee['line_tax'];
						if (0 < $unit_price) {
							$data['line_items'][] = array(
								'product_id' => -1,
								'quantity' => 1,
								'name' => isset($fee['name']) ? $fee['name'] : 'Fee',
								'unit_price' => $this->format_price($unit_price),
								'price_currency_code' => $currency_code,
							);
						}
					}
				}
				
				$this->render(self::TEMPLATE_ORDER_TAGGING, array('order' => $data), self::PAGE_TYPE_ORDER);
			}
		}
	}
	
	/**
	 * Hook callback function for outputting the upsy elements at the bottom of the product page.
	 *
	 * @since 1.0.0
	 */
	public function add_product_page_bottom_elements()
	{
		if (is_product()) {
			$default_element_ids = array(
				self::ELEMENT_upsy_PAGE_PRODUCT_1,
				self::ELEMENT_upsy_PAGE_PRODUCT_2,
				self::ELEMENT_upsy_PAGE_PRODUCT_3,
			);
			$element_ids = apply_filters('wcnt_add_product_page_bottom_elements', $default_element_ids);
			$this->renderElements($element_ids);
		}
	}
	
	/**
	 * Hook callback function for outputting the upsy elements at the top of the category pages.
	 *
	 * @since 1.0.0
	 */
	public function add_category_page_top_elements()
	{
		if (is_product_category()) {
			$default_element_ids = array(
				self::ELEMENT_upsy_PAGE_CATEGORY_1,
			);
			$element_ids = apply_filters('wcnt_add_category_page_top_elements', $default_element_ids);
			$this->renderElements($element_ids);
		}
	}
	
	/**
	 * Hook callback function for outputting the upsy elements at the bottom of the category page.
	 *
	 * @since 1.0.0
	 */
	public function add_category_page_bottom_elements()
	{
		if (is_product_category()) {
			$default_element_ids = array(
				self::ELEMENT_upsy_PAGE_CATEGORY_2,
			);
			$element_ids = apply_filters('wcnt_add_category_page_bottom_elements', $default_element_ids);
			$this->renderElements($element_ids);
		}
	}
	
	/**
	 * Hook callback function for outputting the upsy elements at the bottom of the shopping cart page.
	 *
	 * @since 1.0.0
	 */
	public function add_cart_page_bottom_elements()
	{
		if (is_cart()) {
			$default_element_ids = array(
				self::ELEMENT_upsy_PAGE_CART_1,
				self::ELEMENT_upsy_PAGE_CART_2,
				self::ELEMENT_upsy_PAGE_CART_3,
			);
			$element_ids = apply_filters('wcnt_add_cart_page_bottom_elements', $default_element_ids);
			$this->renderElements($element_ids, self::PAGE_TYPE_CART);
		}
	}
	
	/**
	 * Hook callback function for outputting the upsy elements at the top of the search result page.
	 *
	 * @since 1.0.0
	 */
	public function add_search_page_top_elements()
	{
		if (is_search()) {
			$default_element_ids = array(
				self::ELEMENT_upsy_PAGE_SEARCH_1,
			);
			$element_ids = apply_filters('wcnt_add_search_page_top_elements', $default_element_ids);
			$this->renderElements($element_ids, self::PAGE_TYPE_SEARCH);
		}
	}
	
	/**
	 * Hook callback function for outputting the upsy elements at the bottom of the search result page.
	 *
	 * @since 1.0.0
	 */
	public function add_search_page_bottom_elements()
	{
		if (is_search()) {
			$default_element_ids = array(
				self::ELEMENT_upsy_PAGE_SEARCH_2,
			);
			$element_ids = apply_filters('wcnt_add_search_page_bottom_elements', $default_element_ids);
			$this->renderElements($element_ids);
		}
	}
	
	/**
	 * Hook callback function for outputting the upsy elements at the top of all pages.
	 *
	 * @since 1.0.0
	 */
	public function add_page_top_elements()
	{
		$default_element_ids = array(
			self::ELEMENT_upsy_PAGE_TOP,
		);
		$element_ids = apply_filters('wcnt_add_page_top_elements', $default_element_ids);
		$this->renderElements($element_ids);
	}
	
	/**
	 * Hook callback function for outputting the upsy elements at the bottom of all pages.
	 *
	 * @since 1.0.0
	 */
	public function add_page_bottom_elements()
	{
		$default_element_ids = array(
			self::ELEMENT_upsy_PAGE_BOTTOM,
		);
		$element_ids = apply_filters('wcnt_add_page_bottom_elements', $default_element_ids);
		$this->renderElements($element_ids);
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
			$this->renderElements($element_ids, self::PAGE_TYPE_FRONT_PAGE);
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
			$element_ids = apply_filters('wcnt_add_page_bottom_elements', $default_element_ids);
			$this->renderElements($element_ids);
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
		$this->renderElements($default_element_ids, self::PAGE_TYPE_NOTFOUND);
	}
	
	/**
	 * Renders a template file.
	 *
	 * The file is expected to be located in the plugin "templates" directory.
	 *
	 * @param string $template The name of the template
	 * @param array $data The data to pass to the template file
	 * @since 1.0.0
	 */
	public function render($template, $data = array(), $page_type = null)
	{
		if (is_array($data)) {
			extract($data);
		}
		$file = $template . '.php';
		require($this->plugin_dir . 'templates/' . $file);
		if (!empty($page_type)) {
			require($this->plugin_dir . 'templates/page-type.php');
		}
	}
	
	/**
	 * Renders upsy slots / elements
	 *
	 * @param $element_ids
	 * @param null $page_type
	 */
	public function renderElements($element_ids = array(), $page_type = null)
	{
		if (is_array($element_ids) && count($element_ids) > 0) {
			$this->render(self::TEMPLATE_upsy_ELEMENTS, array('element_ids' => $element_ids), $page_type);
		}
	}
	
	/**
	 * Load class file based on class name.
	 *
	 * The file are expected to be located in the plugin "classes" directory.
	 *
	 * @param string $class_name The name of the class to load
	 * @since 1.0.0
	 */
	public function load_class($class_name = '')
	{
		$file = 'class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
		require_once($this->plugin_dir . 'classes/' . $file);
	}
	
	/**
	 * Registers widget for showing upsy elements in the shop sidebars.
	 *
	 * @since 1.0.0
	 */
	public function register_widgets()
	{
		/*
		$this->load_class( 'WP_Widget_upsy_Element' );
		register_widget( 'WP_Widget_upsy_Element' );
		*/
	}
	
	/**
	 * Get customer data for tagging for the WP_User object.
	 *
	 * @param WP_User $user The user for which to get the data
	 * @return array
	 * @since 1.0.0
	 */
	protected function get_customer_data($user)
	{
		$customer = array();
		
		if ($user instanceof WP_User) {
			$customer['first_name'] = !empty($user->user_firstname) ? $user->user_firstname : '';
			
			if (!empty($user->user_lastname)) {
				$customer['last_name'] = $user->user_lastname;
			} elseif (!empty($user->user_login)) {
				// Fallback on the users login name if there is no last name.
				$customer['last_name'] = $user->user_login;
			} else {
				$customer['last_name'] = '';
			}
			
			$customer['email'] = !empty($user->user_email) ? $user->user_email : '';
		}
		
		return $customer;
	}
	
	/**
	 * Gets the list price including tax for the given product.
	 *
	 * @param WC_Product $product The product object
	 * @return string|int
	 * @since 1.0.0
	 */
	protected function get_list_price_including_tax($product)
	{
		if ($product instanceof WC_Product_Variable) {
			$list_price = $product->get_variation_regular_price('min', true);
		} elseif ($product instanceof WC_Product) {
			if ($product->is_on_sale() && isset($product->regular_price)) {
				// If the product is on sale, then we create a new instance of
				// it to avoid breaking things when we assign it a new price attribute.
				// We do this in order to use the internal WooCommerce tax calculations.
				
				/** @var $new_product WC_Product */
				$new_product = get_product($product->id);
				$new_product->set_price($product->regular_price);
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
	 * @param string|int|float $price The price to format
	 * @return string
	 * @since 1.0.0
	 */
	protected function format_price( $price ) {
		if (is_numeric($price)) {
			//normal float - do nothing	
		}else{
			//string or something else
			if(is_string($price) && $price != ''){
				//convert str to float
				$price = floatval($price);
			}else{
				//empty string or something else
				$price = 0;
			}
		}
		return number_format( $price, 2, '.', '' );	
	}


	/**
	 * Builds a category path string for given term including all its parents.
	 *
	 * @param object $term The term object to build the category path string from
	 * @return string
	 * @since 1.0.0
	 */
	protected function build_category_path($term)
	{
		$category_path = '';
		
		if (is_object($term) && !empty($term->term_id)) {
			$terms = $this->get_parent_terms($term);
			$terms[] = $term;
			
			$term_names = array();
			foreach ($terms as $term) {
				$term_names[] = $term->name;
			}
			
			if (!empty($term_names)) {
				$category_path = DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $term_names);
			}
		}
		
		return $category_path;
	}
	
	/**
	 * Get a list of all parent terms for given term.
	 *
	 * The list is sorted starting from the most distant parent.
	 *
	 * @param object $term The term object to find parent terms for
	 * @param string $taxonomy The taxonomy type for the terms
	 * @return array
	 * @since 1.0.0
	 */
	protected function get_parent_terms($term, $taxonomy = 'product_cat')
	{
		if (empty($term->parent)) {
			return array();
		}
		
		$parent = get_term($term->parent, $taxonomy);
		
		if (is_wp_error($parent)) {
			return array();
		}
		
		$parents = array($parent);
		
		if ($parent->parent && ($parent->parent !== $parent->term_id)) {
			$parents = array_merge($parents, $this->get_parent_terms($parent, $taxonomy));
		}
		
		return array_reverse($parents);
	}
	
	/**
	 * Initializes the plugin admin part.
	 *
	 * Adds a new integration into the WooCommerce settings structure.
	 *
	 * @since 1.0.0
	 */
	protected function init_admin()
	{
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
	protected function init_frontend()
	{
		if($this->is_discount_plugin_found) {
			return false;
		}

		$this->init_settings();
		//add_action( 'woocommerce_before_single_product', array( $this, 'tag_product' ), 20, 0 );
		
		
		add_action('wp_footer', array($this, 'load_upsy_customer_script'), 11, 0);
		
		
		add_action('wp_footer', array($this, 'tag_page'), 20, 0);
		//add_action( 'wp_head', array( $this, 'tag_page_cart' ), 20, 0 );
		//add_action( 'wp_head', array( $this, 'tag_page_notfound' ), 20, 0 );
		//add_action( 'wp_head', array( $this, 'order' ), 20, 0 );
		
		
		add_action('wp_footer', array($this, 'tag_product'), 20, 0);
		add_action('wp_footer', array($this, 'tag_category'), 30, 0);
		add_action('woocommerce_thankyou', array($this, 'tag_order'), 10, 1);
		add_action('wp_footer', array($this, 'tag_customer'), 10, 0);
		add_action('wp_footer', array($this, 'tag_cart'), 10, 0);
		

		
		if ((bool)$this->use_default_elements) {
			add_action('woocommerce_after_single_product_summary', array($this, 'add_product_page_bottom_elements'), 30, 0);
			add_action('woocommerce_before_main_content', array($this, 'add_category_page_top_elements'), 40, 0);
			add_action('woocommerce_after_main_content', array($this, 'add_category_page_bottom_elements'), 5, 0);
			add_action('woocommerce_after_cart', array($this, 'add_cart_page_bottom_elements'), 10, 0);
			add_action('woocommerce_before_main_content', array($this, 'add_search_page_top_elements'), 30, 0);
			add_action('woocommerce_after_main_content', array($this, 'add_search_page_bottom_elements'), 5, 0);
			add_action('woocommerce_before_main_content', array($this, 'add_homepage_top_elements'), 30, 0);
			add_action('woocommerce_after_main_content', array($this, 'add_homepage_bottom_elements'), 5, 0);
			// Custom hooks
			add_action('wcnt_before_search_result', array($this, 'add_search_page_top_elements'), 10, 0);
			add_action('wcnt_after_search_result', array($this, 'add_search_page_bottom_elements'), 10, 0);
			add_action('wcnt_notfound_content', array($this, 'add_notfoundpage_elements'), 10, 0);
			add_action('wcnt_before_main_content', array($this, 'add_page_top_elements'), 10, 0);
			add_action('wcnt_after_main_content', array($this, 'add_page_bottom_elements'), 10, 0);
		}
		
		
	}
	
	
	function load_upsy_customer_script()
	{

		$wp_env = wp_get_environment_type();

		$upsy_id = get_option('upsy_settings_customer_id');
		//$upsy_env = get_option('upsy_settings_environment');
		$upsy_env = $wp_env; //override setting for now

		$upsy_jsurl = get_option('upsy_settings_jsurl');

		$upsyjsurl = self::UPSYJS_URL_PRODUCTION; //default

		if($wp_env == 'local'){
			if($upsy_jsurl != ''){
				//from settings
				$upsyjsurl = $upsy_jsurl;
			}else{
				$upsyjsurl = self::UPSYJS_URL_LOCAL;
			}
		}else if($wp_env == 'staging'){
			if($upsy_jsurl != ''){
				//from settings
				$upsyjsurl = $upsy_jsurl;
			}else{
				$upsyjsurl = self::UPSYJS_URL_STAGING;
			}
		}

		/*
		//for JS tests
	  	console.log('upsyEnv: <?php echo $upsy_env; ?>');
	  	console.log('JSURL: <?php echo $upsyjsurl; ?>');
		*/

		?>
<script type="text/javascript" id="upsy-loader">
(function () {
  var e = function (c, b, d) {
    var a = document.createElement("script");
    a.src = c;
    a.onload = b;
    a.onreadystatechange = b;
    d.appendChild(a)
  }, f = function () {
    upsy_sdk.init("<?php echo esc_attr($upsy_id); ?>");
  };
<?php
if($wp_env != 'production' && $wp_env != ''){
	//dev
echo 'const upsyEnv = window.upsyEnvironment = "' . esc_attr($upsy_env) . '";';
}
?>
e("<?php echo esc_url($upsyjsurl); ?>", f, document.body)
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
	protected function init_settings()
	{
		$settings = get_option('woocommerce_upsy_tagging_settings');
		if (is_array($settings)) {
			foreach ($settings as $key => $value) {
				if (isset($this->$key)) {
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
	 * @return bool
	 * @since 1.0.0
	 */
	protected function check_dependencies()
	{
		global $wp_version;
		
		$title = sprintf(__('WooCommerce upsy Tagging %s not compatible.'), self::VERSION);
		$error = '';
		$args = array(
			'back_link' => true,
		);
		
		if (version_compare($wp_version, self::MIN_WP_VERSION, '<')) {
			$error = sprintf(
				__('Looks like you\'re running an older version of WordPress, you need to be running at least
					WordPress %1$s to use WooCommerce upsy Tagging %2$s.'),
				self::MIN_WP_VERSION,
				self::VERSION
			);
		}
		
		if (!defined('WOOCOMMERCE_VERSION')) {
			$error = sprintf(
				__('Looks like you\'re not running any version of WooCommerce, you need to be running at least
					WooCommerce %1$s to use WooCommerce upsy Tagging %2$s.'),
				self::MIN_WC_VERSION,
				self::VERSION
			);
		} else if (version_compare(WOOCOMMERCE_VERSION, self::MIN_WC_VERSION, '<')) {
			$error = sprintf(
				__('Looks like you\'re running an older version of WooCommerce, you need to be running at least
					WooCommerce %1$s to use WooCommerce upsy Tagging %2$s.'),
				self::MIN_WC_VERSION,
				self::VERSION
			);
		}
		
		if (!empty($error)) {
			deactivate_plugins($this->plugin_name);
			wp_die($error, $title, $args);
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
	public static function get_method_args($class, $func_name)
	{
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
	public function add_notfound_elements()
	{
		if (is_404()) {
			$default_element_ids = array(
				self::ELEMENT_NOTFOUND_upsy_1,
				self::ELEMENT_NOTFOUND_upsy_2,
				self::ELEMENT_NOTFOUND_upsy_3,
			);
			$this->renderElements($default_element_ids, self::PAGE_TYPE_FRONT_PAGE);
		}
	}

	public function generate_upsy_event_url($url, $data)
	{
		$key = wp_get_environment_type() != 'production' ? self::UPSYJS_ANALYTICS_KEY_STAGING : self::UPSYJS_ANALYTICS_KEY_PRODUCTION;
		$decoded_data = json_decode($data, true);
		$tenantId = $decoded_data['customerId'];
		$session_id = $decoded_data['sessionId'];
		$event_url = "{$url}/api/rooms/{$tenantId}/chats/{$session_id}/event?code={$key}";
		return $event_url;
	}
	
	public function send_http_request($data)
	{
		$url = wp_get_environment_type() != 'production' ? $this->generate_upsy_event_url(self::UPSYJS_EVENT_URL_STAGING, $data) : $this->generate_upsy_event_url(self::UPSYJS_EVENT_URL_PRODUCTION, $data);
		$decoded_data = json_decode($data, true);
		$options = [
			'body'        => $data,
			'headers'     => [
				'Content-Type' => 'application/json',
			],
			'data_format' => 'body',
		];
		$response = wp_remote_post($url, $options);

		if ( is_wp_error( $response ) ) {
			echo('error');
		} else {
			echo('success');
		}

	}

	public function generate_new_upsy_session_id($tenantId)
	{
		$characters = self::RANDOM_CHARACTERS;
		$random_string = substr(str_shuffle($characters), 0, 15);
		$session_id = "{$tenantId}-sid-{$random_string}";

		return $session_id;
	}

	public function get_upsy_session($tenantId)
	{
		$cookie = isset( $_COOKIE['upsypx'] ) ? sanitize_text_field($_COOKIE['upsypx']) : $this->generate_new_upsy_session_id($tenantId);
		return $cookie;
	}

	public function sanitize_event_data($chatId, $tenantId, $environment, $event)
	{
		$event_payload = array(
			'chatId' => $chatId,
			'customerId' => $tenantId,
			'environment' => $environment,
			'event' => $event,
			'params' => array(
				'eventTarget' => $event,
				'pageType' => 'thank_you',
				'products' => array()
			),
			'sessionId' => $chatId,
			'timestamp' => strval(time()),
		);

		return $event_payload;
		
	}

	public function process_event_data( $order_id )
	 {
		$order = new WC_Order( $order_id );
		$items = $order->get_items();
		$tenantId = get_option('upsy_settings_customer_id');
		$chatId = $this->get_upsy_session($tenantId);
		$environment = wp_get_environment_type();
		$event = self::PURCHASE_EVENT_TYPE;
		$data = $this->sanitize_event_data($chatId, $tenantId, $environment, $event);

		$price_currency_code = $order->get_currency();

		foreach ( $items as $item ) {
			$productId = $item->get_product_id();
			$name = $item->get_name();
			$quantity = $item->get_quantity();
			$unit_price = $item->get_subtotal();
			array_push($data['params']['products'], array('name' =>$name, 'quantity' => strval($quantity), 'unit_price' => $unit_price, 'productId' => strval($productId), 'price_currency_code' => $price_currency_code));
			
		}
		$json_event_data = json_encode($data);

		$this->send_http_request($json_event_data);
	}

	public function change_update_notification_msg( $translated_text, $untranslated_text, $domain ) 
	{

		if ( is_admin() ) {
			$texts = array('There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a>.' => 'There is a new version of %1$s available.',
            'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a>. <em>Automatic update is unavailable for this theme.</em>' => 'There is a new version of %1$s available. <em>Automatic update is unavailable for this theme.</em>',
			'There is a new version of %1$s available. <a href="%2$s" %3$s>View version %4$s details</a> or <a href="%5$s" %6$s>update now</a>.' => 'There is a new version of %1$s available. <a href="%5$s" %6$s>update now</a>.'
			);

			if ( array_key_exists( $untranslated_text, $texts ) ) {
				return $texts[$untranslated_text];
			}
		}

		return $translated_text;
	}
	
}

add_action('plugins_loaded', array(WC_upsy_Tagging::get_instance(), 'init'));
add_action( 'woocommerce_checkout_order_processed', array(WC_upsy_Tagging::get_instance(), 'process_event_data') );