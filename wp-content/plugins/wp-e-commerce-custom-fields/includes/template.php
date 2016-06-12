<?php
if( !is_admin() ) {

	/* Start of: Storefront */

	function wpsc_the_custom_fields( $args = null ) {

		$position = wpsc_cf_get_option( 'position' );
		if( $args )
			wpsc_cf_get_value( $args );
		else if( $position == 'manual' )
			wpsc_cf_html_product();

	}

	function wpsc_cf_show_title() {

		$display_title = wpsc_cf_get_option( 'display_title' );
		return $display_title;

	}

	function wpsc_cf_title() {

		$output = '';
		$output = wpsc_cf_get_option( 'title_text' );
		if( $output )
			echo $output;

	}

	function wpsc_cf_get_title() {

		$output = '';
		$output = wpsc_cf_get_option( 'title_text' );
		if( $output )
			return $output;

	}

	function wpsc_cf_label( $custom_field ) {

		$output = '';
		$output = $custom_field['name'];
		if( $output )
			echo $output;

	}

	function wpsc_cf_get_label( $custom_field ) {

		$output = '';
		$output = $custom_field['name'];
		if( $output )
			return $output;

	}

	function wpsc_cf_get_value( $args = null ) {

		if( $args ) {
			$defaults = array(
				'slug' => '',
			);
			$args = wp_parse_args( $args, $defaults );
			extract( $args, EXTR_SKIP );
			foreach( $args as $key => $arg ) {
				switch( $key ) {

					case 'slug':
						$data = unserialize( wpsc_cf_get_option( 'data' ) );
						if( $data ) {
							foreach( $data as $key => $item ) {
								if( $item['slug'] == $args['slug'] ) {
									echo wpsc_cf_value( $item );
									break;
								}
							}
						}
						break;

				}
			}
		}

	}

	/* End of: Storefront */

}
?>