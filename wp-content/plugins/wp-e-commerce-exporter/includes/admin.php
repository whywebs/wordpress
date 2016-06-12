<?php
// Display admin notice on screen load
function wpsc_ce_admin_notice( $message = '', $priority = 'updated', $screen = '' ) {

	if( empty( $priority ) )
		$priority = 'updated';
	if( !empty( $message ) )
		add_action( 'admin_notices', wpsc_ce_admin_notice_html( $message, $priority, $screen ) );

}

// HTML template for admin notice
function wpsc_ce_admin_notice_html( $message = '', $priority = 'updated', $screen = '' ) {

	// Display admin notice on specific screen
	if( !empty( $screen ) ) {

		global $pagenow;

		if( is_array( $screen ) ) {
			if( in_array( $pagenow, $screen ) == false )
				return;
		} else {
			if( $pagenow <> $screen )
				return;
		}

	} ?>
<div id="message" class="<?php echo $priority; ?>">
	<p><?php echo $message; ?></p>
</div>
<?php

}


// HTML template header on Store Exporter screen
function wpsc_ce_template_header( $title = '', $icon = 'tools' ) {

	if( $title )
		$output = $title;
	else
		$output = __( 'Store Export', 'wp-e-commerce-exporter' );
	$icon = wpsc_is_admin_icon_valid( $icon );
	$url = esc_url( add_query_arg( 'tab', 'export' ) ); ?>
<div class="wrap">
	<div id="icon-<?php echo $icon; ?>" class="icon32"><br /></div>
	<h2>
		<?php echo $output; ?>
		<a href="<?php echo $url; ?>" class="add-new-h2"><?php _e( 'Add New', 'wp-e-commerce-exporter' ); ?></a>
	</h2>
<?php

}

// HTML template footer on Store Exporter screen
function wpsc_ce_template_footer() { ?>
</div>
<!-- .wrap -->
<?php

}

// HTML template for header prompt on Store Exporter screen
function wpsc_ce_support_donate() {

	$output = '';
	$show = true;
	if( function_exists( 'wpsc_vl_we_love_your_plugins' ) ) {
		if( in_array( WPSC_CE_DIRNAME, wpsc_vl_we_love_your_plugins() ) )
			$show = false;
	}
	if( function_exists( 'wpsc_cd_admin_init' ) )
		$show = false;
	if( $show ) {
		$donate_url = 'http://www.visser.com.au/#donations';
		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/' . WPSC_CE_DIRNAME;
		$output = '
	<div id="support-donate_rate" class="support-donate_rate">
		<p>' . sprintf( __( '<strong>Like this Plugin?</strong> %s and %s.', 'wp-e-commerce-exporter' ), '<a href="' . $donate_url . '" target="_blank">' . __( 'Donate to support this Plugin', 'wp-e-commerce-exporter' ) . '</a>', '<a href="' . esc_url( add_query_arg( array( 'rate' => '5' ), $rate_url ) ) . '#postform" target="_blank">rate / review us on WordPress.org</a>' ) . '</p>
	</div>
';
	}
	echo $output;

}

// Add Store Export to WordPress Administration menu
function wpsc_ce_add_modules_admin_pages( $page_hooks, $base_page ) {

	$page_hooks[] = add_submenu_page( $base_page, __( 'Store Export', 'wp-e-commerce-exporter' ), __( 'Store Export', 'wp-e-commerce-exporter' ), 'manage_options', 'wpsc_ce', 'wpsc_ce_html_page' );
	return $page_hooks;

}
add_filter( 'wpsc_additional_pages', 'wpsc_ce_add_modules_admin_pages', 10, 2 );

function wpsc_ce_plugin_page_notices() {

	global $pagenow;

	if( $pagenow == 'plugins.php' ) {
		if( wpsc_is_woo_activated() || wpsc_is_jigo_activated() ) {
			$r_plugins = array(
				'wp-e-commerce-store-exporter/exporter.php',
				'wp-e-commerce-exporter/exporter.php'
			);
			$i_plugins = get_plugins();
			foreach( $r_plugins as $path ) {
				if( isset( $i_plugins[$path] ) ) {
					add_action( 'after_plugin_row_' . $path, 'wpsc_ce_plugin_page_notice', 10, 3 );
					break;
				}
			}
		}
	}

}

// HTML active class for the currently selected tab on the Store Exporter screen
function wpsc_ce_admin_active_tab( $tab_name = null, $tab = null ) {

	if( isset( $_GET['tab'] ) && !$tab )
		$tab = $_GET['tab'];
	else if( !isset( $_GET['tab'] ) && wpsc_ce_get_option( 'skip_overview', false ) )
		$tab = 'export';
	else
		$tab = 'overview';

	$output = '';
	if( isset( $tab_name ) && $tab_name ) {
		if( $tab_name == $tab )
			$output = ' nav-tab-active';
	}
	echo $output;

}

// HTML template for each tab on the Store Exporter screen
function wpsc_ce_tab_template( $tab = '' ) {

	if( !$tab )
		$tab = 'overview';

	// Store Exporter Deluxe
	$wpsc_cd_exists = false;
	if( !function_exists( 'wpsc_cd_admin_init' ) ) {
		$wpsc_cd_url = 'http://www.visser.com.au/wp-ecommerce/plugins/exporter-deluxe/';
		$wpsc_cd_link = sprintf( '<a href="%s" target="_blank">' . __( 'Store Exporter Deluxe', 'wp-e-commerce-exporter' ) . '</a>', $wpsc_cd_url );
	} else {
		$wpsc_cd_exists = true;
	}
	$troubleshooting_url = 'http://www.visser.com.au/documentation/store-exporter-deluxe/';

	switch( $tab ) {

		case 'overview':
			$skip_overview = wpsc_ce_get_option( 'skip_overview', false );
			break;

		case 'export':

			global $wpsc_purchlog_statuses;

			$export_type = ( isset( $_POST['dataset'] ) ? $_POST['dataset'] : 'products' );

			$products = wpsc_ce_return_count( 'products' );
			$categories = wpsc_ce_return_count( 'categories' );
			$tags = wpsc_ce_return_count( 'tags' );
			$orders = wpsc_ce_return_count( 'orders' );
			$coupons = wpsc_ce_return_count( 'coupons' );
			$customers = wpsc_ce_return_count( 'customers' );

			if( $product_fields = wpsc_ce_get_product_fields() ) {
				foreach( $product_fields as $key => $product_field ) {
					if( !isset( $product_fields[$key]['disabled'] ) )
						$product_fields[$key]['disabled'] = 0;
				}
				$args = array(
					'hide_empty' => 1
				);
				$product_categories = wpsc_ce_get_product_categories( $args );
				$args = array(
					'hide_empty' => 1
				);
				$product_tags = wpsc_ce_get_product_tags( $args );
				$product_statuses = get_post_statuses();
				$product_statuses['trash'] = __( 'Trash', 'wp-e-commerce-exporter' );
				$product_orderby = wpsc_ce_get_option( 'product_orderby', 'ID' );
				$product_order = wpsc_ce_get_option( 'product_order', 'DESC' );
			}
			if( $category_fields = wpsc_ce_get_category_fields() ) {
				$category_orderby = wpsc_ce_get_option( 'category_orderby', 'ID' );
				$category_order = wpsc_ce_get_option( 'category_order', 'DESC' );
			}
			if( $tag_fields = wpsc_ce_get_tag_fields() ) {
				$tag_orderby = wpsc_ce_get_option( 'tag_orderby', 'ID' );
				$tag_order = wpsc_ce_get_option( 'tag_order', 'DESC' );
			}
			if( $order_fields = wpsc_ce_get_order_fields() )
				$order_statuses = $wpsc_purchlog_statuses;
			$customer_fields = wpsc_ce_get_customer_fields();
			$coupon_fields = wpsc_ce_get_coupon_fields();

			$limit_volume = wpsc_ce_get_option( 'limit_volume' );
			$offset = wpsc_ce_get_option( 'offset' );
			break;

		case 'archive':
			if( isset( $_GET['deleted'] ) ) {
				$message = __( 'Archived export has been deleted.', 'wp-e-commerce-exporter' );
				wpsc_ce_admin_notice( $message );
			}
			if( $files = wpsc_ce_get_archive_files() ) {
				foreach( $files as $key => $file )
					$files[$key] = wpsc_ce_get_archive_file( $file );
			}
			break;

		case 'settings':
			$export_filename = wpsc_ce_get_option( 'export_filename', 'wpsc-export_%dataset%-%date%-%random%.csv' );
			if( $export_filename == false )
				$export_filename = '%store_name%-export_%dataset%-%date%-%time%-%random%.csv';
			$delete_csv = wpsc_ce_get_option( 'delete_csv', 1 );
			$timeout = wpsc_ce_get_option( 'timeout', 0 );
			$encoding = wpsc_ce_get_option( 'encoding', 'UTF-8' );
			$bom = wpsc_ce_get_option( 'bom', 1 );
			$delimiter = wpsc_ce_get_option( 'delimiter', ',' );
			$category_separator = wpsc_ce_get_option( 'category_separator', '|' );
			$escape_formatting = wpsc_ce_get_option( 'escape_formatting', 'all' );
			$date_format = wpsc_ce_get_option( 'date_format', 'd/m/Y' );
			$file_encodings = ( function_exists( 'mb_list_encodings' ) ? mb_list_encodings() : false );
			break;

		case 'tools':
			// Product Importer Deluxe
			if( function_exists( 'wpsc_pd_init' ) ) {
				$wpsc_pd_url = esc_url( add_query_arg( 'page', 'wpsc_pd' ) );
				$wpsc_pd_target = false;
			} else {
				$wpsc_pd_url = 'http://www.visser.com.au/wp-ecommerce/plugins/product-importer-deluxe/';
				$wpsc_pd_target = ' target="_blank"';
			}
			// Coupon Importer Deluxe
			if( function_exists( 'wpsc_ci_init' ) ) {
				$wpsc_ci_url = esc_url( add_query_arg( 'page', 'wpsc_ci' ) );
				$wpsc_ci_target = false;
			} else {
				$wpsc_ci_url = 'http://www.visser.com.au/wp-ecommerce/plugins/coupon-importer-deluxe/';
				$wpsc_ci_target = ' target="_blank"';
			}
			break;

	}
	if( $tab )
		include_once( WPSC_CE_PATH . 'templates/admin/tabs-' . $tab . '.php' );

}

function wpsc_ce_plugin_page_notice( $file, $data, $context ) {

	if( is_plugin_active( $file ) ) { ?>
<tr class='plugin-update-tr su-plugin-notice'>
	<td colspan='3' class='plugin-update colspanchange'>
		<div class='update-message'>
			<?php printf( __( '%1$s is intended to be used with a WP e-Commerce store, please check that you are using Store Exporter with the correct e-Commerce platform.', 'wp-e-commerce-exporter' ), $data['Name'] ); ?>
		</div>
	</td>
</tr>
<?php
	}

}
?>