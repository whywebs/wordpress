<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	function wpsc_cf_template_header( $title = '', $icon = 'tools' ) {

		$icon = wpsc_is_admin_icon_valid( $icon ); ?>
<div class="wrap">
	<div id="icon-<?php echo $icon; ?>" class="icon32"><br /></div>
	<h2><?php echo $title; ?></h2>
<?php
	}

	function wpsc_cf_template_footer() { ?>
</div>
<?php
	}

	function wpsc_cf_check_options_exist() {

		$prefix = 'vl_wpsccf';
		$sample = get_option( $prefix . '_data' );
		if( $sample )
			return true;

	}

	function wpsc_cf_is_serialized( $str ) {

		return( $str == serialize( false ) || @unserialize( $str ) !== false );

	}

	function wpsc_cf_return_type_label( $type ) {

		$options = wpsc_cf_custom_field_types();

		foreach( $options as $option ) {
			if( $option['name'] == $type ) {
				$label = $option['label'];
				break;
			}
		}
		return $label;

	}

	function wpsc_cf_custom_field_types() {

		$options = array();
		$options[] = array(
			'name' => 'input',
			'label' => __( 'Input', 'wpsc_ce' )
		);
		$options[] = array(
			'name' => 'textarea',
			'label' => __( 'Textarea', 'wpsc_ce' )
		);
		$options[] = array(
			'name' => 'dropdown',
			'label' => __( 'Dropdown', 'wpsc_ce' )
		);
		$options[] = array(
			'name' => 'wysiwyg',
			'label' => __( 'Textarea (with Editor)', 'wpsc_ce' )
		);
		$options[] = array(
			'name' => 'checkbox',
			'label' => __( 'Checkbox List', 'wpsc_ce' )
		);
		$options[] = array(
			'name' => 'radio',
			'label' => __( 'Radio List', 'wpsc_ce' )
		);

		return $options;

	}

	/* End of: WordPress Administration */

} else {

	/* Start of: Storefront */

	function wpsc_cf_show_name( $show_name = null ) {

		if( $show_name )
			return $show_name;

	}

	/* End of: Storefront */

}

/* Start of: Common */

function wpsc_cf_get_option( $option = null, $default = false ) {

	$output = '';
	if( isset( $option ) ) {
		$separator = '_';
		$output = get_option( WPSC_CF_PREFIX . $separator . $option, $default );
	}
	return $output;

}

function wpsc_cf_update_option( $option = null, $value = null ) {

	$output = false;
	if( isset( $option ) && isset( $value ) ) {
		$separator = '_';
		$output = update_option( WPSC_CF_PREFIX . $separator . $option, $value );
	}
	return $output;

}


function wpsc_cf_custom_field_sort( $array, $key ) {

	$sort = array();
	$ret = array();
	reset( $array );
	foreach( $array as $ii => $va )
		$sort[$ii] = $va[$key];
	asort( $sort );
	foreach( $sort as $ii => $va )
		$ret[$ii] = $array[$ii];
	$array = $ret;

	return $array;

}

function wpsc_cf_pd_create_product_addons( $product, $import ) {

	if( $import->custom_options ) {
		foreach( $import->custom_options as $custom_option ) {
			if( isset( $product->custom_fields[$custom_option['slug']] ) && $product->custom_fields[$custom_option['slug']] ) {
				if( version_compare( wpsc_get_major_version(), '3.7', '<=' ) ) {
					$wpdb->insert( $wpdb->prefix . 'wpsc_productmeta', array( 
						'product_id' => $product->ID,
						'meta_key' => $custom_option['slug'],
						'meta_value' => $product->custom_fields[$custom_option['slug']]
					) );
				} else if( version_compare( wpsc_get_major_version(), '3.8', '>=' ) ) {
					update_product_meta( $product->ID, $custom_option['slug'], $product->custom_fields[$custom_option['slug']] );
				}
			}
		}
	}
	return $product;

}
add_filter( 'wpsc_pd_create_product_addons', 'wpsc_cf_pd_create_product_addons', null, 2 );

function wpsc_cf_pd_merge_product_data_addons( $product_data, $product, $import ) {

	if( $product->ID ) {
		if( $import->custom_options ) {
			$custom_fields = array();
			foreach( $import->custom_options as $custom_option )
				$custom_fields[$custom_option['slug']] = get_product_meta( $product->ID, $custom_option['slug'], true );
			$product_data->custom_fields = $custom_fields;
		}
	}
	return $product_data;

}
add_filter( 'wpsc_pd_merge_product_data_addons', 'wpsc_cf_pd_merge_product_data_addons', null, 3 );

function wpsc_cf_pd_merge_product_addons( $product, $import, $product_data ) {

	if( isset( $product->custom_fields ) && $product->custom_fields ) {
		foreach( $import->custom_options as $custom_option ) {
			if( $product->custom_fields[$custom_option['slug']] <> $product_data->custom_fields[$custom_option['slug']] ) {
				update_product_meta( $product->ID, $custom_option['slug'], $product->custom_fields[$custom_option['slug']] );
				$product->updated = true;
			}
		}
	}
	return $product;

}
add_filter( 'wpsc_pd_merge_product_addons', 'wpsc_cf_pd_merge_product_addons', null, 3 );

function wpsc_cf_pd_merge_product_log_addons( $import, $product, $product_data ) {

	if( isset( $product->custom_fields ) && $product->custom_fields ) {
		foreach( $import->custom_options as $custom_option ) {
			if( $product->custom_fields[$custom_option['slug']] <> $product_data->custom_fields[$custom_option['slug']] )
				$import->log .= "<br />>>>>>> " . sprintf( __( "Updating Attribute: %s", 'wpsc_pd' ), $custom_option['name'] );
			else if( $import->advanced_log )
				$import->log .= "<br />>>>>>> " . sprintf( __( 'Skipping Attribute: %s', 'wpsc_pd' ), $custom_option['name'] );
		}
	}
	return $import;

}
add_filter( 'wpsc_pd_merge_product_log_addons', 'wpsc_cf_pd_merge_product_log_addons', null, 3 );

/* End of: Common */
?>