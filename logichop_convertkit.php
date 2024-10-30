<?php
	/*
		Plugin Name: Logic Hop ConvertKit Add-on
		Plugin URI:	https://logichop.com/docs/convertkit
		Description: Use ConvertKit data to personalize WordPress: show relevant offers to subscribers, boost conversions, and make more sales.
		Author: Logic Hop
		Version: 3.0.2
		Author URI: https://logichop.com
	*/

	if (!defined('ABSPATH')) die;

	if ( is_admin() ) {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'logichop/logichop.php' ) && ! is_plugin_active( 'logic-hop/logichop.php' ) ) {
			add_action( 'admin_notices', 'logichop_convertkit_plugin_notice' );
		}
	}

	function logichop_convertkit_plugin_notice () {
		$message = sprintf(__('The Logic Hop ConvertKit Add-on requires the Logic Hop plugin. Please download and activate the <a href="%s" target="_blank">Logic Hop plugin</a>.', 'logichop'),
							'http://wordpress.org/plugins/logic-hop/'
						);

		printf('<div class="notice notice-warning is-dismissible">
						<p>
							%s
						</p>
					</div>',
					$message
				);
	}

	require_once 'includes/convertkit.php';

	/**
	 * Plugin activation/deactviation routine to clear Logic Hop transients
	 *
	 * @since    2.0.1
	 */
	function logichop_convertkit_activation () {
		delete_transient( 'logichop' );
    }
	register_activation_hook( __FILE__, 'logichop_convertkit_activation' );
	register_deactivation_hook( __FILE__, 'logichop_convertkit_activation' );

	/**
	 * Register admin notices
	 *
	 * @since    2.0.0
	 */
	function logichop_convertkit_admin_notice () {
		global $logichop;

		$message = '';

		if ( ! $logichop->logic->addon_active('convertkit') ) {
			$message = sprintf(__('The Logic Hop ConvertKit Add-on requires a <a href="%s" target="_blank">Logic Hop License Key or Data Plan</a>.', 'logichop'),
							'https://logichop.com/get-started/?ref=addon-convertkit'
						);
		}

		if ( $message ) {
			printf('<div class="notice notice-warning is-dismissible">
						<p>
							%s
						</p>
					</div>',
					$message
				);
		}
	}
	add_action( 'logichop_admin_notice', 'logichop_convertkit_admin_notice' );

	/**
	 * Plugin page links
	 *
	 * @since    1.0.0
	 * @param    array		$links			Plugin links
	 * @return   array  	$new_links 		Plugin links
	 */
	function logichop_plugin_action_links_convertkit ($links) {
		$new_links = array();
        $new_links['settings'] = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://logichop.com/docs/convertkit', 'Instructions' );
 		$new_links['deactivate'] = $links['deactivate'];
 		return $new_links;
	}
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'logichop_plugin_action_links_convertkit');

	/**
	 * Initialize functionality
	 *
	 * @since    1.0.0
	 */
	function logichop_integration_init_convertkit () {
		global $logichop;

		if ( isset( $logichop->logic ) && $logichop->logic->addon_active('convertkit') ) {
			$logichop->logic->convertkit = new LogicHop_ConvertKit($logichop->logic);

			add_action('logichop_initialize_core_data_check', 'logichop_data_check_convertkit');
			add_action('logichop_data_retrieve', 'logichop_data_retrieve_convertkit', 10, 1);
			add_action('logichop_check_track_event', 'logichop_track_event_convertkit', 10, 2);
			add_filter('logichop_data_object_create', 'logichop_object_create_convertkit');
			add_filter('logichop_condition_default_get', 'logichop_condition_default_convertkit');
			add_filter('logichop_client_meta_integrations', 'logichop_client_meta_convertkit');
			add_filter('logichop_settings_register', 'logichop_settings_register_convertkit');
			add_filter('logichop_settings_validate', 'logichop_settings_validate_convertkit', 10, 3);
			add_filter('logichop_editor_shortcode_variables', 'logichop_editor_convertkit_variables');
			add_filter('logichop_gutenberg_variables', 'logichop_gutenberg_convertkit_variables');
			add_action('logichop_configure_metaboxes', 'logichop_configure_metabox_convertkit');
			add_action('logichop_event_save', 'logichop_event_save_convertkit');
			add_filter('logichop_condition_builder_vars', 'logichop_condition_builder_vars_convertkit');
			add_action('logichop_admin_enqueue_styles', 'logichop_admin_enqueue_styles_convertkit');
			add_action('logichop_admin_enqueue_scripts', 'logichop_admin_enqueue_scripts_convertkit', 10, 2);
			add_action('logichop_public_enqueue_scripts', 'logichop_enqueue_scripts_convertkit', 10, 2);
			add_action('logichop_admin_menu_pages', 'logichop_admin_menu_page_convertkit');
			add_action('logichop_admin_settings_tabs', 'logichop_admin_settings_tab_convertkit', 10, 2);
			add_action('logichop_admin_settings_page', 'logichop_admin_settings_page_convertkit');
			add_action('logichop_register_shortcodes', 'logichop_register_shortcodes_convertkit', 10, 1);
		}
	}
	add_action('logichop_integration_init', 'logichop_integration_init_convertkit');

	/**
	 * Check for user data
	 *
	 * @since    1.0.0
	 *
	 * DEPRECATED
	 *
	 */
	function logichop_convertkit_user_check () {
		global $logichop;

		$bypass = false;

		if (isset($_REQUEST['convertkit']) && isset($_REQUEST['logichop']) && $logichop->logic->convertkit->active()) {
			$logichop->logic->hash = $_REQUEST['logichop']; // LOGIC HOP HASH PASSED FROM CONVERTKIT LINK
			$logichop->logic->session_create(); // CREATE THE SESSION :: NEW USER
			$data = $logichop->logic->data_retrieve(); // LOAD USER DATA
			if ( $logichop->logic->data_factory->get_value( 'ConvertKitID' ) != '') { // IF HASH/UID EXISTED --> CONTINUE
				$bypass = true;
				$logichop->logic->cookie_create(); // CREATE THE COOKIE :: STORE HASH
			} else { // INVALID HASH/UID --> RESET
				$logichop->logic->hash = null;
				$logichop->logic->session_delete();
			}
		}

		return $bypass;
	}
	//add_filter('logichop_initialize_core', 'logichop_convertkit_user_check');

	/**
	 * Check for ConvertKit data
	 *
	 * @since    1.0.0
	 */
	function logichop_data_check_convertkit () {
		global $logichop;
		if ( ! isset( $logichop ) ) return;
		$logichop->logic->convertkit->data_check();
	}

	/**
	 * Parse data returned from SPF lookup
	 *
	 * @since    1.0.0
	 * @param    array		$data	Store data
	 * @return   boolean   	Data retrieved
	 */
	function logichop_data_retrieve_convertkit ($data) {
		global $logichop;

		$data = array_change_key_case($data, CASE_LOWER);

		if (isset($data['convertkit'])) {
			if ( is_string( $data['convertkit'] ) && $data['convertkit'] != '' ) {
				$logichop->logic->data_factory->set_value( 'ConvertKitID', $data['convertkit'] );
				return true;
			}

			if ( is_array( $data['convertkit'] ) ) {
				foreach ($data['convertkit'] as $key => $value) {
					$logichop->logic->data_factory->set_value( 'ConvertKitID', $key );
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Handle event tracking
	 *
	 * @since    1.0.0
	 * @param    integer	$id		Goal ID
	 * @return   boolean   	Event tracked
	 */
	function logichop_track_event_convertkit ($id, $values) {
		global $logichop;

		return $logichop->logic->convertkit->track_event($id, $values);
	}

	/**
	 * Create default session data
	 *
	 * @since    1.0.0
	 */
	function logichop_object_create_convertkit ( $data = null ) {
		if ( is_null( $data ) ) {
			$data = new stdclass;
		}
		$data->ConvertKitID		= '';
		$data->ConvertKit			= new stdclass();
		$data->ConvertKit->tags	= array ();
		return $data;
	}

	/**
	 * Generate default conditions
	 *
	 * @since    1.0.0
	 * @param    array		$conditions		Array of default conditions
	 * @return   array    	$conditions		Array of default conditions
	 */
	function logichop_condition_default_convertkit ($conditions) {
		global $logichop;

		if ( ! isset( $logichop->logic->convertkit ) ) {
			return array();
		}

		if ($logichop->logic->convertkit->active()) {
			$conditions['convertkit'] = array (
					'title' => "ConvertKit Data Is Available for User",
					'rule'	=> '{"==": [ {"var": "ConvertKit.email_address" }, true ] }',
					'info'	=> "Is ConvertKit data available for the current user."
				);
		}
		return $conditions;
	}

	/**
	 * Generate client meta data
	 *
	 * @since    1.0.0
	 * @param    array		$integrations	Integration names
	 * @return   array    	$integrations	Integration names
	 */
	function logichop_client_meta_convertkit ($integrations) {
		$integrations[] = 'convertkit';
		return $integrations;
	}

	/**
	 * Add settings
	 *
	 * @since    1.0.0
	 * @param    array		$settings	Settings parameters
	 * @return   array    	$settings	Settings parameters
	 */
	function logichop_settings_register_convertkit ($settings) {
		$settings['convertkit_key'] = array (
							'name' 	=> __('ConvertKit API Key', 'logichop'),
							'meta' 	=> __('Enables ConvertKit integration. <a href="https://logichop.com/docs/using-logic-hop-with-convertkit/" target="_blank">Learn More</a>.', 'logichop'),
							'type' 	=> 'text',
							'label' => '',
							'opts'  => null
						);
		$settings['convertkit_secret'] = array (
							'name' 	=> __('ConvertKit API Secret', 'logichop'),
							'meta' 	=> __('Enables ConvertKit integration. <a href="https://logichop.com/docs/using-logic-hop-with-convertkit/" target="_blank">Learn More</a>.', 'logichop'),
							'type' 	=> 'text',
							'label' => '',
							'opts'  => null
						);

		return $settings;
	}

	/**
	 * Validate settings
	 *
	 * @since    1.0.0
	 * @param    string		$key		Settings key
	 * @return   string    	$result		Error object
	 */
	function logichop_settings_validate_convertkit ($validation, $key, $input) {
		global $logichop;

		if ($key == 'convertkit_secret') {
			if (!$logichop->logic->convertkit->set_up($input[$key])) {
				$validation->error = true;
         		$validation->error_msg = '<li>Invalid ConvertKit API Secret</li>';
         	}
		}

        if ($key == 'convertkit_key') {
        	if ($logichop->logic->convertkit->tags_get($input[$key]) === false) {
        		$validation->error = true;
         		$validation->error_msg = '<li>Invalid ConvertKit API Key</li>';
         	}
		}

		return $validation;
	}

	/**
	 * Generate editor modal nav
	 *
	 * @since    1.0.0
	 * @param    string		$tab_navigation	Navigation tabs
	 * @return   string    	Navigation tab
	 *
	 * DEPRECATED 3.0.0
	 *
	 */
	function logichop_editor_nav_convertkit ($tab_navigation) {
		return $tab_navigation . '<a href="#" class="nav-tab" data-tab="logichop-modal-convertkit">ConvertKit</a>';
	}
	//add_filter('logichop_editor_modal_nav', 'logichop_editor_nav_convertkit');

	/**
	 * Generate editor modal panel
	 *
	 * @since    1.0.0
	 * @param    string		$tab_panel	Modal panel
	 * @return   string    	Modal panel
	 *
	 * DEPRECATED 3.0.0
	 *
	 */
	function logichop_editor_panel_convertkit ($tab_panel) {
		global $logichop;

		$panel = '';
		if ($logichop->logic->convertkit->active()) {
			$ck_vars = $logichop->logic->convertkit->shortcode_variables();
			$panel = sprintf('<div class="nav-tab-display logichop-modal-convertkit">
									<h4>%s</h4>
									<select id="logichop_convertkit_var">
										<option value="">%s</option>
										%s
									</select>
									<p>
										<button class="button button-primary logichop_insert_data_shortcode" data-input="#logichop_convertkit_var">%s</button>
									</p>
									<hr>

									<h4>%s</h4>
									<select id="logichop_convertkit_js">
										<option value="">%s</option>
										%s
									</select>

									<h4>%s</h4>
									<select id="logichop_convertkit_js_event">
										<option value="show">Show</option>
										<option value="fadeIn">Fade In</option>
										<option value="slideDown">Slide Down</option>
									</select>

									<p>
										<button class="button button-primary logichop_insert_data_javascript" data-input="#logichop_convertkit_js">%s</button>
									</p>
								</div>',
					__('ConvertKit Variable Display Shortcode', 'logichop'),
					__('Select a variable', 'logichop'),
					$ck_vars,
					__('Insert Variable Shortcode', 'logichop'),

					__('ConvertKit Variable Display Javascript', 'logichop'),
					__('Select a variable', 'logichop'),
					$ck_vars,
					__('Event', 'logichop'),
					__('Insert Variable Javascript ', 'logichop')
				);
		}

		return $tab_panel . $panel;
	}
	//add_filter('logichop_editor_modal_panel', 'logichop_editor_panel_convertkit');

	/**
	 * Add variables to editor
	 *
	 * @since    2.0.0
	 * @return   string    	Variables as datalist options
	 */
	function logichop_editor_convertkit_variables ( $datalist ) {
		global $logichop;

		return $datalist . $logichop->logic->convertkit->shortcode_variables();
	}

	/**
	 * Add variables to Gutenberg plugin
	 *
	 * @since    2.0.4
	 * @return   string    	Variables as array
	 */
	function logichop_gutenberg_convertkit_variables ( $options ) {
		global $logichop;

		$vars = $logichop->logic->convertkit->shortcode_variables_data();

		foreach( $vars as $k => $v ) {
			$options[] = [
					'value' => $k,
					'label' => sprintf( 'ConvertKit %s', $v )
				];
		}

		return $options;
	}


	/**
	 * Add goal metabox
	 *
	 * @since    1.0.0
	 */
	function logichop_configure_metabox_convertkit () {
		global $logichop;

		add_meta_box(
				'logichop_goal_convertkit_tag',
				__('ConvertKit', 'logichop'),
				array($logichop->logic->convertkit, 'goal_tag_display'),
				array('logichop-goals'),
				'normal',
				'low'
			);
	}

	/**
	 * Save event data
	 *
	 * @since    1.0.0
	 * @param    integer	$post_id	WP post ID
	 */
	function logichop_event_save_convertkit ($post_id) {
		if (isset($_POST['logichop_goal_ck_tag'])) 			update_post_meta($post_id, 'logichop_goal_ck_tag', wp_kses($_POST['logichop_goal_ck_tag'],''));
		if (isset($_POST['logichop_goal_ck_tag_action'])) 	update_post_meta($post_id, 'logichop_goal_ck_tag_action', wp_kses($_POST['logichop_goal_ck_tag_action'],''));
		if (isset($_POST['logichop_goal_ck_custom_field']))	update_post_meta($post_id, 'logichop_goal_ck_custom_field', wp_kses($_POST['logichop_goal_ck_custom_field'],''));
		if (isset($_POST['logichop_goal_ck_custom_value'])) update_post_meta($post_id, 'logichop_goal_ck_custom_value', wp_kses($_POST['logichop_goal_ck_custom_value'],''));
		if (isset($_POST['logichop_goal_ck_custom_type'])) 	update_post_meta($post_id, 'logichop_goal_ck_custom_type', wp_kses($_POST['logichop_goal_ck_custom_type'],''));
	}

	/**
	 * Output Javscript variables
	 *
	 * @since    1.0.0
	 * @return   string    Javscript variables
	 */
	function logichop_condition_builder_vars_convertkit ($condition_vars) {
		global $logichop;

		$ck_tags 	= $logichop->logic->convertkit->tags_get_json();
		$ck_fields	= $logichop->logic->convertkit->fields_get_json();

		return sprintf('%s var logichop_ck_tags = %s; var logichop_ck_fields = %s;', $condition_vars, $ck_tags, $ck_fields);
	}

	/**
	 * Enqueue styles
	 *
	 * @since    1.0.0
	 */
	function logichop_admin_enqueue_styles_convertkit ($hook) {
		global $logichop;

		if (in_array($hook, array('post.php', 'post-new.php'))) {
			$css_path = sprintf('%sadmin/logichop_convertkit.css', plugin_dir_url( __FILE__ ));
			wp_enqueue_style( 'logichop_convertkit', $css_path, array(), $logichop->logic->convertkit->version, 'all' );
		}
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @since    1.0.0
	 */
	function logichop_admin_enqueue_scripts_convertkit ($hook, $post_type) {
		global $logichop;

		if ($post_type == 'logichop-conditions') {
			$js_path = sprintf('%sadmin/logichop_convertkit.js', plugin_dir_url( __FILE__ ));

			$js_params = array(
						'tags' 		=> json_decode($logichop->logic->convertkit->tags_get_json()),
						'fields'	=> json_decode($logichop->logic->convertkit->fields_get_json())
					);

 			wp_enqueue_script( 'logichop_convertkit', $js_path, array( 'jquery' ), $logichop->logic->convertkit->version, false );
 			wp_localize_script( 'logichop_convertkit', 'logichop_convertkit', $js_params);
		}

		if ($post_type == 'logichop-goals') {
			$js_path = sprintf('%sadmin/logichop_convertkit_goals.js', plugin_dir_url( __FILE__ ));
			wp_enqueue_script( 'logichop_convertkit', $js_path, array( 'jquery' ), $logichop->logic->convertkit->version, false );
		}
	}

	/**
	 * Enqueue public scripts
	 *
	 * @since    2.0.0
	 */
	function logichop_enqueue_scripts_convertkit ($hook, $post_type) {
		global $logichop;

		$js_path = sprintf('%spublic/logichop_convertkit.js', plugin_dir_url( __FILE__ ));
		wp_enqueue_script( 'logichop_convertkit', $js_path, array( 'jquery' ), $logichop->logic->convertkit->version, false );
	}

	/**
	 * Add admin menu
	 *
	 * @since    1.0.0
	 */
	function logichop_admin_menu_page_convertkit () {
		add_submenu_page(
			'logichop-menu',
			'ConvertKit',
			'ConvertKit',
			'manage_options',
			'admin.php?page=logichop-settings&tab=convertkit',
			''
		);
	}

	/**
	 * Add tab navigation to settings page
	 *
	 * @param    string		$tabs	Tab HTML
	 * @param    string		$active	Active tab
	 * @return   string    	$tabs	Tab HTML
	 * @since    1.0.0
	 */
	function logichop_admin_settings_tab_convertkit ($tabs, $active) {
		return sprintf('%s <a href="?page=logichop-settings&tab=convertkit" class="nav-tab %s">ConvertKit</a>',
							$tabs,
							($active == 'convertkit') ? 'nav-tab-active' : ''
						);
	}

	/**
	 * Include settings page when tab is active
	 *
	 * @param    string		$active	Active tab
	 * @since    1.0.0
	 */
	function logichop_admin_settings_page_convertkit ($active) {
		if ($active == 'convertkit') include_once('admin/settings.php');
	}

	/**
	 * Register shortcodes
	 *
	 * @param    object		$public		Public class
	 * @since    1.0.0
	 */
	function logichop_register_shortcodes_convertkit ($public) {
		add_shortcode( 'logichop_data_ck', array($public, 'shortcode_logichop_data_display') );
	}
