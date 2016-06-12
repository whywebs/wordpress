<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	/* WordPress Administration menu */
	function wpsc_cf_admin_menu() {

		add_options_page( __( 'Custom Fields for WP e-Commerce', 'wpsc_cf' ), __( 'Custom Fields', 'wpsc_cf' ), 'manage_options', 'wpsc_cf', 'wpsc_cf_html_settings' );

	}
	add_action( 'admin_menu', 'wpsc_cf_admin_menu' );

	function wpsc_cf_add_toolbar_items( $admin_bar ){

		$admin_bar->add_menu( array(
			'id'    => 'new-attribute',
			'parent' => 'new-content',
			'title' => __( 'Attribute', 'wpsc_cf' ),
			'href'  => self_admin_url( 'edit.php?post_type=wpsc-product&page=wpsc_cf' ),
			'meta'  => array(
				'title' => __( 'Attribute' )
			),
		));

	}
	add_action( 'admin_bar_menu', 'wpsc_cf_add_toolbar_items', 100 );

	function wpsc_cf_add_modules_admin_pages( $page_hooks, $base_page ) {

		$page_hooks[] = add_submenu_page( $base_page, __( 'Custom Fields', 'wpsc_cf' ), __( 'Attributes', 'wpsc_pd' ), 'manage_options', 'wpsc_cf', 'wpsc_cf_html_page' );
		return $page_hooks;

	}
	add_filter( 'wpsc_additional_pages', 'wpsc_cf_add_modules_admin_pages', 10, 2 );

	function wpsc_cf_init_meta_box() {

		$pagename = 'wpsc-product';
		add_meta_box( 'wpsc_cf_meta_box', __( 'Attributes', 'wpsc_cf' ), 'wpsc_cf_meta_box', $pagename, 'normal', 'default' );

	}
	add_action( 'admin_menu', 'wpsc_cf_init_meta_box' );

	function wpsc_cf_add_to_product_form( $order ) {

		if( array_search( 'wpsc_cf_meta_box', (array)$order ) === false )
			$order[] = 'wpsc_cf_meta_box';
		return $order;

	}
	add_filter( 'wpsc_products_page_forms', 'wpsc_cf_add_to_product_form' );

	function wpsc_cf_meta_box() {

		global $post, $wpdb, $closed_postboxes;

		$product_meta = maybe_unserialize( get_post_meta( $post->ID, '_wpsc_product_metadata', true ) );
		$data = unserialize( wpsc_cf_get_option( 'data' ) );
		if( $data ) {
			$data = wpsc_cf_custom_field_sort( $data, 'order' );
			$i = 0;
			foreach( $data as $field ) {
				$manage_attribute_url = sprintf( '<a href="%s">' . __( 'Manage Attribute', 'wpsc_cf' ) . '</a>', add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_cf', 'action' => 'edit', 'id' => $i ), 'edit.php' ) ); ?>
<label for="wpsc_cf_product_<?php echo $i; ?>"><?php echo $field['name']; ?>:</label><br />
<?php
				$output = '';
				switch( $field['type'] ) {

					case 'input':
						$output = '
<input type="text" id="wpsc_cf_product_' . $i . '" name="meta[' . WPSC_META_PREFIX . $field['slug'] . ']" value="' . get_product_meta( $post->ID, $field['slug'], true ) . '" size="32" />
<span class="howto">' . $field['description'] . '</span>';
						break;

					case 'textarea':
						$output = '
<textarea id="wpsc_cf_product_' . $i . '" name="meta[' . WPSC_META_PREFIX . $field['slug'] . ']" rows="3" cols="30">' . get_product_meta( $post->ID, $field['slug'], true ) . '</textarea>
<span class="howto">' . $field['description'] . '</span>';
						break;

					case 'dropdown':
						if( isset( $field['options'] ) && $field['options'] ) {
							$output = '
<select id="wpsc_cf_product_' . $i . '" name="meta[' . WPSC_META_PREFIX . $field['slug'] . ']">
	<option></option>';
							$options = explode( '|', $field['options'] );
							foreach( $options as $option )
								$output .= '
	<option value="' . $option . '"' . selected( $option, get_product_meta( $post->ID, $field['slug'], true ), false ) . '>' . $option . '&nbsp;</option>' . "\n";
							$output .= '
</select>';
						} else {
							$output .= '<span>' . sprintf( __( 'No options have been set for this Attribute. %s', 'wpsc_rp' ), $manage_attribute_url ) . '</span>';
						}
						$output .= '
<span class="howto">' . $field['description'] . '</span>';
						break;

					case 'checkbox':
					case 'radio':
						if( isset( $field['options'] ) && $field['options'] ) {
						$output = '
<fieldset id="wpsc_cf_product_fieldset_' . $i . '">';
							$options = explode( '|', $field['options'] );
							$values = (array)get_product_meta( $post->ID, $field['slug'], true );
							foreach( $options as $option ) {
								$selected = null;
								if( $values ) {
									if( in_array( $option, $values ) ) {
										$key = array_search( $option, $values );
										$selected = $values[$key];
									}
								}
								$output .= '
	<label><input type="' . $field['type'] . '" id="wpsc_cf_product_' . $i . '" name="meta[' . WPSC_META_PREFIX . $field['slug'] . '][]" value="' . $option . '"' . checked( $option, $selected, false ) . ' />&nbsp;' . $option . '</label><br />' . "\n";
							}
							$output .= '
</fieldset>';
						} else {
							$output .= '<span>' . __( 'No options have been set for this Attribute.', 'wpsc_rp' ) . '</span>';
						}
						$output .= '
<span class="howto">' . $field['description'] . '</span>';
						break;

					case 'wysiwyg':
						$output = '
<script type="text/javascript">
	jQuery(document).ready( function () {
	jQuery("#wpsc_cf_product_' . $i . '").addClass("mceEditor");
	if ( typeof( tinyMCE ) == "object" && typeof( tinyMCE.execCommand ) == "function" ) {
	jQuery("#wpsc_cf_product_' . $i . '").wrap( "" );
	tinyMCE.execCommand("mceAddControl", false, "wpsc_cf_product_' . $i . '");
	}
	});
</script>
<div style="background-color:#fff;">
	<textarea id="wpsc_cf_product_' . $i . '" name="meta[' . WPSC_META_PREFIX . $field['slug'] . ']" rows="3" cols="30">' . wpautop( get_product_meta( $post->ID, $field['slug'], true ) ) . '</textarea>
</div>
<span class="howto">' . $field['description'] . '</span>';
					break;

				}
				echo $output; ?>
<br />
<?php
				$i++;
			}
		} else {
			$output = '<p>' . __( 'No Attributes have been created.', 'wpsc_cf' ) . '</p>';
			echo $output;
		}
	}

	/* End of: WordPress Administration */

} else {

	/* Start of: Storefront */

	function wpsc_cf_init() {

		global $wp_query;

		$position = wpsc_cf_get_option( 'position' );

		if( $wp_query->is_single ) {
			if( $position <> 'manual' )
				wpsc_cf_html_product();
		}

	}

	function wpsc_cf_html_product( $args = null ) {

		$data = unserialize( wpsc_cf_get_option( 'data' ) );
		if( $data ) {
			$data = wpsc_cf_custom_field_sort( $data, 'order' );
			$custom_fields = $data;
			$layout = wpsc_cf_get_option( 'layout' );
			if( $layout ) {
				if( file_exists( STYLESHEETPATH . '/wpsc-single_product_customfields_' . $layout ) )
					include_once( STYLESHEETPATH . '/wpsc-single_product_customfields_' . $layout );
				else
					include_once( WPSC_CF_PATH . 'templates/store/wpsc-single_product_customfields_' . $layout );
			} else {
				include_once( WPSC_CF_PATH . 'templates/store/wpsc-single_product_customfields_table.php' );
			}

		}

	}

	function wpsc_cf_has_value( $custom_field ) {

		global $post;

		$check = get_post_meta( $post->ID, '_wpsc_' . $custom_field['slug'], true );
		if( $check )
			return true;

	}

	function wpsc_cf_value( $custom_field = array() ) {

		$output = '';
		if( $custom_field )
			$output = wpsc_cf_return_value( $custom_field );
		echo $output;

	}

	function wpsc_cf_return_value( $custom_field = array() ) {

		global $post;

		$output = '';
		if( $custom_field ) {
			switch( $custom_field['type'] ) {

				case 'input':
				case 'dropdown':
				case 'checkbox':
				case 'radio':
					$values = get_post_meta( $post->ID, '_wpsc_' . $custom_field['slug'], true );
					if( is_array( $values ) ) {
						$value = '';
						$size = count( $values );
						for( $i = 0; $i < $size; $i++ )
							$value .= $values[$i] . ', ';
						$value = substr( $value, 0, -2 );
					} else {
						$value = $values;
					}
					$output = stripcslashes( $custom_field['prefix'] ) . $value . stripslashes( $custom_field['suffix'] );
					break;

				case 'textarea':
				case 'wysiwyg':
					$output = stripcslashes( $custom_field['prefix'] ) . get_post_meta( $post->ID, '_wpsc_' . $custom_field['slug'], true ) . stripslashes( $custom_field['suffix'] );
					$output = str_replace( "\n", '<br />', $output );
					break;

			}
		}
		return $output;

	}

	/* End of: Storefront */

}

/* Start of: Common */

/* Product Importer Deluxe integration */
function wpsc_cf_pd_options_addons( $options ) {

	$custom_options = maybe_unserialize( wpsc_cf_get_option( 'data' ) );
	if( $custom_options ) {
		foreach( $custom_options as $custom_option )
			$options[] = array( 'attribute_' . $custom_option['slug'], sprintf( __( 'Attribute: %s', 'wpsc_pd' ), $custom_option['name'] ) );
	}
	return $options;

}
add_filter( 'wpsc_pd_options_addons', 'wpsc_cf_pd_options_addons', null, 1 );

function wpsc_cf_pd_import_addons( $import, $csv_data ) {

	$import->custom_options = unserialize( wpsc_cf_get_option( 'data' ) );
	if( isset( $import->custom_options ) && $import->custom_options ) {
		// echo '<code>' . print_r( $csv_data, true ) . '</code>';
		// echo '<br />';
		foreach( $import->custom_options as $custom_option ) {
			// echo ' - ' . $custom_option['slug'] . '<br />';
			if( isset( $csv_data['attribute_' . $custom_option['slug']] ) ) {
				$import->csv_custom[$custom_option['slug']] = array_filter( $csv_data['attribute_' . $custom_option['slug']] );
				$import->log .= "<br />>>> " . sprintf( __( 'Attribute: %s has been detected and grouped', 'wpsc_pd' ), $custom_option['name'] );
			}
		}
	}
	return $import;

}
add_filter( 'wpsc_pd_import_addons', 'wpsc_cf_pd_import_addons', null, 2 );

function wpsc_cf_pd_product_addons( $product, $import, $count ) {

	/* Attribute integration */
	if( $import->custom_options ) {
		foreach( $import->custom_options as $custom_option )
			if( isset( $import->csv_custom[$custom_option['slug']][$count] ) )
				$product->custom_fields[$custom_option['slug']] = $import->csv_custom[$custom_option['slug']][$count];
	}
	return $product;

}
add_filter( 'wpsc_pd_product_addons', 'wpsc_cf_pd_product_addons', null, 3 );

function wpsc_cf_pd_create_product_log_addons( $import, $product ) {

	if( $import->custom_options ) {
		$import->log .= "<br />>>>>>> " . __( 'Setting Attributes', 'wpsc_pd' );
		foreach( $import->custom_options as $custom_option ) {
			if( isset( $product->custom_fields[$custom_option['slug']] ) && $product->custom_fields[$custom_option['slug']] )
				$import->log .= "<br />>>>>>>>>> " . sprintf( __( 'Setting %s: %s', 'wpsc_pd' ), $custom_option['name'], $product->custom_fields[$custom_option['slug']] );
		}
	}
	return $import;

}
add_filter( 'wpsc_pd_create_product_log_addons', 'wpsc_cf_pd_create_product_log_addons', null, 2 );

/* End of: Common */
?>