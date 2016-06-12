<?php
/*
Plugin Name: WP e-Commerce - Store Exporter
Plugin URI: http://www.visser.com.au/wp-ecommerce/plugins/exporter/
Description: Export store details out of WP e-Commerce into simple formatted files (e.g. CSV, XML, TXT, etc.).
Version: 1.6.9
Author: Visser Labs
Author URI: http://www.visser.com.au/about/
Text Domain: wp-e-commerce-exporter
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'WPSC_CE_DIRNAME', basename( dirname( __FILE__ ) ) );
define( 'WPSC_CE_RELPATH', basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );
define( 'WPSC_CE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSC_CE_PREFIX', 'wpsc_ce' );

// Turn this on to enable additional debugging options at export time
define( 'WPSC_CE_DEBUG', false );

include_once( WPSC_CE_PATH . 'includes/common.php' );
include_once( WPSC_CE_PATH . 'includes/functions.php' );
include_once( WPSC_CE_PATH . 'includes/functions-alternatives.php' );

if( version_compare( wpsc_get_major_version(), '3.7', '<=' ) ) {
	include_once( WPSC_CE_PATH . 'includes/release-3_7.php' );
} else if( version_compare( wpsc_get_major_version(), '3.8', '>=' ) ) {
	include_once( WPSC_CE_PATH . 'includes/release-3_8.php' );
}

function wpsc_ce_i18n() {

	$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-e-commerce-exporter' );
	load_plugin_textdomain( 'wp-e-commerce-exporter', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

}
add_action( 'init', 'wpsc_ce_i18n' );

if( is_admin() ) {

	/* Start of: WordPress Administration */

	// Add Export and Docs links to the Plugins screen
	function wpsc_ce_add_settings_link( $links, $file ) {

		static $this_plugin;

		if( !$this_plugin ) $this_plugin = plugin_basename( __FILE__ );
		if( $file == $this_plugin ) {
			$docs_url = 'http://www.visser.com.au/docs/';
			$docs_link = sprintf( '<a href="%s" target="_blank">' . __( 'Docs', 'wp-e-commerce-exporter' ) . '</a>', $docs_url );
			if( function_exists( 'wpsc_find_purchlog_status_name' ) )
				$export_link = sprintf( '<a href="%s">' . __( 'Export', 'wp-e-commerce-exporter' ) . '</a>', esc_url( add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_ce' ), 'edit.php' ) ) );
			else
				$export_link = sprintf( '<a href="%s">' . __( 'Export', 'wp-e-commerce-exporter' ) . '</a>', esc_url( add_query_arg( 'page', 'wpsc_ce', 'admin.php' ) ) );
			array_unshift( $links, $docs_link );
			array_unshift( $links, $export_link );
		}
		return $links;

	}
	add_filter( 'plugin_action_links', 'wpsc_ce_add_settings_link', 10, 2 );

	// Load CSS and jQuery scripts for Store Exporter screen
	function wpsc_ce_enqueue_scripts( $hook ) {

		$pages = array( 'wpsc-product_page_wpsc_ce', 'store_page_wpsc_ce' );
		if( in_array( $hook, $pages ) ) {
			// Date Picker
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-datepicker', plugins_url( '/templates/admin/jquery-ui-datepicker.css', __FILE__ ) );

			// Chosen
			wp_enqueue_script( 'jquery-chosen', plugins_url( '/js/jquery.chosen.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_style( 'jquery-chosen', plugins_url( '/templates/admin/chosen.css', __FILE__ ) );

			// Common
			wp_enqueue_style( 'wpsc_ce_styles', plugins_url( '/templates/admin/export.css', __FILE__ ) );
			wp_enqueue_script( 'wpsc_ce_scripts', plugins_url( '/templates/admin/export.js', __FILE__ ), array( 'jquery' ) );

			if( WPSC_CE_DEBUG ) {
				wp_enqueue_style( 'jquery-csvToTable', plugins_url( '/templates/admin/jquery-csvtable.css', WPSC_CE_RELPATH ) );
				wp_enqueue_script( 'jquery-csvToTable', plugins_url( '/js/jquery.csvToTable.js', WPSC_CE_RELPATH ), array( 'jquery' ) );
			}

		}

	}
	add_action( 'admin_enqueue_scripts', 'wpsc_ce_enqueue_scripts' );

	// Add Store Export menu to alternative Store menu
	function wpsc_ce_store_admin_menu() {

		add_submenu_page( 'wpsc_sm', __( 'Store Export', 'wp-e-commerce-exporter' ), __( 'Store Export', 'wp-e-commerce-exporter' ), 'manage_options', 'wpsc_ce', 'wpsc_ce_html_page' );
		remove_filter( 'wpsc_additional_pages', 'wpsc_ce_add_modules_admin_pages', 10 );

	}
	add_action( 'wpsc_sm_store_admin_subpages', 'wpsc_ce_store_admin_menu' );

	// Initial scripts and export process
	function wpsc_ce_admin_init() {

		global $export, $wp_roles;

		// Check the User has the manage_options capability
		if( current_user_can( 'manage_options' ) == false )
			return;

		// Check that we are on the Store Exporter screen
		$page = ( isset($_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : false );
		if( $page != strtolower( WPSC_CE_PREFIX ) )
			return;

		// Process any pre-export notice confirmations
		$action = ( function_exists( 'wpsc_get_action' ) ? wpsc_get_action() : false );
		switch( $action ) {

			// Prompt on Export screen when insufficient memory (less than 64M is allocated)
			case 'dismiss_memory_prompt':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'wpsc_ce_dismiss_memory_prompt' ) ) {
					// Remember that we've dismissed this notice
					wpsc_ce_update_option( 'dismiss_memory_prompt', 1 );
					$url = add_query_arg( array( 'action' => null, '_wpnonce' => null ) );
					wp_redirect( $url );
					exit();
				}
				break;

			// Save skip overview preference
			case 'skip_overview':
				// We need to verify the nonce.
				if( !empty( $_POST ) && check_admin_referer( 'skip_overview', 'wpsc_ce_skip_overview' ) ) {
					$skip_overview = false;
					if( isset( $_POST['skip_overview'] ) )
						$skip_overview = 1;
					wpsc_ce_update_option( 'skip_overview', $skip_overview );

					if( $skip_overview == 1 ) {
						$url = add_query_arg( array( 'tab' => 'export', '_wpnonce' => null ) );
						wp_redirect( $url );
						exit();
					}
				}
				break;

			// This is where the magic happens
			case 'export':

				// Make sure we play nice with other WP e-Commerce and WordPress exporters
				if( !isset( $_POST['wpsc_ce_export'] ) )
					return;

				check_admin_referer( 'manual_export', 'wpsc_ce_export' );

				// Set up the basic export options
				$export = new stdClass();
				$export->start_time = time();
				$export->idle_memory_start = wpsc_ce_current_memory_usage();
				$export->encoding = wpsc_ce_get_option( 'encoding', get_option( 'blog_charset', 'UTF-8' ) );
				if( $export->encoding == '' ) {
					$export->encoding = 'UTF-8';
				}
				$export->delimiter = wpsc_ce_get_option( 'delimiter', ',' );
				$export->category_separator = wpsc_ce_get_option( 'category_separator', '|' );
				$export->bom = wpsc_ce_get_option( 'bom', 1 );
				$export->escape_formatting = wpsc_ce_get_option( 'escape_formatting', 'all' );
				$export->date_format = wpsc_ce_get_option( 'date_format', 'd/m/Y' );

				// Save export option changes made on the Export screen
				$export->limit_volume = ( isset( $_POST['limit_volume'] ) ? $_POST['limit_volume'] : '' );
				wpsc_ce_update_option( 'limit_volume', $export->limit_volume );
				if( $export->limit_volume == '' )
					$export->limit_volume = -1;
				$export->offset = ( isset( $_POST['offset'] ) ? $_POST['offset'] : '' );
				wpsc_ce_update_option( 'offset', $export->offset );
				if( $export->offset == '' )
					$export->offset = 0;
				if( function_exists( 'wpsc_cd_admin_init' ) )
					wpsc_ce_update_option( 'export_format', (string)$_POST['export_format'] );

				// Set default values for all export options to be later passed onto the export process
				$export->fields = false;
				$export->export_format = wpsc_ce_get_option( 'export_format', 'csv' );

				// Product sorting
				$export->product_categories = false;
				$export->product_tags = false;
				$export->product_status = false;
				$export->product_orderby = false;
				$export->product_order = false;

				// Category sorting
				$export->category_orderby = false;
				$export->category_order = false;

				// Tag sorting
				$export->tag_orderby = false;
				$export->tag_order = false;

				// Order sorting
				$export->order_dates_filter = false;
				$export->order_dates_from = '';
				$export->order_dates_to = '';
				$export->order_status = false;
				$export->order_customer = false;
				$export->order_user_roles = false;
				$export->order_product = false;
				$export->order_orderby = false;
				$export->order_order = false;

				$export->type = ( isset( $_POST['dataset'] ) ? $_POST['dataset'] : false );
				switch( $export->type ) {

					case 'products':
						// Set up dataset specific options
						$export->fields = ( isset( $_POST['product_fields'] ) ? $_POST['product_fields'] : false );
						$export->product_categories = ( isset( $_POST['product_filter_categories'] ) ? wpsc_ce_format_product_filters( $_POST['product_filter_categories'] ) : false );
						$export->product_tags = ( isset( $_POST['product_filter_tags'] ) ? wpsc_ce_format_product_filters( $_POST['product_filter_tags'] ) : false );
						$export->product_status = ( isset( $_POST['product_filter_status'] ) ? wpsc_ce_format_product_filters( $_POST['product_filter_status'] ) : false );
						$export->product_orderby = ( isset( $_POST['product_orderby'] ) ? $_POST['product_orderby'] : false );
						$export->product_order = ( isset( $_POST['product_order'] ) ? $_POST['product_order'] : false );

						// Save dataset export specific options
						// @mod - Add support for saving Product Categories, Prduct Tags, Product Status, Product Type
						if( $export->product_orderby <> wpsc_ce_get_option( 'product_orderby' ) )
							wpsc_ce_update_option( 'product_orderby', $export->product_orderby );
						if( $export->product_order <> wpsc_ce_get_option( 'product_order' ) )
							wpsc_ce_update_option( 'product_order', $export->product_order );
						break;

					case 'categories':
						// Set up dataset specific options
						$export->fields = ( isset( $_POST['category_fields'] ) ? $_POST['category_fields'] : false );
						$export->category_orderby = ( isset( $_POST['category_orderby'] ) ? $_POST['category_orderby'] : false );
						$export->category_order = ( isset( $_POST['category_order'] ) ? $_POST['category_order'] : false );

						// Save dataset export specific options
						if( $export->category_orderby <> wpsc_ce_get_option( 'category_orderby' ) )
							wpsc_ce_update_option( 'category_orderby', $export->category_orderby );
						if( $export->category_order <> wpsc_ce_get_option( 'category_order' ) )
							wpsc_ce_update_option( 'category_order', $export->category_order );
						break;

					case 'tags':
						// Set up dataset specific options
						$export->fields = ( isset( $_POST['tag_fields'] ) ? $_POST['tag_fields'] : false );
						$export->tag_orderby = ( isset( $_POST['tag_orderby'] ) ? $_POST['tag_orderby'] : false );
						$export->tag_order = ( isset( $_POST['tag_order'] ) ? $_POST['tag_order'] : false );

						// Save dataset export specific options
						if( $export->tag_orderby <> wpsc_ce_get_option( 'tag_orderby' ) )
							wpsc_ce_update_option( 'tag_orderby', $export->tag_orderby );
						if( $export->tag_order <> wpsc_ce_get_option( 'tag_order' ) )
							wpsc_ce_update_option( 'tag_order', $export->tag_order );
						break;

					case 'orders':
						// Set up dataset specific options
						$export->fields = ( isset( $_POST['order_fields'] ) ? $_POST['order_fields'] : false );
						$export->order_dates_filter = ( isset( $_POST['order_dates_filter'] ) ? $_POST['order_dates_filter'] : false );
						$export->order_dates_from = $_POST['order_dates_from'];
						$export->order_dates_to = $_POST['order_dates_to'];
						$export->order_status = ( isset( $_POST['order_filter_status'] ) ? wpsc_ce_format_product_filters( $_POST['order_filter_status'] ) : false );
						$export->order_customer = ( isset( $_POST['order_customer'] ) ? $_POST['order_customer'] : false );
						$export->order_user_roles = ( isset( $_POST['order_filter_user_role'] ) ? wpsc_ce_format_user_role_filters( $_POST['order_filter_user_role'] ) : false );
						$export->order_product = ( isset( $_POST['order_filter_product'] ) ? wpsc_ce_format_product_filters( $_POST['order_filter_product'] ) : false );
						$export->order_orderby = ( isset( $_POST['order_orderby'] ) ? $_POST['order_orderby'] : false );
						$export->order_order = ( isset( $_POST['order_order'] ) ? $_POST['order_order'] : false );

						// Save dataset export specific options
						if( $export->order_orderby <> wpsc_ce_get_option( 'order_orderby' ) )
							wpsc_ce_update_option( 'order_orderby', $export->order_orderby );
						if( $export->order_order <> wpsc_ce_get_option( 'order_order' ) )
							wpsc_ce_update_option( 'order_order', $export->order_order );
						break;

					case 'customers':
						// Set up dataset specific options
						$export->fields = $_POST['customer_fields'];
						break;

					case 'coupons':
						// Set up dataset specific options
						$export->fields = $_POST['coupon_fields'];
						break;

				}
				if( $export->type ) {

					$timeout = 600;
					if( isset( $_POST['timeout'] ) ) {
						$timeout = absint( $_POST['timeout'] );
						if( $timeout <> wpsc_ce_get_option( 'timeout' ) )
							wpsc_ce_update_option( 'timeout', $timeout );
					}
					if( !ini_get( 'safe_mode' ) ) {
						@set_time_limit( $timeout );
						@ini_set( 'max_execution_time', $timeout );
					}

					@ini_set( 'memory_limit', WP_MAX_MEMORY_LIMIT );

					$export->args = array(
						'limit_volume' => $export->limit_volume,
						'offset' => $export->offset,
						'encoding' => $export->encoding,
						'date_format' => $export->date_format,
						'product_categories' => $export->product_categories,
						'product_tags' => $export->product_tags,
						'product_status' => $export->product_status,
						'product_orderby' => $export->product_orderby,
						'product_order' => $export->product_order,
						'category_orderby' => $export->category_orderby,
						'category_order' => $export->category_order,
						'tag_orderby' => $export->tag_orderby,
						'tag_order' => $export->tag_order,
						'order_status' => $export->order_status,
						'order_dates_filter' => $export->order_dates_filter,
						'order_dates_from' => wpsc_ce_format_order_date( $export->order_dates_from ),
						'order_dates_to' => wpsc_ce_format_order_date( $export->order_dates_to ),
						'order_customer' => $export->order_customer,
						'order_user_roles' => $export->order_user_roles,
						'order_product' => $export->order_product,
						'order_orderby' => $export->order_orderby,
						'order_order' => $export->order_order
					);
					wpsc_ce_save_fields( $export->type, $export->fields );
					if( $export->export_format == 'csv' )
						$export->filename = wpsc_ce_generate_csv_filename( $export->type );
					// Print file contents to debug export screen
					if( WPSC_CE_DEBUG ) {

						if( in_array( $export->export_format, array( 'csv' ) ) ) {
							wpsc_ce_export_dataset( $export->type );
						}
						$export->idle_memory_end = wpsc_ce_current_memory_usage();
						$export->end_time = time();

					// Print file contents to browser
					} else {
						if( in_array( $export->export_format, array( 'csv' ) ) ) {

							// Generate CSV contents
							$bits = wpsc_ce_export_dataset( $export->type );
							unset( $export->fields );
							if( empty( $bits ) ) {
								$url = add_query_arg( 'empty', true );
								wp_redirect( $url );
								exit();
							}
							if( wpsc_ce_get_option( 'delete_file', 1 ) ) {

								// Print to browser
								wpsc_ce_generate_csv_header( $export->type );
								echo $bits;
								exit();

							} else {

								// Save to file and insert to WordPress Media
								if( $export->filename && $bits ) {
									$post_ID = wpsc_ce_save_file_attachment( $export->filename, 'text/csv' );
									$upload = wp_upload_bits( $export->filename, null, $bits );
									if( $upload['error'] ) {
										wp_delete_attachment( $post_ID, true );
										$url = esc_url( add_query_arg( array( 'failed' => true, 'message' => urlencode( $upload['error'] ) ) ) );
										wp_redirect( $url );
										return;
									}
									$attach_data = wp_generate_attachment_metadata( $post_ID, $upload['file'] );
									wp_update_attachment_metadata( $post_ID, $attach_data );
									update_attached_file( $post_ID, $upload['file'] );
									if( $post_ID ) {
										wpsc_ce_save_file_guid( $post_ID, $export->type, $upload['url'] );
										wpsc_ce_save_file_details( $post_ID );
									}
									$export_type = $export->type;
									unset( $export );

									// The end memory usage and time is collected at the very last opportunity prior to the CSV header being rendered to the screen
									wpsc_ce_update_file_detail( $post_ID, '_wpsc_idle_memory_end', wpsc_ce_current_memory_usage() );
									wpsc_ce_update_file_detail( $post_ID, '_wpsc_end_time', time() );

									// Generate CSV header
									wpsc_ce_generate_csv_header( $export_type );
									unset( $export_type );

									// Print file contents to screen
									if( $upload['file'] ) {
										readfile( $upload['file'] );
									} else {
										$url = add_query_arg( 'failed', true );
										wp_redirect( $url );
									}
									unset( $upload );
								} else {
									$url = add_query_arg( 'failed', true );
									wp_redirect( $url );
								}
								exit();

							}

						}
					}
				}
				break;

			// Save changes on Settings screen
			case 'save':
				// We need to verify the nonce.
				if( !empty( $_POST ) && check_admin_referer( 'save_settings', 'wpsc_ce_save_settings' ) ) {
					wpsc_ce_update_option( 'export_filename', (string)$_POST['export_filename'] );
					wpsc_ce_update_option( 'delete_file', absint( $_POST['delete_file'] ) );
					wpsc_ce_update_option( 'delimiter', (string)$_POST['delimiter'] );
					wpsc_ce_update_option( 'category_separator', (string)$_POST['category_separator'] );
					wpsc_ce_update_option( 'bom', (string)$_POST['bom'] );
					wpsc_ce_update_option( 'encoding', (string)$_POST['encoding'] );
					wpsc_ce_update_option( 'escape_formatting', (string)$_POST['escape_formatting'] );
					wpsc_ce_update_option( 'date_format', (string)$_POST['date_format'] );

					// Save Store Exporter Deluxe options if present
					if( function_exists( 'wpsc_cd_admin_init' ) ) {
						wpsc_ce_update_option( 'xml_attribute_url', absint( $_POST['xml_attribute_url'] ) );
						wpsc_ce_update_option( 'xml_attribute_title', absint( $_POST['xml_attribute_title'] ) );
						wpsc_ce_update_option( 'xml_attribute_date', absint( $_POST['xml_attribute_date'] ) );
						wpsc_ce_update_option( 'xml_attribute_time', absint( $_POST['xml_attribute_time'] ) );
						wpsc_ce_update_option( 'xml_attribute_export', absint( $_POST['xml_attribute_export'] ) );
						// Display additional notice if Enabled Scheduled Exports is enabled/disabled
						if( wpsc_ce_get_option( 'enable_auto', 0 ) <> absint( $_POST['enable_auto'] ) ) {
							$message = sprintf( __( 'Scheduled exports has been %s.', 'wp-e-commerce-exporter' ), ( ( absint( $_POST['enable_auto'] ) == 1 ) ? sprintf( __( 'activated, next scheduled export will run in %d minutes', 'wp-e-commerce-exporter' ), absint( $_POST['auto_interval'] ) ) : __( 'de-activated, no further automated exports will occur', 'wp-e-commerce-exporter' ) ) );
							wpsc_ce_admin_notice( $message );
						}
						wpsc_ce_update_option( 'enable_auto', absint( $_POST['enable_auto'] ) );
						wpsc_ce_update_option( 'auto_type', (string)$_POST['auto_type'] );
						wpsc_ce_update_option( 'auto_interval', absint( $_POST['auto_interval'] ) );
						wpsc_ce_update_option( 'auto_method', (string)$_POST['auto_method'] );
						// Display additional notice if Enabled CRON is enabled/disabled
						if( wpsc_ce_get_option( 'enable_cron', 0 ) <> absint( $_POST['enable_cron'] ) ) {
							// Remove from WP-CRON schedule if disabled
							if( absint( $POST['enable_cron'] ) == 0 && function_exists( 'wpsc_cd_admin_init' ) )
								wpsc_cd_cron_activation();
							$message = sprintf( __( 'CRON support has been %s.', 'wp-e-commerce-exporter' ), ( ( absint( $_POST['enable_cron'] ) == 1 ) ? __( 'enabled', 'wp-e-commerce-exporter' ) : __( 'disabled', 'wp-e-commerce-exporter' ) ) );
							wpsc_ce_admin_notice( $message );
						}
						wpsc_ce_update_option( 'enable_cron', absint( $_POST['enable_cron'] ) );
						wpsc_ce_update_option( 'secret_key', (string)$_POST['secret_key'] );
						wpsc_ce_update_option( 'email_to', (string)$_POST['email_to'] );
						wpsc_ce_update_option( 'post_to', (string)$_POST['post_to'] );
					}
					$message = __( 'Changes have been saved.', 'wp-e-commerce-exporter' );
					wpsc_ce_admin_notice( $message );
				}
				break;

			default:
				// Detect other platform versions
				wpsc_ce_detect_non_wpsc_install();

				add_action( 'wpsc_ce_export_order_options_before_table', 'wpsc_ce_orders_filter_by_date' );
				add_action( 'wpsc_ce_export_order_options_before_table', 'wpsc_ce_orders_filter_by_status' );
				add_action( 'wpsc_ce_export_order_options_before_table', 'wpsc_ce_orders_filter_by_product' );
				add_action( 'wpsc_ce_export_order_options_before_table', 'wpsc_ce_orders_filter_by_customer' );
				add_action( 'wpsc_ce_export_order_options_after_table', 'wpsc_ce_orders_order_sorting' );
				add_action( 'wpsc_ce_export_options', 'wpsc_ce_export_options_export_format' );
				break;

		}

	}
	add_action( 'admin_init', 'wpsc_ce_admin_init' );

	// HTML templates and form processor for Store Exporter screen
	function wpsc_ce_html_page() {

		global $wpdb, $export;

		$title = apply_filters( 'wpsc_ce_template_header', '' );
		wpsc_ce_template_header( $title );
		wpsc_ce_support_donate();
		$action = ( function_exists( 'wpsc_get_action' ) ? wpsc_get_action() : false );
		switch( $action ) {

			case 'export':
				$message = __( 'Chosen WP e-Commerce details have been exported from your store.', 'wp-e-commerce-exporter' );
				wpsc_ce_admin_notice( $message );
				if( WPSC_CE_DEBUG ) {
					$output = '';
					if( false === ( $export_log = get_transient( WPSC_CE_PREFIX . '_debug_log' ) ) ) {
						$export_log = __( 'No export entries were found, please try again with different export filters.', 'wp-e-commerce-exporter' );
					} else {
						// We take the contents of our WordPress Transient and de-base64 it back to CSV format
						$export_log = base64_decode( $export_log );
					}
					delete_transient( WPSC_CE_PREFIX . '_debug_log' );
					$output = '
<script>
	$j(function() {
		$j(\'#export_sheet\').CSVToTable(\'\', { startLine: 0 });
	});
</script>
<h3>' . sprintf( __( 'Export Details: %s', 'wp-e-commerce-exporter' ), $export->filename ) . '</h3>
<p>' . __( 'This prints the $export global that contains the different export options and filters to help reproduce this on another instance of WordPress. Very useful for debugging blank or unexpected exports.', 'wp-e-commerce-exporter' ) . '</p>
<textarea id="export_log">' . print_r( $export, true ) . '</textarea>
<hr />
<h3>' . __( 'Export', 'wp-e-commerce-exporter' ) . '</h3>
<p>' . __( 'We use the <a href="http://code.google.com/p/jquerycsvtotable/" target="_blank"><em>CSV to Table plugin</em></a> to see first hand formatting errors or unexpected values within the export file.', 'wp-e-commerce-exporter' ) . '</p>
<div id="export_sheet" style="margin-bottom:1em;">' . $export_log . '</div>
<p class="description">' . __( 'This jQuery plugin can fail with <code>\'Item count (28) does not match header count\'</code> notices which simply mean the number of headers detected does not match the number of cell contents.', 'wp-e-commerce-exporter' ) . '</p>
<hr />
<h3>' . __( 'Export Log', 'wp-e-commerce-exporter' ) . '</h3>
<p>' . __( 'This prints the raw export contents and is helpful when the jQuery plugin above fails due to major formatting errors.', 'wp-e-commerce-exporter' ) . '</p>
<textarea id="export_log" wrap="off">' . $export_log . '</textarea>
<hr />
';
					echo $output;
				}

				wpsc_ce_manage_form();
				break;

			default:
				wpsc_ce_manage_form();
				break;

		}
		wpsc_ce_template_footer();

	}

	// HTML template for Export screen
	function wpsc_ce_manage_form() {

		$tab = ( isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : false );
		// If Skip Overview is set then jump to Export screen
		if( $tab == false && wpsc_ce_get_option( 'skip_overview', false ) )
			$tab = 'export';

		wpsc_ce_admin_fail_notices();

		$url = esc_url( add_query_arg( 'page', 'wpsc_ce' ) );
		if( version_compare( wpsc_get_major_version(), '3.7', '<=' ) ) {
			$wpsc_ce_url = esc_url( add_query_arg( 'page', 'wpsc_ce', 'admin.php' ) );
		} else if( version_compare( wpsc_get_major_version(), '3.8', '>=' ) ) {
			$wpsc_ce_url = add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_ce' ), 'edit.php' );
		}

		include_once( WPSC_CE_PATH . 'templates/admin/tabs.php' );

	}

	/* End of: WordPress Administration */

}
?>