<?php
/*
Plugin Name: WP e-Commerce - Store Toolkit
Plugin URI: http://www.visser.com.au/wp-ecommerce/plugins/store-toolkit/
Description: Store Toolkit - formally Nuke - includes a growing set of commonly-used WP e-Commerce administration tools aimed at web developers and store maintainers.
Version: 2.0.3
Author: Visser Labs
Author URI: http://www.visser.com.au/about/
Text Domain: wp-e-commerce-store-toolkit
License: GPL2
*/

include_once( 'includes/functions.php' );
include_once( 'includes/common.php' );

if( version_compare( wpsc_get_major_version(), '3.7', '<=' ) ) {
	include_once( 'includes/release-3_7.php' );
} else if( version_compare( wpsc_get_major_version(), '3.8', '>=' ) ) {
	include_once( 'includes/release-3_8.php' );
}

$wpsc_st = array(
	'filename' => basename( __FILE__ ),
	'dirname' => basename( dirname( __FILE__ ) ),
	'abspath' => dirname( __FILE__ ),
	'relpath' => basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ )
);

$wpsc_st['prefix'] = 'wpsc_st';
$wpsc_st['name'] = __( 'Store Toolkit for WP e-Commerce', 'wp-e-commerce-store-toolkit' );
$wpsc_st['menu'] = __( 'Store Toolkit', 'wp-e-commerce-store-toolkit' );
/**
 * For developers: Store Toolkit debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 */
$wpsc_st['debug'] = false;

function wpsc_st_i18n() {

	load_plugin_textdomain( 'wp-e-commerce-store-toolkit', null, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

}
add_action( 'init', 'wpsc_st_i18n' );

if( is_admin() ) {

	/* Start of: WordPress Administration */

	include_once( 'includes/install.php' );
	register_activation_hook( __FILE__, 'wpsc_st_install' );

	function wpsc_st_add_settings_link( $links, $file ) {

		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename( __FILE__ );
		if( $file == $this_plugin ) {
			// Settings
			$settings_link = sprintf( '<a href="%s">' . __( 'Settings', 'wp-e-commerce-store-toolkit' ) . '</a>', add_query_arg( 'page', 'wpsc_st', 'options-general.php' ) );
			array_unshift( $links, $settings_link );
			if( function_exists( 'wpsc_find_purchlog_status_name' ) ) {
				// Manage
				$manage_link = sprintf( '<a href="%s">' . __( 'Manage', 'wp-e-commerce-store-toolkit' ) . '</a>', add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_st-toolkit' ), 'edit.php' ) );
				array_unshift( $links, $manage_link );
			} else {
				// Uninstall
				$uninstall_link = sprintf( '<a href="%s#uninstall-wpecommerce">' . __( 'Uninstall', 'wp-e-commerce-store-toolkit' ) . '</a>', add_query_arg( 'page', 'wpsc_st', 'options-general.php' ) );
				array_unshift( $links, $uninstall_link );
			}
		}
		return $links;

	}
	add_filter( 'plugin_action_links', 'wpsc_st_add_settings_link', 10, 2 );

	function wpsc_st_enqueue_scripts( $hook ) {

		// Settings
		$page = 'settings_page_wpsc_st';
		if( $page == $hook ) {
			// Color Picker
			wp_enqueue_script( 'colorpicker', plugins_url( '/js/colorpicker.js', __FILE__ ), array( 'jquery' ) );
			wp_enqueue_style( 'colorpicker', plugins_url( '/templates/admin/colorpicker.css', __FILE__ ) );

			// Common
			wp_enqueue_script( 'wpsc_st-settings', plugins_url( '/templates/admin/wpsc-admin_st-settings.js', __FILE__ ), array( 'jquery' ) );
		}

		// Manage Sales
		$pages = array( 'dashboard_page_wpsc-purchase-logs', 'wpsc-product_page_wpsc_st-toolkit', 'wpsc-product_page_wpsc_st-store_status', 'settings_page_wpsc_st', 'edit.php' );
		if( in_array( $hook, $pages ) ) {
			wp_enqueue_style( 'wpsc_st_styles', plugins_url( '/templates/admin/wpsc-admin_st-toolkit.css', __FILE__ ) );
			wp_enqueue_script( 'wpsc_st-toolkit', plugins_url( '/templates/admin/wpsc-admin_st-toolkit.js', __FILE__ ), array( 'jquery' ) );
		}

	}
	add_action( 'admin_enqueue_scripts', 'wpsc_st_enqueue_scripts' );

	function wpsc_st_admin_init() {

		global $wpdb, $wpsc_st;

		// Check the User has the manage_options capability
		if( current_user_can( 'manage_options' ) == false )
			return;

		$action = ( function_exists( 'wpsc_get_action' ) ? wpsc_get_action() : false );
		if( !$action && isset( $_POST['wpsc_admin_action'] ) )
			$action = sanitize_text_field( $_POST['wpsc_admin_action'] );
		if( !$action && isset( $_GET['wpsc_admin_action'] ) )
			$action = sanitize_text_field( $_GET['wpsc_admin_action'] );
		switch( $action ) {

			case 'nuke':
				// Make sure we play nice with other WP e-Commerce and WordPress nukes
				if( !isset( $_POST['wpsc_st_nuke'] ) ) {
					$url = add_query_arg( array( 'action' => null, 'message' => __( 'A required $_POST element was not detected so the requested nuke will not proceed', 'wpsc_ce' ) ) );
					wp_redirect( $url );
					exit();
				}

				// We need to verify the nonce.
				check_admin_referer( 'nuke', 'wpsc_st_nuke' );

				if( !ini_get( 'safe_mode' ) )
					set_time_limit( 0 );

				// WP e-Commerce
				if( isset( $_POST['wpsc_st_products'] ) )
					wpsc_st_clear_dataset( 'products' );
				if( isset( $_POST['wpsc_st_product_variations'] ) )
					wpsc_st_clear_dataset( 'variations' );
				if( isset( $_POST['wpsc_st_variation_sets'] ) )
					wpsc_st_clear_dataset( 'variation_sets' );
				if( isset( $_POST['wpsc_st_product_tags'] ) )
					wpsc_st_clear_dataset( 'tags' );
				if( isset( $_POST['wpsc_st_categories'] ) ) {
					$categories = $_POST['wpsc_st_categories'];
					wpsc_st_clear_dataset( 'categories', $categories );
				} else if( isset( $_POST['wpsc_st_product_categories'] ) ) {
					wpsc_st_clear_dataset( 'categories' );
				}
				if( isset( $_POST['wpsc_st_product_images'] ) )
					wpsc_st_clear_dataset( 'images' );
				if( isset( $_POST['wpsc_st_product_files'] ) )
					wpsc_st_clear_dataset( 'files' );
				if( isset( $_POST['wpsc_st_sales_orders'] ) )
					wpsc_st_clear_dataset( 'orders' );
				if( isset( $_POST['wpsc_st_coupons'] ) )
					wpsc_st_clear_dataset( 'coupons' );

				// 3rd Party
				if( isset( $_POST['wpsc_st_wishlist'] ) )
					wpsc_st_clear_dataset( 'wishlist' );
				if( isset( $_POST['wpsc_st_enquiries'] ) )
					wpsc_st_clear_dataset( 'enquiries' );
				if( isset( $_POST['wpsc_st_creditcards'] ) )
					wpsc_st_clear_dataset( 'credit-cards' );
				if( isset( $_POST['wpsc_st_customfields'] ) )
					wpsc_st_clear_dataset( 'custom-fields' );
				if( isset( $_POST['wpsc_st_previewfiles'] ) )
					wpsc_st_clear_dataset( 'preview-files' );

				// WordPress
				if( isset( $_POST['wpsc_st_posts'] ) )
					wpsc_st_clear_dataset( 'posts' );
				if( isset( $_POST['wpsc_st_post_categories'] ) )
					wpsc_st_clear_dataset( 'post_categories' );
				if( isset( $_POST['wpsc_st_post_tags'] ) )
					wpsc_st_clear_dataset( 'post_tags' );
				if( isset( $_POST['wpsc_st_links'] ) )
					wpsc_st_clear_dataset( 'links' );
				if( isset( $_POST['wpsc_st_comments'] ) )
					wpsc_st_clear_dataset( 'comments' );

				$message = __( 'Chosen WP e-Commerce details have been permanently erased from your store.', 'wp-e-commerce-store-toolkit' );
				wpsc_st_admin_notice( $message );
				break;

			case 'tools':
				// We need to verify the nonce.
				check_admin_referer( 'tools', 'wpsc_st_tools' );

				if( isset( $_POST['maximum_cart_quantity'] ) ) {
					$maximum_cart_quantity = $_POST['maximum_cart_quantity'];
					wpsc_st_update_option( 'maximum_cart_quantity', $maximum_cart_quantity );

					$message = __( 'Settings saved.', 'wp-e-commerce-store-toolkit' );
					wpsc_st_admin_notice( $message );
				}
				break;

			case 'relink-pages':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'wpsc_st_relink_pages' ) ) {
					$product_page = wpsc_st_get_page_by_shortcode( '[productspage]' );
					$checkout = wpsc_st_get_page_by_shortcode( '[shoppingcart]' );
					$transaction_results = wpsc_st_get_page_by_shortcode( '[transactionresults]' );
					$my_account = wpsc_st_get_page_by_shortcode( '[userlog]' );
					if( $product_page )
						update_option( 'product_list_url', get_bloginfo( 'url' ) . "/?page_id=" . $product_page );
					if( $checkout )
						update_option( 'shopping_cart_url', get_bloginfo( 'url' ) . "/?page_id=" . $checkout );
					if( $checkout )
						update_option( 'checkout_url', get_bloginfo( 'url' ) . "/?page_id=" . $checkout );
					if( $transaction_results )
						update_option( 'transact_url', get_bloginfo( 'url' ) . "/?page_id=" . $transaction_results );
					if( $my_account )
						update_option( 'user_account_url', get_bloginfo( 'url' ) . "/?page_id=" . $my_account );

					$message = __( 'Default WP e-Commerce Pages have been restored.', 'wp-e-commerce-store-toolkit' );
					wpsc_st_admin_notice( $message );
				}
				break;

			case 'relink-existing-preregistered-sales':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'wpsc_st_relink_existing_preregistered_sales' ) ) {
					$sales = wpsc_st_get_unlinked_sales();
					$size = count( $sales );
					if( $sales ) {
						$adjusted_sales = 0;
						foreach( $sales as $sale ) {
							$sale_email = wpsc_st_get_email_from_sale( $sale->ID );
							if( $sale_email ) {
								$sale_user = get_user_by( 'email', $sale_email );
								if( $sale_user ) {
									$wpdb->update( $wpdb->prefix . 'wpsc_purchase_logs', array(
										'user_ID' => $sale_user->ID
									), array( 'id' => $sale->ID ) );
								}
								$adjusted_sales++;
							}
						}
					}
					$notice_status = '';
					if( $adjusted_sales > 0 ) {
						$message = sprintf( __( '%s of %s unlinked Sale\'s from pre-registered Users have been re-linked.', 'wp-e-commerce-store-toolkit' ), '<strong>' . $adjusted_sales . '</strong>', '<strong>' . $size . '</strong>' );
					} else {
						$message = __( 'No existing Sales from pre-registered Users have been re-linked.', 'wp-e-commerce-store-toolkit' );
						$notice_status = 'error';
					}
					wpsc_st_admin_notice( $message, $notice_status );
				}
				break;

			case 'reset-file-downloads-sale':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'wpsc_st_reset_file_downloads_sale' ) ) {
					if( version_compare( wpsc_get_minor_version(), '3.8.8', '>=' ) )
						$purchase_id = (int)$_GET['id'];
					else
						$purchase_id = (int)$_GET['purchaselog_id'];
					$file_downloads_sql = $wpdb->prepare( "SELECT `id`, `downloads` FROM `" . $wpdb->prefix . "wpsc_download_status` WHERE `purchid` = %d", $purchase_id );
					$file_downloads = $wpdb->get_results( $file_downloads_sql );
					$notice_status = '';
					if( $file_downloads ) {
						$max_downloads = get_option( 'wpsc_max_downloads' );
						if( $max_downloads ) {
							foreach( $file_downloads as $file_download ) {
								if( $file_download->downloads < $max_downloads ) {
									$wpdb->update( $wpdb->prefix . 'wpsc_download_status', array(
										'downloads' => $max_downloads
									), array( 'id' => $file_download->id ) );
								}
							}
							$message = __( 'File Downloads have been reset for this Sale.', 'wp-e-commerce-store-toolkit' );
						} else {
							$message = __( 'Max downloads per file has not been set - or is set to 0 - within Settings > Store > Admin screen.', 'wp-e-commerce-store-toolkit' );
							$notice_status = 'error';
						}
					} else {
						$message = __( 'No File Downloads are assigned to this Sale.', 'wp-e-commerce-store-toolkit' );
						$notice_status = 'error';
					}
					wpsc_st_admin_notice( $message, $notice_status );
				}
				break;

			case 'demo':
				// We need to verify the nonce.
				check_admin_referer( 'demo', 'wpsc_st_demo' );

				$options = false;
				if( isset( $_POST['options'] ) ) {
					$options = $_POST['options'];
					if( isset( $options['demo_store'] ) )
						$options['demo_store'] = 1;
					else
						$options['demo_store'] = 0;
					if( $options ) {
						foreach( $options as $key => $option )
							wpsc_st_update_option( $key, $option );
					}
				}

				$message = __( 'Settings saved.', 'wp-e-commerce-store-toolkit' );
				wpsc_st_admin_notice( $message );
				break;

			case 'uninstall':
				// We need to verify the nonce.
				check_admin_referer( 'uninstall', 'wpsc_st_uninstall' );

				global $uninstall;

				$uninstall = new stdClass();
				$uninstall->log = '';

				$uninstall->log .= "<br />" . __( 'Removing WP e-Commerce tables...', 'wp-e-commerce-store-toolkit' );
				$wpsc_tables = array();
				$wpsc_tables[] = array( 'label' => __( 'Also Bought', 'wp-e-commerce-store-toolkit' ), 'table' => 'wpsc_also_bought' );
				$wpsc_tables[] = array( 'label' => __( 'Cart Contents', 'wp-e-commerce-store-toolkit' ), 'table' => 'wpsc_cart_contents' );
				$wpsc_tables[] = array( 'label' => __( 'Checkout Forms', 'wp-e-commerce-store-toolkit' ), 'table' => 'wpsc_checkout_forms' );
				$wpsc_tables[] = array( 'label' => __( 'Claimed Stock', 'wp-e-commerce-store-toolkit' ), 'table' => 'wpsc_claimed_stock' );
				$wpsc_tables[] = array( 'label' => __( 'Coupons', 'wp-e-commerce-store-toolkit' ), 'table' => 'wpsc_coupon_codes' );
				$wpsc_tables[] = array( 'label' => __( 'Currency List', 'wp-e-commerce-store-toolkit' ), 'table' => 'wpsc_currency_list' );
				$wpsc_tables[] = array( 'label' => __( 'Download Status', 'wp-e-commerce-store-toolkit' ), 'table' => 'wpsc_download_status' );
				$wpsc_tables[] = array( 'label' => __( 'Meta', 'wp-e-commerce-store-toolkit' ), 'table' => 'wpsc_meta' );
				$wpsc_tables[] = array( 'label' => __( 'Product Rating', 'wp-e-commerce-store-toolkit' ), 'table' => 'wpsc_product_rating' );
				$wpsc_tables[] = array( 'label' => __( 'Purchase Logs', 'wp-e-commerce-store-toolkit' ), 'table' => 'wpsc_purchase_logs' );
				$wpsc_tables[] = array( 'label' => __( 'Tax Regions', 'wp-e-commerce-store-toolkit' ), 'table' => 'wpsc_region_tax' );
				$wpsc_tables[] = array( 'label' => __( 'Submited Form Data', 'wp-e-commerce-store-toolkit' ), 'table' => 'wpsc_submited_form_data' );
				$size = count( $wpsc_tables );
				for( $i = 0; $i < $size; $i++ ) {
					// Check that the WP e-Commerce table exists
					if( wpsc_st_check_table_exists( $wpsc_tables[$i]['table'] ) ) {
						// $wpdb->query( "DROP TABLE `" . $wpdb->prefix . $wpsc_tables[$i]['table'] . "`" );
						$uninstall->log .= "<br />>>> " . sprintf( __( 'Removed %s (%s)', 'wp-e-commerce-store-toolkit' ), $wpsc_tables[$i]['label'], $wpdb->prefix . $wpsc_tables[$i]['table'] );
					}
				}

				$uninstall->log .= "<br /><br />" . __( 'Removing WP e-Commerce details...', 'wp-e-commerce-store-toolkit' );
				$wpsc_dataset = array();
				$wpsc_dataset[] = array( 'label' => __( 'Products', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'products' );
				$wpsc_dataset[] = array( 'label' => __( 'Product Variations', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'variations' );
				$wpsc_dataset[] = array( 'label' => __( 'Product Tags', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'tags' );
				$wpsc_dataset[] = array( 'label' => __( 'Product Categories', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'categories' );
				$wpsc_dataset[] = array( 'label' => __( 'Product Images', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'images' );
				$wpsc_dataset[] = array( 'label' => __( 'Product Files', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'files' );
				$wpsc_dataset[] = array( 'label' => __( 'Sales', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'orders' );
				$wpsc_dataset[] = array( 'label' => __( 'Coupons', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'coupons' );
				$wpsc_dataset[] = array( 'label' => __( 'Add to Wishlist', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'wishlist' );
				$wpsc_dataset[] = array( 'label' => __( 'Enquiries', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'enquiries' );
				$wpsc_dataset[] = array( 'label' => __( 'Offline Payments', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'credit-cards' );
				$wpsc_dataset[] = array( 'label' => __( 'Custom Fields', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'custom-fields' );
				$wpsc_dataset[] = array( 'label' => __( 'Preview Files', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'preview-files' );
				$wpsc_dataset[] = array( 'label' => __( 'Plugin Pages', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'wpsc_pages' );
				$wpsc_dataset[] = array( 'label' => __( 'Plugin Options', 'wp-e-commerce-store-toolkit' ), 'dataset' => 'wpsc_options' );
				$size = count( $wpsc_dataset );
				for( $i = 0; $i < $size; $i++ ) {
					if( $wpsc_dataset[$i]['dataset'] ) {
						// wpsc_st_clear_dataset( $wpsc_dataset[$i] );
						$uninstall->log .= "<br />>>> " . sprintf( __( 'Removed %s', 'wp-e-commerce-store-toolkit' ), $wpsc_dataset[$i]['label'] 	);
					}
				}
				$message = __( 'Uninstall complete.', 'wp-e-commerce-store-toolkit' );
				wpsc_st_admin_notice( $message );
				break;

			case 'wpsc_update_session_id':
				// We need to verify the nonce.
				check_admin_referer( 'update_session_id', 'wpsc_st_update_session_id' );

				global $wpdb;

				$session_id = $_POST['session_id'];
				$purchase_id = $_POST['purchase_id'];
				if( isset( $session_id ) && $purchase_id )
					$wpdb->update( $wpdb->prefix . 'wpsc_purchase_logs', array(
						'sessionid' => $session_id
					), array( 'id' => $purchase_id ) );
				break;

			case 'update':
				// We need to verify the nonce.
				check_admin_referer( 'update', 'wpsc_st_update' );

				$options = false;
				if( isset( $_POST['options'] ) )
					$options = $_POST['options'];
				if( $options ) {
					foreach( $options as $key => $option )
						wpsc_st_update_option( $key, $option );
				}

				$message = __( 'Settings saved.', 'wp-e-commerce-store-toolkit' );
				wpsc_st_admin_notice( $message );
				break;

			case 'clear-claimed_stock':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'wpsc_st_clear_claimed_stock' ) ) {

					global $wpdb;

					$wpdb->query( "TRUNCATE TABLE `" . $wpdb->prefix . "wpsc_claimed_stock`" );
					$message = __( 'The \'claimed stock\' table has been emptied.', 'wp-e-commerce-store-toolkit' );
					wpsc_st_admin_notice( $message );
				}
				break;

			case 'fix-wpsc_version':
				// We need to verify the nonce.
				if( !empty( $_GET ) && check_admin_referer( 'wpsc_st_fix_wpsc_version' ) ) {
					$notice_status = '';
					if( version_compare( wpsc_get_major_version(), '3.8', '>=' ) && ( WPSC_VERSION == '3.7' ) ) {
						update_option( 'wpsc_version', '3.7' );
						$message = __( 'WordPress option \'wpsc_version\' has been repaired.', 'wp-e-commerce-store-toolkit' );
					} else {
						$notice_status = 'error';
						$message = __( 'WordPress option \'wpsc_version\' did not require attention.', 'wp-e-commerce-store-toolkit' );
					}
					wpsc_st_admin_notice( $message, $notice_status );
				}
				break;

		}

	}
	add_action( 'admin_init', 'wpsc_st_admin_init' );

	function wpsc_st_store_admin_menu() {

		add_submenu_page( 'wpsc_sm', __( 'Store Toolkit', 'wp-e-commerce-store-toolkit' ), __( 'Store Toolkit', 'wp-e-commerce-store-toolkit' ), 'manage_options', 'wpsc_st', 'wpsc_st_html_toolkit' );
		remove_filter( 'wpsc_additional_pages', 'wpsc_st_add_modules_manage_pages', 10 );
		remove_action( 'admin_menu', 'wpsc_st_admin_menu', 10 );

	}
	add_action( 'wpsc_sm_store_admin_subpages', 'wpsc_st_store_admin_menu' );

	function wpsc_st_default_file_downloads_html_page() {

		global $wpsc_st, $wpdb;

		$post_type = 'wpsc-product-file';

		$mime_types_all = new stdClass;
		$mime_types_all->post_mime_type = __( 'All', 'wp-e-commerce-store-toolkit' );
		$mime_types_count = wp_count_posts( $post_type );
		$mime_types_all->count = $mime_types_count->inherit;
		if( $mime_types_all->count ) {

			$current_post_mime = false;
			if( isset( $_GET['post_mime_type'] ) ) {
				$current_post_mime = $_GET['post_mime_type'];
				if( $current_post_mime == 'all' )
					$current_post_mime = false;
			}

			if( $current_post_mime ) {
				$args = array(
					'post_type' => $post_type,
					'post_mime_type' => wpsc_st_format_post_mime_type_filter( $current_post_mime, 'expand' ),
					'post_status' => 'inherit',
					'numberposts' => -1
				);
			} else {
				$args = array(
					'post_type' => $post_type,
					'post_status' => 'inherit',
					'numberposts' => -1
				);
			}
			$files = get_posts( $args );
			$mime_types = wpsc_st_get_mime_types( $post_type );
			$show_mime_ext = true;
			if( $mime_types ) {
				$i = 1;
				array_unshift( $mime_types, $mime_types_all );
				$size = count( $mime_types );
				foreach( $mime_types as $key => $mime_type ) {
					$mime_types[$key]->current = false;
					if( empty( $mime_type->post_mime_type ) ) {
						$mime_types[$key]->filter = 'other';
						$mime_types[$key]->post_mime_type = __( 'N/A', 'wp-e-commerce-store-toolkit' );
					} else if( $mime_type->post_mime_type == __( 'All', 'wp-e-commerce-store-toolkit' ) ) {
						if( !$current_post_mime )
							$mime_types[$key]->current = true;
						$mime_types[$key]->filter = 'all';
					} else {
						$mime_types[$key]->filter = wpsc_st_format_post_mime_type_filter( $mime_type->post_mime_type );
						$mime_types[$key]->post_mime_type = wpsc_st_format_post_mime_type( $mime_type->post_mime_type, $show_mime_ext );
					}
					if( $mime_type->filter == 'all' )
						$mime_type->filter = false;
					if( $mime_type->filter )
						$mime_types[$key]->filter_url = add_query_arg( 'post_mime_type', $mime_type->filter );
					else
						$mime_types[$key]->filter_url = add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_st-file_downloads' ), 'edit.php' );
					if( $current_post_mime && ( $mime_type->filter == $current_post_mime ) )
						$mime_types[$key]->current = true;
					$mime_types[$key]->i = $i;
					$i++;
				}
			}
			if( $files ) {
				foreach( $files as $key => $file ) {
					$files[$key]->post_mime_type = get_post_mime_type( $file->ID );
					if( !$file->post_mime_type )
						$files[$key]->post_mime_type = __( 'N/A', 'wp-e-commerce-store-toolkit' );
					$files[$key]->media_icon = wp_get_attachment_image( $file->ID, array( 80, 60 ), true );
					$author_name = get_user_by( 'id', $file->post_author );
					$parent_post = get_post( $file->post_parent );
					if( $parent_post ) {
						$files[$key]->post_parent_title = $parent_post->post_title;
					} else {
						$files[$key]->post_parent = '';
						$files[$key]->post_parent_title = __( 'Unassigned', 'wp-e-commerce-store-toolkit' );
					}
					$files[$key]->post_author_name = $author_name->display_name;
					$t_time = strtotime( $file->post_date, current_time( 'timestamp' ) );
					$time = get_post_time( 'G', true, $file->ID, false );
					if( ( abs( $t_diff = time() - $time ) ) < 86400 )
						$files[$key]->post_date = sprintf( __( '%s ago' ), human_time_diff( $time ) );
					else
						$files[$key]->post_date = mysql2date( __( 'Y/m/d' ), $file->post_date );
				}
			}
		}
		include_once( 'templates/admin/wpsc-admin_st-file_downloads.php' );

	}

	function wpsc_st_default_toolkit_html_page() {

		global $wpsc_st, $wpdb;

		$tab = false;
		if( isset( $_GET['tab'] ) )
			$tab = $_GET['tab'];

		include_once( 'templates/admin/wpsc-admin_st-toolkit.php' );

	}

	function wpsc_st_html_file_downloads() {

		wpsc_st_template_header( __( 'File Downloads', 'wp-e-commerce-store-toolkit' ), 'upload' );
		$action = wpsc_get_action();
		switch( $action ) {

			default:
				wpsc_st_default_file_downloads_html_page();
				break;

		}
		wpsc_st_template_footer();

	}

	function wpsc_st_html_store_status() {

		wpsc_st_template_header( __( 'Store Status', 'wp-e-commerce-store-toolkit' ), 'admin' );
		wpsc_st_store_status_html_page();
		wpsc_st_template_footer();

	}

	function wpsc_st_store_status_html_page() {

		global $wpsc_st;

		$gold_cart = wpsc_st_gold_cart_status();
		if( get_bloginfo( 'version' ) < '3.4' )
			$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
		else
			$theme_data = wp_get_theme();
		$theme = array( 'name' => $theme_data['Name'], 'version' => $theme_data['Version'], 'author' => $theme_data['Author'], 'author_url' => $theme_data['AuthorURI'] );
		$plugins = get_plugins();
		$plugins_option = get_option( 'active_plugins', array() );
		$active_plugins = array();
		foreach( $plugins as $plugin_path => $plugin ) {
			if( !in_array( $plugin_path, $plugins_option ) )
				continue;
			$active_plugins[] = array( 'name' => $plugin['Name'], 'version' => $plugin['Version'], 'url' => $plugin['PluginURI'], 'author' => $plugin['Author'], 'author_url' => $plugin['PluginURI'], 'raw' => print_r( $plugin, true ) );
		}
		$max_upload = round( wp_max_upload_size() / 1024 / 1024, 2 ) . 'MB';
		$max_post = (int)( ini_get( 'post_max_size' ) ) . 'MB';
		$memory_limit = (int)( ini_get( 'memory_limit' ) ) . 'MB';
		$max_execution_time = ini_get( 'max_execution_time' );
		$store_email = get_option( 'purch_log_email', true );
		$base_country = get_option( 'base_country', true );

		$wpsc_pages = wpsc_st_get_wpsc_pages();

		include_once( 'templates/admin/wpsc-admin_st-store_status.php' );

	}

	function wpsc_st_html_settings() {

		global $wpsc_st;

		$action = wpsc_get_action();
		wpsc_st_template_header();
		switch( $action ) {

			case 'update':
				wpsc_st_options_form();
				break;

			case 'uninstall':
				wpsc_st_uninstall_form();
				break;

			default:
				wpsc_st_options_form();
				break;

		}
		wpsc_st_template_footer();

	}

	function wpsc_st_uninstall_form() {

		global $uninstall;

		include( 'templates/admin/wpsc-admin_st-uninstall.php' );

	}

	function wpsc_st_options_form() {

		global $wpsc_purchlog_statuses;

		$sale_statuses = wpsc_st_add_colours_to_sale_statuses( $wpsc_purchlog_statuses );
		if( !$sale_status_background = wpsc_st_get_option( 'sale_status_background' ) ) {
			$sale_status_background = array();
			foreach( $sale_statuses as $sale_status )
				$sale_status_background[$sale_status['internalname']] = $sale_status['default_background'];
		}
		if( !$sale_status_border = wpsc_st_get_option( 'sale_status_border' ) ) {
			$sale_status_border = array();
			foreach( $sale_statuses as $sale_status )
				$sale_status_border[$sale_status['internalname']] = $sale_status['default_border'];
		}
		$options = wpsc_st_get_options();

		include( 'templates/admin/wpsc-admin_st-settings.php' );

	}

	function wpsc_st_html_toolkit() {

		global $wpdb;

		wpsc_st_template_header();
		wpsc_st_support_donate();
		$action = wpsc_get_action();
		switch( $action ) {

			case 'nuke':
				wpsc_st_default_toolkit_html_page();
				break;

			case 'relink-existing-preregistered-sales':
				wpsc_st_default_toolkit_html_page();
				break;

			case 'relink-pages':
				wpsc_st_default_toolkit_html_page();
				break;

			case 'fix-wpsc_version':
				wpsc_st_default_toolkit_html_page();
				break;

			case 'demo':
				wpsc_st_default_toolkit_html_page();
				break;

			case 'clear-claimed_stock':
				wpsc_st_default_toolkit_html_page();
				break;

			default:
				wpsc_st_default_toolkit_html_page();
				break;

		}
		wpsc_st_template_footer();

	}

	/* End of: WordPress Administration */

} else {

	/* Start of: Storefront */

	function wpsc_st_print_scripts() {

		global $wpsc_st;

		$output = '';
		$addtocart = wpsc_st_get_option( 'addtocart_label', __( 'Add To Cart', 'wpsc' ) );
		if( $addtocart <> __( 'Add To Cart', 'wpsc' ) ) {
			$output = '
<!-- Store Toolkit: Add To Cart -->
<script type="text/javascript">
var $j = jQuery.noConflict();

$j(function(){

	$j(\'.wpsc_buy_button_container input.wpsc_buy_button\').each(function() {
		if( $j(this).attr(\'onclick\') == undefined ) {
			$j(this).val( \'' . $addtocart . '\' );
		}
	});

});
</script>
';
		}
		echo $output;

	}
	add_action( 'wp_print_footer_scripts' , 'wpsc_st_print_scripts' );

	include_once( 'includes/template.php' );

	/* End of: Storefront */

}

/* Start of: Common */

if( function_exists( 'wpsc_st_get_option' ) ) {
	if( wpsc_st_get_option( 'maximum_cart_quantity', 10000 ) <> 10000 )
		add_filter( 'wpsc_product_max_cart_quantity', 'wpsc_st_override_max_cart_quantity', 10, 2 );
}

/* End of: Common */
?>