<?php
include_once( WPSC_CE_PATH . 'includes/product.php' );
include_once( WPSC_CE_PATH . 'includes/category.php' );
include_once( WPSC_CE_PATH . 'includes/tag.php' );
include_once( WPSC_CE_PATH . 'includes/order.php' );
include_once( WPSC_CE_PATH . 'includes/customer.php' );
include_once( WPSC_CE_PATH . 'includes/coupon.php' );

include_once( WPSC_CE_PATH . 'includes/formatting.php' );

include_once( WPSC_CE_PATH . 'includes/export-csv.php' );

if( is_admin() ) {

	/* Start of: WordPress Administration */

	include_once( WPSC_CE_PATH . 'includes/admin.php' );

	function wpsc_ce_detect_non_wpsc_install() {

		if( !wpsc_is_wpsc_activated() && ( wpsc_is_jigo_activated() || wpsc_is_woo_activated() ) ) {
			$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/usage/';
			$message = __( 'We have detected another e-Commerce Plugin than WP e-Commerce activated, please check that you are using Store Exporter Deluxe for the correct platform.', 'wp-e-commerce-exporter' ) . '<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'wp-e-commerce-exporter' ) . '</a>';
			wpsc_ce_admin_notice( $message, 'error', 'plugins.php' );
		}
		wpsc_ce_plugin_page_notices();

	}

	// Displays a HTML notice when a WordPress or Store Exporter error is encountered
	function wpsc_ce_admin_fail_notices() {

		$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/usage/';

		// If the failed flag is set then prepare for an error notice
		if( isset( $_GET['failed'] ) ) {
			$message = '';
			if( isset( $_GET['message'] ) )
				$message = urldecode( $_GET['message'] );
			if( $message )
				$message = __( 'A WordPress or server error caused the exporter to fail, the exporter was provided with a reason: ', 'wp-e-commerce-exporter' ) . '<em>' . $message . '</em>' . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'wp-e-commerce-exporter' ) . '</a>)';
			else
				$message = __( 'A WordPress or server error caused the exporter to fail, no reason was provided, please get in touch so we can reproduce and resolve this.', 'wp-e-commerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'wp-e-commerce-exporter' ) . '</a>)';
			wpsc_ce_admin_notice( $message, 'error' );
		}

		// Displays a HTML notice where the memory allocated to WordPress falls below 64MB
		if( !wpsc_ce_get_option( 'dismiss_memory_prompt', 0 ) ) {
			$memory_limit = absint( ini_get( 'memory_limit' ) );
			$minimum_memory_limit = 64;
			if( $memory_limit < $minimum_memory_limit ) {
				$dismiss_url = esc_url( add_query_arg( array( 'action' => 'dismiss_memory_prompt', '_wpnonce' => wp_create_nonce( 'wpsc_ce_dismiss_memory_prompt' ) ) ) );
				$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/usage/';
				$message = sprintf( __( 'We recommend setting memory to at least %dMB, your site has only %dMB allocated to it. See: <a href="%s" target="_blank">Increasing memory allocated to PHP</a>', 'wp-e-commerce-exporter' ), $minimum_memory_limit, $memory_limit, $troubleshooting_url ) . '<span style="float:right;"><a href="' . $dismiss_url . '">' . __( 'Dismiss', 'wp-e-commerce-exporter' ) . '</a></span>';
				wpsc_ce_admin_notice( $message, 'error' );
			}
		}

		// If the empty flag is set then prepare for an error notice
		if( isset( $_GET['empty'] ) ) {
			$message = __( 'No export entries were found, please try again with different export filters.', 'wp-e-commerce-exporter' );
			wpsc_ce_admin_notice( $message, 'error' );
		}

		// If the export failed the WordPress Transient will still exist
		if( get_transient( WPSC_CE_PREFIX . '_running' ) ) {
			$message = __( 'A WordPress or server error caused the exporter to fail with a blank screen, this is either a memory or timeout issue, please get in touch so we can reproduce and resolve this.', 'wp-e-commerce-exporter' ) . ' (<a href="' . $troubleshooting_url . '" target="_blank">' . __( 'Need help?', 'wp-e-commerce-exporter' ) . '</a>)';
			wpsc_ce_admin_notice( $message, 'error' );
			delete_transient( WPSC_CE_PREFIX . '_running' );
		}

	}

	// Saves the state of Export fields for next export
	function wpsc_ce_save_fields( $type, $fields = array() ) {

		if( $fields == false )
			$fields = array();
		if( $type && isset( $fields ) )
			wpsc_ce_update_option( $type . '_fields', $fields );

	}

	// Add Store Export to filter types on the WordPress Media screen
	function wpsc_ce_add_post_mime_type( $post_mime_types = array() ) {

		$post_mime_types['text/csv'] = array( __( 'Store Exports', 'wp-e-commerce-exporter' ), __( 'Manage Store Exports', 'wp-e-commerce-exporter' ), _n_noop( 'Store Export <span class="count">(%s)</span>', 'Store Exports <span class="count">(%s)</span>' ) );
		return $post_mime_types;

	}
	add_filter( 'post_mime_types', 'wpsc_ce_add_post_mime_type' );

	// In-line display of CSV file and export details when viewed via WordPress Media screen
	function wpsc_ce_read_csv_file( $post = null ) {

		if( !$post ) {
			if( isset( $_GET['post'] ) )
				$post = get_post( $_GET['post'] );
		}

		if( $post->post_type != 'attachment' )
			return false;

		if( $post->post_mime_type != 'text/csv' )
			return false;

		$filename = $post->post_name;
		$filepath = get_attached_file( $post->ID );
		$contents = __( 'No export entries were found, please try again with different export filters.', 'wp-e-commerce-exporter' );
		if( file_exists( $filepath ) ) {
			$handle = fopen( $filepath, "r" );
			$contents = stream_get_contents( $handle );
			fclose( $handle );
		} else {
			// This resets the _wp_attached_file Post meta key to the correct value
			update_attached_file( $post->ID, $post->guid );
			// Try grabbing the file contents again
			$filepath = get_attached_file( $post->ID );
			if( file_exists( $filepath ) ) {
				$handle = fopen( $filepath, "r" );
				$contents = stream_get_contents( $handle );
				fclose( $handle );
			}
		}
		if( !empty( $contents ) )
			include_once( WPSC_CE_PATH . 'templates/admin/media-csv_file.php' );

		$export_type = get_post_meta( $post->ID, '_wpsc_export_type', true );
		$columns = get_post_meta( $post->ID, '_wpsc_columns', true );
		$rows = get_post_meta( $post->ID, '_wpsc_rows', true );
		$start_time = get_post_meta( $post->ID, '_wpsc_start_time', true );
		$end_time = get_post_meta( $post->ID, '_wpsc_end_time', true );
		$idle_memory_start = get_post_meta( $post->ID, '_wpsc_idle_memory_start', true );
		$data_memory_start = get_post_meta( $post->ID, '_wpsc_data_memory_start', true );
		$data_memory_end = get_post_meta( $post->ID, '_wpsc_data_memory_end', true );
		$idle_memory_end = get_post_meta( $post->ID, '_wpsc_idle_memory_end', true );
		include_once( WPSC_CE_PATH . 'templates/admin/media-export_details.php' );

	}
	add_action( 'edit_form_after_editor', 'wpsc_ce_read_csv_file' );

	// List of Export types used on Store Exporter screen
	function wpsc_ce_return_export_types() {

		$export_types = array();
		$export_types['products'] = __( 'Products', 'wp-e-commerce-exporter' );
		$export_types['categories'] = __( 'Categories', 'wp-e-commerce-exporter' );
		$export_types['tags'] = __( 'Tags', 'wp-e-commerce-exporter' );
		$export_types['orders'] = __( 'Orders', 'wp-e-commerce-exporter' );
		$export_types['customers'] = __( 'Customers', 'wp-e-commerce-exporter' );
		$export_types['coupons'] = __( 'Coupons', 'wp-e-commerce-exporter' );
		$export_types = apply_filters( 'wpsc_ce_export_types', $export_types );
		return $export_types;

	}

	// Returns label of Export type slug used on Store Exporter screen
	function wpsc_ce_export_type_label( $export_type = '', $echo = false ) {

		$output = '';
		if( !empty( $export_type ) ) {
			$export_types = wpsc_ce_return_export_types();
			if( array_key_exists( $export_type, $export_types ) )
				$output = $export_types[$export_type];
		}
		if( $echo )
			echo $output;
		else
			return $output;

	}

	function wpsc_ce_export_options_export_format() {

		ob_start(); ?>
<tr>
	<th>
		<label><?php _e( 'Export format', 'wp-e-commerce-exporter' ); ?></label>
	</th>
	<td>
		<label><input type="radio" name="export_format" value="csv"<?php checked( 'csv', 'csv' ); ?> /> <?php _e( 'CSV', 'wp-e-commerce-exporter' ); ?> <span class="description"><?php _e( '(Comma separated values)', 'wp-e-commerce-exporter' ); ?></span></label><br />
		<label><input type="radio" name="export_format" value="xml" disabled="disabled" /> <?php _e( 'XML', 'wp-e-commerce-exporter' ); ?> <span class="description"><?php _e( '(EXtensible Markup Language)', 'wp-e-commerce-exporter' ); ?></label>
		<p class="description"><?php _e( 'Adjust the export format to generate different export file formats.', 'wp-e-commerce-exporter' ); ?></p>
	</td>
</tr>
<?php
		ob_end_flush();

	}

	// Returns a list of archived exports
	function wpsc_ce_get_archive_files() {

		$post_type = 'attachment';
		$meta_key = '_wpsc_export_type';
		$args = array(
			'post_type' => $post_type,
			'post_mime_type' => 'text/csv',
			'meta_key' => $meta_key,
			'meta_value' => null,
			'posts_per_page' => -1,
			'cache_results' => false,
			'no_found_rows' => false
		);
		if( isset( $_GET['filter'] ) ) {
			$filter = $_GET['filter'];
			if( !empty( $filter ) )
				$args['meta_value'] = $filter;
		}
		$files = get_posts( $args );
		return $files;

	}

	// Returns an archived export with additional details
	function wpsc_ce_get_archive_file( $file = '' ) {

		$wp_upload_dir = wp_upload_dir();
		$file->export_type = get_post_meta( $file->ID, '_wpsc_export_type', true );
		$file->export_type_label = wpsc_ce_export_type_label( $file->export_type );
		if( empty( $file->export_type ) )
			$file->export_type = __( 'Unassigned', 'wp-e-commerce-exporter' );
		if( empty( $file->guid ) )
			$file->guid = $wp_upload_dir['url'] . '/' . basename( $file->post_title );
		$file->post_mime_type = get_post_mime_type( $file->ID );
		if( !$file->post_mime_type )
			$file->post_mime_type = __( 'N/A', 'wp-e-commerce-exporter' );
		$file->media_icon = wp_get_attachment_image( $file->ID, array( 80, 60 ), true );
		if( $author_name = get_user_by( 'id', $file->post_author ) )
			$file->post_author_name = $author_name->display_name;
		$t_time = strtotime( $file->post_date, current_time( 'timestamp' ) );
		$time = get_post_time( 'G', true, $file->ID, false );
		if( ( abs( $t_diff = time() - $time ) ) < 86400 )
			$file->post_date = sprintf( __( '%s ago' ), human_time_diff( $time ) );
		else
			$file->post_date = mysql2date( __( 'Y/m/d' ), $file->post_date );
		unset( $author_name, $t_time, $time );
		return $file;

	}

	// HTML template for displaying the current export type filter on the Archives screen
	function wpsc_ce_archives_quicklink_current( $current = '' ) {

		$output = '';
		if( isset( $_GET['filter'] ) ) {
			$filter = $_GET['filter'];
			if( $filter == $current )
				$output = ' class="current"';
		} else if( $current == 'all' ) {
			$output = ' class="current"';
		}
		echo $output;

	}

	// HTML template for displaying the number of each export type filter on the Archives screen
	function wpsc_ce_archives_quicklink_count( $type = '' ) {

		$output = '0';
		$post_type = 'attachment';
		$meta_key = '_wpsc_export_type';
		$args = array(
			'post_type' => $post_type,
			'meta_key' => $meta_key,
			'meta_value' => null,
			'numberposts' => -1,
			'cache_results' => false,
			'no_found_rows' => false
		);
		if( $type )
			$args['meta_value'] = $type;
		if( $posts = get_posts( $args ) )
			$output = count( $posts );
		echo $output;

	}

	/* End of: WordPress Administration */

}

// Returns the Post object of the export file saved as an attachment to the WordPress Media library
function wpsc_ce_save_file_attachment( $filename = '', $post_mime_type = 'text/csv' ) {

	if( !empty( $filename ) ) {
		$post_type = 'wpsc-export';
		$args = array(
			'post_title' => $filename,
			'post_type' => $post_type,
			'post_mime_type' => $post_mime_type
		);
		$post_ID = wp_insert_attachment( $args, $filename );
		if( is_wp_error( $post_ID ) )
			error_log( '[store-exporter] ' . sprintf( 'save_file_attachment() - $s: %s', $filename, $result->get_error_message() ) );
		else
			return $post_ID;
	}

}

// Updates the GUID of the export file attachment to match the correct file URL
function wpsc_ce_save_file_guid( $post_ID, $export_type, $upload_url = '' ) {

	add_post_meta( $post_ID, '_wpsc_export_type', $export_type );
	if( !empty( $upload_url ) ) {
		$args = array(
			'ID' => $post_ID,
			'guid' => $upload_url
		);
		wp_update_post( $args );
	}

}

// Save critical export details against the archived export
function wpsc_ce_save_file_details( $post_ID ) {

	global $export;

	add_post_meta( $post_ID, '_wpsc_start_time', $export->start_time );
	add_post_meta( $post_ID, '_wpsc_idle_memory_start', $export->idle_memory_start );
	add_post_meta( $post_ID, '_wpsc_columns', $export->total_columns );
	add_post_meta( $post_ID, '_wpsc_rows', $export->total_rows );
	add_post_meta( $post_ID, '_wpsc_data_memory_start', $export->data_memory_start );
	add_post_meta( $post_ID, '_wpsc_data_memory_end', $export->data_memory_end );

}

// Update detail of existing archived export
function wpsc_ce_update_file_detail( $post_ID, $detail, $value ) {

	if( strstr( $detail, '_wpsc_' ) !== false )
		update_post_meta( $post_ID, $detail, $value );

}

// Returns a list of allowed Export type statuses, can be overridden on a per-Export type basis
function wpsc_ce_post_statuses( $extra_status = array(), $override = false ) {

	$output = array(
		'publish',
		'pending',
		'draft',
		'future',
		'private',
		'trash'
	);
	if( $override ) {
		$output = $extra_status;
	} else {
		if( $extra_status )
			$output = array_merge( $output, $extra_status );
	}
	return $output;

}

// Returns a list of WordPress User Roles
function wpsc_ce_get_user_roles() {

	global $wp_roles;

	$user_roles = $wp_roles->roles;
	return $user_roles;

}

function wpsc_ce_add_missing_mime_type( $mime_types = array() ) {

	// Add CSV mime type if it has been removed
	if( !isset( $mime_types['csv'] ) )
		$mime_types['csv'] = 'text/csv';
	return $mime_types;

}
add_filter( 'upload_mimes', 'wpsc_ce_add_missing_mime_type', 10, 1 );

if( !function_exists( 'wpsc_ce_current_memory_usage' ) ) {
	function wpsc_ce_current_memory_usage() {

		$output = '';
		if( function_exists( 'memory_get_usage' ) )
			$output = round( memory_get_usage( true ) / 1024 / 1024, 2 );
		return $output;

	}
}

function wpsc_ce_get_option( $option = null, $default = false, $allow_empty = false ) {

	$output = '';
	if( isset( $option ) ) {
		$separator = '_';
		$output = get_option( WPSC_CE_PREFIX . $separator . $option, $default );
		if( $allow_empty == false && $output != 0 && ( $output == false || $output == '' ) )
			$output = $default;
	}
	return $output;

}

function wpsc_ce_update_option( $option = null, $value = null ) {

	$output = false;
	if( isset( $option ) && isset( $value ) ) {
		$separator = '_';
		$output = update_option( WPSC_CE_PREFIX . $separator . $option, $value );
	}
	return $output;

}
?>