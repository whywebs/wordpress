<?php
function wpsc_cf_install() {

	wpsc_cf_create_options();

}

// Trigger the creation of Admin options for this Plugin
function wpsc_cf_create_options() {

	$prefix = 'wpsc_cf';

	if( !get_option( $prefix . '_position' ) )
		add_option( $prefix . '_position', 'wpsc_product_addon_after_descr' );

	if( !get_option( $prefix . '_layout' ) )
		add_option( $prefix . '_layout', 'table.php' );

	if( !get_option( $prefix . '_display_title' ) )
		add_option( $prefix . '_display_title', 1 );

	if( !get_option( $prefix . '_title_text' ) )
		add_option( $prefix . '_title_text', __( 'Additional Details', 'wpsc_cf' ) );

}
?>