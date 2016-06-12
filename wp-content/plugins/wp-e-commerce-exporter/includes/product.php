<?php
// Returns Product Tags associated to a specific Product
function wpsc_ce_get_product_assoc_tags( $product_id = 0 ) {

	global $export;

	$output = '';
	$term_taxonomy = 'product_tag';
	$tags = wp_get_object_terms( $product_id, $term_taxonomy );
	if( is_wp_error( $tags ) == false ) {
		$size = count( $tags );
		for( $i = 0; $i < $size; $i++ ) {
			if( $tag = get_term( $tags[$i]->term_id, $term_taxonomy ) )
				$output .= $tag->name . $export->category_separator;
		}
		$output = substr( $output, 0, -1 );
	}
	return $output;

}
// Returns a list of Product export columns
function wpsc_ce_get_product_fields( $format = 'full' ) {

	$fields = array();
	$fields[] = array(
		'name' => 'parent_id',
		'label' => __( 'Parent ID', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'parent_sku',
		'label' => __( 'Parent SKU', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_id',
		'label' => __( 'Product ID', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sku',
		'label' => __( 'Product SKU', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'name',
		'label' => __( 'Product Name', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'slug',
		'label' => __( 'Slug', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'permalink',
		'label' => __( 'Permalink', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'description',
		'label' => __( 'Description', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'additional_description',
		'label' => __( 'Additional Description', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_date',
		'label' => __( 'Product Published', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'post_modified',
		'label' => __( 'Product Modified', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'price',
		'label' => __( 'Price', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'sale_price',
		'label' => __( 'Sale Price', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'weight',
		'label' => __( 'Weight', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'weight_unit',
		'label' => __( 'Weight Unit', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'height',
		'label' => __( 'Height', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'height_unit',
		'label' => __( 'Height Unit', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'width',
		'label' => __( 'Width', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'width_unit',
		'label' => __( 'Width Unit', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'length',
		'label' => __( 'Length', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'length_unit',
		'label' => __( 'Length Unit', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'category',
		'label' => __( 'Category', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'tag',
		'label' => __( 'Tag', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'image',
		'label' => __( 'Image', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'quantity',
		'label' => __( 'Quantity', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'notify_oos',
		'label' => __( 'Notify OOS', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'unpublish_oos',
		'label' => __( 'Unpublish OOS', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'file_download',
		'label' => __( 'File Download', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'external_link',
		'label' => __( 'External Link', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'external_link_text',
		'label' => __( 'External Link Text', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'external_link_target',
		'label' => __( 'External Link Target', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'local_shipping',
		'label' => __( 'Local Shipping Fee', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'international_shipping',
		'label' => __( 'International Shipping Fee', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'no_shipping',
		'label' => __( 'No Shipping', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'taxable_amount',
		'label' => __( 'Taxable Amount', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'tax_bands',
		'label' => __( 'Tax Bands', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'not_taxable',
		'label' => __( 'Not Taxable', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'product_status',
		'label' => __( 'Product Status', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'comment_status',
		'label' => __( 'Comment Status', 'wp-e-commerce-exporter' )
	);

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'wp-e-commerce-exporter' )
	);
*/

	// Allow Plugin/Theme authors to add support for additional Product columns
	$fields = apply_filters( 'wpsc_ce_product_fields', $fields );

	// Advanced Google Product Feed - http://www.leewillis.co.uk/wordpress-plugins/
	if( function_exists( 'wpec_gpf_install' ) ) {
		$fields[] = array(
			'name' => 'gpf_availability',
			'label' => __( 'Advanced Google Product Feed - Availability', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_condition',
			'label' => __( 'Advanced Google Product Feed - Condition', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_brand',
			'label' => __( 'Advanced Google Product Feed - Brand', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_productype',
			'label' => __( 'Advanced Google Product Feed - Product Type', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_google_product_category',
			'label' => __( 'Advanced Google Product Feed - Google Product Category', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_gtin',
			'label' => __( 'Advanced Google Product Feed - Global Trade Item Number (GTIN)', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_mpn',
			'label' => __( 'Advanced Google Product Feed - Manufacturer Part Number (MPN)', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_gender',
			'label' => __( 'Advanced Google Product Feed - Gender', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_agegroup',
			'label' => __( 'Advanced Google Product Feed - Age Group', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_colour',
			'label' => __( 'Advanced Google Product Feed - Colour', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'gpf_size',
			'label' => __( 'Advanced Google Product Feed - Size', 'wp-e-commerce-exporter' )
		);
	}

	// All in One SEO Pack - http://wordpress.org/extend/plugins/all-in-one-seo-pack/
	if( function_exists( 'aioseop_activate' ) ) {
		$fields[] = array(
			'name' => 'aioseop_keywords',
			'label' => __( 'All in One SEO - Keywords', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'aioseop_description',
			'label' => __( 'All in One SEO - Description', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'aioseop_title',
			'label' => __( 'All in One SEO - Title', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'aioseop_title_attributes',
			'label' => __( 'All in One SEO - Title Attributes', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'aioseop_menu_label',
			'label' => __( 'All in One SEO - Menu Label', 'wp-e-commerce-exporter' )
		);
	}

	// Custom Fields - http://wordpress.org/plugins/wp-e-commerce-custom-fields/
	if( function_exists( 'wpsc_cf_install' ) ) {
		$attributes = maybe_unserialize( get_option( 'wpsc_cf_data' ) );
		if( !empty( $attributes ) ) {
			foreach( $attributes as $key => $attribute ) {
				$fields[] = array(
					'name' => sprintf( 'attribute_%s', $attribute['slug'] ),
					'label' => sprintf( __( 'Attribute: %s', 'wp-e-commerce-exporter' ), $attribute['name'] )
				);
			}
			unset( $attributes, $attribute );
		}
	}

	// Related Products - http://www.visser.com.au/plugins/related-products/
	if( function_exists( 'wpsc_rp_pd_options_addons' ) ) {
		$fields[] = array(
			'name' => 'related_products',
			'label' => __( 'Related Products', 'wp-e-commerce-exporter' )
		);
	}

	// Simple Product Options - http://wordpress.org/plugins/wp-e-commerce-simple-product-options/
	if( class_exists( 'wpec_simple_product_options_admin' ) ) {
		$args = array(
			'hide_empty' => false,
			'parent' => 0
		);
		$product_options = get_terms( 'wpec_product_option', $args );
		if( is_wp_error( $product_options ) == false ) {
			foreach( $product_options as $product_option ) {
				$fields[] = array(
					'name' => sprintf( 'simple_product_option_%s', $product_option->slug ),
					'label' => sprintf( __( 'Simple Product Option: %s', 'wp-e-commerce-exporter' ), $product_option->name )
				);
			}
		}
	}

	// WordPress SEO - http://wordpress.org/plugins/wordpress-seo/
	if( function_exists( 'wpseo_admin_init' ) ) {
		$fields[] = array(
			'name' => 'wpseo_focuskw',
			'label' => __( 'WordPress SEO - Focus Keyword', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_metadesc',
			'label' => __( 'WordPress SEO - Meta Description', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_title',
			'label' => __( 'WordPress SEO - SEO Title', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_googleplus_description',
			'label' => __( 'WordPress SEO - Google+ Description', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'wpseo_opengraph_description',
			'label' => __( 'WordPress SEO - Facebook Description', 'wp-e-commerce-exporter' )
		);
	}

	// Ultimate SEO - http://wordpress.org/plugins/seo-ultimate/
	if( function_exists( 'su_wp_incompat_notice' ) ) {
		$fields[] = array(
			'name' => 'useo_meta_title',
			'label' => __( 'Ultimate SEO - Title Tag', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'useo_meta_description',
			'label' => __( 'Ultimate SEO - Meta Description', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'useo_meta_keywords',
			'label' => __( 'Ultimate SEO - Meta Keywords', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'useo_social_title',
			'label' => __( 'Ultimate SEO - Social Title', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'useo_social_description',
			'label' => __( 'Ultimate SEO - Social Description', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'useo_meta_noindex',
			'label' => __( 'Ultimate SEO - NoIndex', 'wp-e-commerce-exporter' )
		);
		$fields[] = array(
			'name' => 'useo_meta_noautolinks',
			'label' => __( 'Ultimate SEO - Disable Autolinks', 'wp-e-commerce-exporter' )
		);
	}

	if( $remember = wpsc_ce_get_option( 'products_fields', array() ) ) {
		$remember = maybe_unserialize( $remember );
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			$fields[$i]['disabled'] = 0;
			$fields[$i]['default'] = 1;
			if( !array_key_exists( $fields[$i]['name'], $remember ) )
				$fields[$i]['default'] = 0;
		}
	}

	switch( $format ) {

		case 'summary':
			$output = array();
			$size = count( $fields );
			for( $i = 0; $i < $size; $i++ )
				$output[$fields[$i]['name']] = 'on';
			return $output;
			break;

		case 'full':
		default:
			return $fields;
			break;

	}

}

// Returns the export column header label based on an export column slug
function wpsc_ce_get_product_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = wpsc_ce_get_product_fields();
		$size = count( $fields );
		for( $i = 0; $i < $size; $i++ ) {
			if( $fields[$i]['name'] == $name ) {
				switch( $format ) {

					case 'name':
						$output = $fields[$i]['label'];
						break;

					case 'full':
						$output = $fields[$i];
						break;

				}
				$i = $size;
			}
		}
	}
	return $output;

}
?>