<?php
/*
Plugin Name: WP e-Commerce - Custom Fields
Plugin URI: http://www.visser.com.au/wp-ecommerce/plugins/custom-fields/
Description: Add and manage custom Product meta details within WP e-Commerce.
Version: 1.5.7
Author: Visser Labs
Author URI: http://www.visser.com.au/about/
Contributor: Ryan Waggoner
Contributor URI: http://ryanwaggoner.com/
Contributor: Kleber Lopes da Silva
Contributor URI: http://gameplaceholder.blogspot.com/
License: GPL2
*/

load_plugin_textdomain( 'wpsc_cf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

include_once( 'includes/functions.php' );

include_once( 'includes/common.php' );

if( version_compare( wpsc_get_major_version(), '3.7', '<=' ) ) {
	include_once( 'includes/release-3_7.php' );
} else if( version_compare( wpsc_get_major_version(), '3.8', '>=' ) ) {
	include_once( 'includes/release-3_8.php' );
}

define( 'WPSC_CF_FILE', __FILE__ );
define( 'WPSC_CF_DIRNAME', basename( dirname( __FILE__ ) ) );
define( 'WPSC_CF_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSC_CF_PREFIX', 'wpsc_cf' );

if( is_admin() ) {

	/* Start of: WordPress Administration */

	include_once( WPSC_CF_PATH . 'includes/install.php' );
	register_activation_hook( __FILE__, 'wpsc_cf_install' );

	include_once( WPSC_CF_PATH . 'includes/update.php' );

	function wpsc_cf_add_settings_link( $links, $file ) {

		static $this_plugin;
		if( !$this_plugin ) $this_plugin = plugin_basename( __FILE__ );
		if( $file == $this_plugin ) {
			$settings_link = sprintf( '<a href="%s">' . __( 'Manage', 'wpsc_cf' ) . '</a>', add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_cf' ), 'edit.php' ) );
			array_unshift( $links, $settings_link );
		}
		return $links;

	}
	add_filter( 'plugin_action_links', 'wpsc_cf_add_settings_link', 10, 2 );

	function wpsc_cf_html_page() {

		global $wpdb, $wpsc_cf;

		$title = __( 'Attributes ', 'wpsc_cf' );
		$action = wpsc_get_action();
		if( !in_array( $action, array( 'new', 'edit' ) ) )
			$title .= '<a href="' . add_query_arg( array( 'action' => 'new' ) ) . '" class="add-new-h2">' . __( 'Add New', 'wpsc_cf' ) . '</a>';
		wpsc_cf_template_header( $title );
		switch( $action ) {

			case 'delete':
				$id = (int)$_GET['id'];
				$data = wpsc_cf_get_option( 'data' );
				if( !empty( $data ) ) {
					$data = maybe_unserialize( $data );
					unset( $data[$id] );
					$data = serialize( $data );
					update_option( 'wpsc_cf_data', $data );
					unset( $data );
				}

				$message = __( 'Attribute deleted', 'wpsc_cf' );
				$output = '<div class="updated settings-error"><p><strong>' . $message . '.</strong></p></div>';
				echo $output;

				wpsc_cf_manage_form();
				break;

			case 'edit-confirm':
				$id = $_POST['custom-field-id'];
				$name = $_POST['custom-field-name'];
				$slug = $_POST['custom-field-slug'];
				$type = $_POST['custom-field-type'];
				$order = $_POST['custom-field-order'];
				$prefix = $_POST['custom-field-prefix'];
				$suffix = $_POST['custom-field-suffix'];
				$show_name = $_POST['custom-field-show-name'];
				if( isset( $id ) && $name && $slug && $type ) {
					$options = '';
					if( isset( $_POST['custom-field-options'] ) )
						$options = $_POST['custom-field-options'];
					$description = $_POST['custom-field-description'];
					$field = array();
					$field[] = array(
						'name' => $name, 
						'slug' => $slug, 
						'type' => $type, 
						'options' => $options, 
						'order' => $order, 
						'description' => $description,
						'prefix' => $prefix,
						'suffix' => $suffix,
						'show_name' => $show_name
					);
					$data = unserialize( wpsc_cf_get_option( 'data' ) );
					$data[$id]['name'] = $name;
					$data[$id]['slug'] = $slug;
					$data[$id]['type'] = $type;
					$data[$id]['order'] = $order;
					$data[$id]['prefix'] = $prefix;
					$data[$id]['suffix'] = $suffix;
					$data[$id]['show_name'] = $show_name;
					if( $type == 'dropdown' || $type == 'checkbox' || $type == 'radio' )
						$data[$id]['options'] = $options;
					$data[$id]['description'] = $description;
					$data = serialize( $data );

					wpsc_cf_update_option( 'data', $data );
					unset( $data );

					$message = __( 'Attribute updated', 'wpsc_cf' );
					$output = '<div class="updated settings-error"><p><strong>' . $message . '.</strong></p></div>';
				} else {
					$message = '<strong>' . __( 'ERROR', 'wpsc_cf' ) . '</strong>: ' . __( 'A required field was not filled. Please ensure required fields are filled.', 'wpsc_cf' );
					$output = '<div class="error settings-error"><p>' . $message . '</strong></p></div>';
				}
				echo $output;

				wpsc_cf_manage_form();
				break;

			case 'new-confirm':
				$name = $_POST['custom-field-name'];
				$slug = $_POST['custom-field-slug'];
				$type = $_POST['custom-field-type'];
				$order = $_POST['custom-field-order'];
				$prefix = $_POST['custom-field-prefix'];
				$suffix = $_POST['custom-field-suffix'];
				$show_name = $_POST['custom-field-show-name'];
				if( $name && $type ) {
					if( !$slug ) {
						$slug_filters = array( '(', ')' );
						$slug = str_replace( $slug_filters, '', $name );
						$slug = strtolower( str_replace( ' ', '-', $slug ) );
					}
					$description = $_POST['custom-field-description'];
					if( wpsc_cf_get_option( 'data' ) ) {
						$data = unserialize( wpsc_cf_get_option( 'data' ) );
						$field = array(
							'name' => $name, 
							'slug' => $slug, 
							'type' => $type, 
							'order' => $order, 
							'description' => $description,
							'prefix' => $prefix,
							'suffix' => $suffix,
							'show_name' => $show_name
						);
						$data[] = $field;
						$data = serialize( $data );
						update_option( 'wpsc_cf_data', $data );
					} else {
						$data = array();
						$data[] = array(
							'name' => $name, 
							'slug' => $slug, 
							'type' => $type, 
							'order' => $order, 
							'description' => $description,
							'prefix' => $prefix,
							'suffix' => $suffix,
							'show_name' => $show_name
						);
						$data = serialize( $data );
						update_option( 'wpsc_cf_data', $data );
					}
					unset( $data );

					if( $type == 'dropdown' || $type == 'checkbox' || $type == 'radio' )
						$message = __( 'Attribute saved, you\'ll now need to define the Options for this field', 'wpsc_cf' );
					else
						$message = __( 'Attribute saved', 'wpsc_cf' );
					$output = '<div class="updated settings-error"><p><strong>' . $message . '.</strong></p></div>';
				} else {
					$message = '<strong>' . __( 'ERROR', 'wpsc_cf' ) . '</strong>: ' . __( 'A required field was not filled. Please ensure required fields are filled.', 'wpsc_cf' ) . '.</strong>';
					$output = '<div class="error settings-error"><p>' . $message . '</p></div>';
				}
				echo $output;

				wpsc_cf_manage_form();
				break;

			case 'edit':
			case 'new':
				$field = array(
					'name' => null,
					'slug' => null,
					'type' => null,
					'options' => null,
					'order' => null,
					'prefix' => null,
					'suffix' => null,
					'show_name' => null,
					'description' => null
				);
				if( $action == 'edit' ) {
					$id = (int)$_GET['id'];
					$data = wpsc_cf_get_option( 'data' );
					if( !empty( $data ) ) {
						$data = maybe_unserialize( $data );
						if( !isset( $data[$id]['options'] ) )
							$data[$id]['options'] = '';
						$field = $data[$id];
					}
				}

				$title = __( 'Add New Attribute', 'wpsc_cf' );
				if( $action == 'edit' )
					$title = __( 'Edit Attribute', 'wpsc_cf' );
				$options = wpsc_cf_custom_field_types();

				include_once( WPSC_CF_PATH . 'templates/admin/wpsc-admin_cf_manage-detail.php' );

				break;

			default:
				wpsc_cf_manage_form();
				break;

		}
		wpsc_cf_template_footer();

	}

	function wpsc_cf_html_settings() {

		global $wpsc_cf;

		$title = __( 'Custom Fields', 'wpsc_cf' );
		wpsc_cf_template_header( $title );
		$action = wpsc_get_action();
		switch( $action ) {

			case 'update':
				$options = array();
				$options['position'] = $_POST['position'];
				$options['layout'] = $_POST['layout'];
				$options['display_title'] = $_POST['display_title'];
				$options['title_text'] = $_POST['title_text'];
				foreach( $options as $key => $option )
					wpsc_cf_update_option( $key, $option );

				$message = __( 'Settings updated', 'wpsc_cf' );
				$output = '<div class="updated settings-error"><p><strong>' . $message . '.</strong></p></div>';
				echo $output;

				wpsc_cf_settings_form();
				break;

			default:
				wpsc_cf_settings_form();
				break;

		}
		wpsc_cf_template_footer();

	}

	function wpsc_cf_manage_form() {

		global $wpsc_cf;

		$data = wpsc_cf_get_option( 'data' );
		if( $data ) {
			if( wpsc_cf_is_serialized( $data ) )
				$data = unserialize( $data );
			$data = wpsc_cf_custom_field_sort( $data, 'order' );
		}
		$i = 0;

		include_once( WPSC_CF_PATH . 'templates/admin/wpsc-admin_cf_manage.php' );

	}

	function wpsc_cf_settings_form() {

		$positions = wpsc_productpage_positions();

		$layouts = array();
		$layouts[] = array( 'filename' => 'table.php', 'label' => __( 'Table', 'wpsc_cf' ) );
		$layouts[] = array( 'filename' => 'list-ordered.php', 'label' => __( 'List - Ordered', 'wpsc_cf' ) );
		$layouts[] = array( 'filename' => 'list-unordered.php', 'label' => __( 'List - Unordered', 'wpsc_cf' ) );
		$layouts = apply_filters( 'wpsc_cf_layouts', $layouts );

		$position = wpsc_cf_get_option( 'position' );
		$selected_layout = wpsc_cf_get_option( 'layout' );
		$display_title = wpsc_cf_get_option( 'display_title' );
		$title_text = wpsc_cf_get_option( 'title_text' );

		include_once( WPSC_CF_PATH . 'templates/admin/wpsc-admin_cf_settings.php' );

	}

	/* End of: WordPress Administration */

} else {

	/* Start of: Storefront */

	include_once( WPSC_CF_PATH . 'includes/template.php' );
	include_once( WPSC_CF_PATH . 'includes/legacy.php' );

	$position = wpsc_cf_get_option( 'position', 'wpsc_product_addon_after_descr' );
	add_action( $position, 'wpsc_cf_init' );

	/* End of: Storefront */

}
?>