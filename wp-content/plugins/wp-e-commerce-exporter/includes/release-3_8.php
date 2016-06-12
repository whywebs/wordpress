<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	// Add Store Export to WordPress Administration menu
	function wpsc_ce_admin_page_item( $menu = array() ) {

		$title = __( 'Store Export', 'wp-e-commerce-exporter' );
		$link = esc_url( add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_ce' ), 'edit.php' ) );
		$description = __( 'Export store details out of WP e-Commerce into a CSV-formatted file.', 'wp-e-commerce-exporter' );

		$menu[] = array( 'title' => $title, 'link' => $link, 'description' => $description );

		return $menu;

	}
	add_filter( 'wpsc_sm_store_admin_page', 'wpsc_ce_admin_page_item', 1 );

	// Returns number of an Export type prior to export, used on Store Exporter screen
	function wpsc_ce_return_count( $export_type ) {

		global $wpdb;

		$count_sql = null;
		switch( $export_type ) {

			case 'products':
				$post_type = 'wpsc-product';
				$count = wp_count_posts( $post_type );
				break;

			case 'variations':
				$post_type = 'wpsc-variation';
				$count = wp_count_posts( $post_type );
				break;

			case 'images':
				$post_type = 'attachment';
				$count_sql = $wpdb->prepare( "SELECT COUNT(`id`) FROM `" . $wpdb->posts . "` WHERE `post_type` = '%s' AND `post_mime_type` LIKE 'image/%'", $post_type );
				break;

			case 'files':
				$post_type = 'wpsc-product-file';
				$count = wp_count_posts( $post_type );
				break;

			case 'categories':
				$term_taxonomy = 'wpsc_product_category';
				$count = wp_count_terms( $term_taxonomy );
				break;

			case 'tags':
				$term_taxonomy = 'product_tag';
				$count = wp_count_terms( $term_taxonomy );
				break;

			case 'orders':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_purchase_logs`";
				break;

			case 'coupons':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_coupon_codes`";
				break;

			case 'customers':
				$count_sql = "SELECT COUNT( DISTINCT wpsc_submited_form_data.`value` ) FROM `" . $wpdb->prefix . "wpsc_checkout_forms` as wpsc_checkout_forms, `" . $wpdb->prefix . "wpsc_submited_form_data` as wpsc_submited_form_data WHERE wpsc_checkout_forms.`id` = wpsc_submited_form_data.`form_id` AND wpsc_checkout_forms.unique_name = 'billingemail'";
				break;

			// 3rd Party

			case 'wishlist':
				$post_type = 'wpsc-wishlist';
				$count = wp_count_posts( $post_type );
				break;

			case 'enquiries':
				$post_type = 'wpsc-enquiry';
				$count = wp_count_posts( $post_type );
				break;

			case 'credit-cards':
				$post_type = 'offline_payment';
				$count = wp_count_posts( $post_type );
				break;

			case 'related-products':
				break;

		}
		if( isset( $count ) || $count_sql ) {
			if( isset( $count ) ) {
				if( is_object( $count ) ) {
					$count = (array)$count;
					$count = absint( array_sum( $count ) );
				}
				return $count;
			} else {
				if( $count_sql )
					$count = $wpdb->get_var( $count_sql );
				else
					$count = 0;
			}
			return $count;
		} else {
			return 0;
		}

	}

	/* End of: WordPress Administration */

}

/* Start of: Common */

// Export process for CSV file
function wpsc_ce_export_dataset( $export_type, &$output = null ) {

	global $export;

	$separator = $export->delimiter;
	$export->columns = array();
	$export->total_rows = 0;
	$export->total_columns = 0;
	set_transient( WPSC_CE_PREFIX . '_running', time(), wpsc_ce_get_option( 'timeout', MINUTE_IN_SECONDS ) );

	switch( $export_type ) {

		// Products
		case 'products':
			$fields = wpsc_ce_get_product_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( $fields, (array)$export->fields ) ) {
				if( function_exists( 'wpsc_cf_install' ) )
					$export->args['custom_fields'] = array();
				if( class_exists( 'wpec_simple_product_options_admin' ) )
					$export->args['simple_product_options'] = array();
				foreach( $export->fields as $key => $field ) {
					$export->columns[] = wpsc_ce_get_product_field( $key );
					if( strpos( $key, 'attribute_' ) !== false )
						$export->args['custom_fields'][] = str_replace( 'attribute_', '', $key );
					if( strpos( $key, 'simple_product_option_' ) !== false )
						$export->args['simple_product_options'][] = str_replace( 'simple_product_option_', '', $key );
				}
			}
			$export->data_memory_start = wpsc_ce_current_memory_usage();
			if( $products = wpsc_ce_get_products( $export->args ) ) {
				$export->total_rows = count( $products );
				$export->total_columns = $size = count( $export->columns );
				if( $export->export_format == 'csv' ) {
					for( $i = 0; $i < $size; $i++ ) {
						if( $i == ( $size - 1 ) )
							$output .= wpsc_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . "\n";
						else
							$output .= wpsc_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . $separator;
					}
				}
				if( !empty( $export->fields ) ) {
					foreach( $products as $product ) {

						if( $export->export_format == 'xml' )
							$child = $output->addChild( substr( $export->type, 0, -1 ) );

						$product = wpsc_ce_get_product_data( $product, $export->args );
						foreach( $export->fields as $key => $field ) {
							if( isset( $product->$key ) ) {
								if( is_array( $field ) ) {
									foreach( $field as $array_key => $array_value ) {
										if( !is_array( $array_value ) ) {
											if( $export->export_format == 'csv' )
												$output .= wpsc_ce_escape_csv_value( $array_value, $export->delimiter, $export->escape_formatting );
											else if( $export->export_format == 'xml' )
												$child->addChild( $array_key, htmlspecialchars( $array_value ) );
										}
									}
								} else {
									if( $export->export_format == 'csv' )
										$output .= wpsc_ce_escape_csv_value( $product->$key, $export->delimiter, $export->escape_formatting );
									else if( $export->export_format == 'xml' )
										$child->addChild( $key, htmlspecialchars( $product->$key ) );
								}
							}
							if( $export->export_format == 'csv' )
								$output .= $separator;
						}

						if( $export->export_format == 'csv' )
							$output = substr( $output, 0, -1 ) . "\n";
					}
				}
				unset( $products, $product );
			}
			$export->data_memory_end = wpsc_ce_current_memory_usage();
			break;

		// Categories
		case 'categories':
			$fields = wpsc_ce_get_category_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( $fields, (array)$export->fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = wpsc_ce_get_category_field( $key );
			}
			$export->data_memory_start = wpsc_ce_current_memory_usage();
			if( $categories = wpsc_ce_get_product_categories( $export->args ) ) {
				$export->total_rows = count( $categories );
				$export->total_columns = $size = count( $export->columns );
				if( $export->export_format == 'csv' ) {
					for( $i = 0; $i < $size; $i++ ) {
						if( $i == ( $size - 1 ) )
							$output .= wpsc_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . "\n";
						else
							$output .= wpsc_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . $separator;
					}
				}
				if( !empty( $export->fields ) ) {
					foreach( $categories as $category ) {

						if( $export->export_format == 'xml' )
							$child = $output->addChild( str_replace( 'ies', 'y', $export->type ) );

						foreach( $export->fields as $key => $field ) {
							if( isset( $category->$key ) ) {
								if( $export->export_format == 'csv' )
									$output .= wpsc_ce_escape_csv_value( $category->$key, $export->delimiter, $export->escape_formatting );
								else if( $export->export_format == 'xml' )
									$child->addChild( $key, htmlspecialchars( $category->$key ) );
							}
							if( $export->export_format == 'csv' )
								$output .= $separator;
						}
						if( $export->export_format == 'csv' )
							$output = substr( $output, 0, -1 ) . "\n";
					}
				}
				unset( $categories, $category );
			}
			$export->data_memory_end = wpsc_ce_current_memory_usage();
			break;

		// Tags
		case 'tags':
			$fields = wpsc_ce_get_tag_fields( 'summary' );
			if( $export->fields = array_intersect_assoc( $fields, (array)$export->fields ) ) {
				foreach( $export->fields as $key => $field )
					$export->columns[] = wpsc_ce_get_tag_field( $key );
			}
			$export->data_memory_start = wpsc_ce_current_memory_usage();
			$tag_args = array(
				'orderby' => ( isset( $export->args['tag_orderby'] ) ? $export->args['tag_orderby'] : 'ID' ),
				'order' => ( isset( $export->args['tag_order'] ) ? $export->args['tag_order'] : 'ASC' ),
			);
			if( $tags = wpsc_ce_get_product_tags( $tag_args ) ) {
				$export->total_rows = count( $tags );
				$export->total_columns = $size = count( $export->columns );
				if( $export->export_format == 'csv' ) {
					for( $i = 0; $i < $size; $i++ ) {
						if( $i == ( $size - 1 ) )
							$output .= wpsc_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . "\n";
						else
							$output .= wpsc_ce_escape_csv_value( $export->columns[$i], $export->delimiter, $export->escape_formatting ) . $separator;
					}
				}
				if( !empty( $export->fields ) ) {
					foreach( $tags as $tag ) {

						if( $export->export_format == 'xml' )
							$child = $output->addChild( substr( $export->type, 0, -1 ) );

						foreach( $export->fields as $key => $field ) {
							if( isset( $tag->$key ) ) {
								if( $export->export_format == 'csv' )
									$output .= wpsc_ce_escape_csv_value( $tag->$key, $export->delimiter, $export->escape_formatting );
								else if( $export->export_format == 'xml' )
									$child->addChild( $key, htmlspecialchars( $tag->$key ) );
							}
							if( $export->export_format == 'csv' )
								$output .= $separator;
						}
						if( $export->export_format == 'csv' )
							$output = substr( $output, 0, -1 ) . "\n";
					}
				}
				unset( $tags, $tag );
			}
			$export->data_memory_end = wpsc_ce_current_memory_usage();
			break;

		// Orders
		case 'orders':
		// Customers
		case 'customers':
		// Coupons
		case 'coupons':
			if( $export->export_format == 'csv' )
				$output = apply_filters( 'wpsc_ce_export_dataset', $export->type );
			else if( $export->export_format == 'xml' )
				$output = apply_filters( 'wpsc_ce_export_dataset', $export->type, $output );
			break;

	}
	// Export completed successfully
	delete_transient( WPSC_CE_PREFIX . '_running' );
	// Check that the export file is populated, export columns have been assigned and rows counted
	if( $output && $export->total_rows && !empty( $export->columns ) ) {
		if( $export->export_format == 'csv' ) {
			if( $export->bom ) {
				// $output .= chr(239) . chr(187) . chr(191) . '';
				$output = "\xEF\xBB\xBF" . $output;
			}
			$output = wpsc_ce_file_encoding( $output );
		}
		if( WPSC_CE_DEBUG )
			set_transient( WPSC_CE_PREFIX . '_debug_log', base64_encode( $output ), wpsc_ce_get_option( 'timeout', MINUTE_IN_SECONDS ) );
		else
			return $output;
	}

}

// Returns a list of WP e-Commerce Products to export process
function wpsc_ce_get_products( $args = array() ) {

	$limit_volume = -1;
	$offset = 0;
	$product_categories = false;
	$product_tags = false;
	$product_status = false;
	$orderby = 'ID';
	$order = 'ASC';
	if( $args ) {
		$limit_volume = $args['limit_volume'];
		$offset = $args['offset'];
		if ( isset( $args['custom_fields'] ) && !empty( $args['custom_fields'] ) )
			$custom_fields = $args['custom_fields'];
		if ( isset( $args['simple_product_options'] ) && !empty( $args['simple_product_options'] ) )
			$simple_product_options = $args['simple_product_options'];
		if( !empty( $args['product_categories'] ) )
			$product_categories = $args['product_categories'];
		if( !empty( $args['product_tags'] ) )
			$product_tags = $args['product_tags'];
		if( !empty( $args['product_status'] ) )
			$product_status = $args['product_status'];
		if( isset( $args['product_orderby'] ) )
			$orderby = $args['product_orderby'];
		if( isset( $args['product_order'] ) )
			$order = $args['product_order'];
	}
	$post_type = 'wpsc-product';
	$args = array(
		'post_type' => $post_type,
		'orderby' => $orderby,
		'order' => $order,
		'offset' => $offset,
		'posts_per_page' => $limit_volume,
		'post_status' => wpsc_ce_post_statuses( array( 'inherit' ) ),
		'no_found_rows' => false,
		'fields' => 'ids'
	);
	if( $product_categories ) {
		$term_taxonomy = 'wpsc_product_category';
		$args['tax_query'] = array(
			array(
				'taxonomy' => $term_taxonomy,
				'field' => 'id',
				'terms' => $product_categories
			)
		);
	}
	if( $product_tags ) {
		$term_taxonomy = 'product_tag';
		$args['tax_query'] = array(
			array(
				'taxonomy' => $term_taxonomy,
				'field' => 'id',
				'terms' => $product_tags
			)
		);
	}
	if( $product_status )
		$args['post_status'] = wpsc_ce_post_statuses( $product_status, true );
	$products = array();
	$product_ids = new WP_Query( $args );
	if( $product_ids->posts ) {
		foreach( $product_ids->posts as $product_id )
			$products[] = $product_id;
		unset( $product_ids, $product_id );
	}
	return $products;

}

function wpsc_ce_get_product_data( $product_id = 0, $args = array() ) {

	global $export;

	$product = get_post( $product_id );
	$product_data = wpsc_ce_get_product_meta( $product_id );

	$product->parent_id = '';
	$product->parent_sku = '';
	if( $product->post_parent ) {
		$product->parent_id = $product->post_parent;
		$product->parent_sku = get_product_meta( $product->post_parent, 'sku', true );
	}
	$product->product_id = $product->ID;
	$product->sku = wpsc_ce_clean_html( get_product_meta( $product->ID, 'sku', true ) );
	$product->name = wpsc_ce_clean_html( get_the_title( $product->ID ) );
	$product->is_variation = false;
		$term_taxonomy = 'wpsc-variation';
	if( $product->variations = wp_get_object_terms( $product->ID, $term_taxonomy ) ) {
		$product->is_variation = true;
		if( count( $product->variations ) == 1 ) {
			$product->variations = $product->variations[0];
			$variation = get_term( $product->variations->term_id, $term_taxonomy );
			$parent_variation = get_term( $product->variations->parent, $term_taxonomy );
			$product->name = wpsc_ce_clean_html( str_replace( ' (' . $variation->name . ')', '', $product->post_title ) . '|' . $parent_variation->name . '|' . $variation->name );
		}
	}
	$product->description = wpsc_ce_clean_html( $product->post_content );
	$product->additional_description = wpsc_ce_clean_html( $product->post_excerpt );
	$product->price = 0;
	if( get_product_meta( $product->ID, 'price', true ) )
		$product->price = get_product_meta( $product->ID, 'price', true );
		$product->sale_price = 0;
	if( get_product_meta( $product->ID, 'special_price', true ) )
		$product->sale_price = get_product_meta( $product->ID, 'special_price', true );
	$product->slug = $product->post_name;
	$product->permalink = get_permalink( $product->ID );
	$product->post_date = wpsc_ce_format_date( $product->post_date );
	$product->post_modified = wpsc_ce_format_date( $product->post_modified );
	$product->weight = wpsc_ce_convert_product_raw_weight( $product_data['weight'], $product_data['weight_unit'] );
	if( !$product->weight )
		$product->weight = 0;
	if( isset( $product_data['weight_unit'] ) )
		$product->weight_unit = $product_data['weight_unit'];
	if( !$product->weight_unit )
		$product->weight_unit = 'kg';
	if( isset( $product_data['dimensions']['height'] ) )
		$product->height = trim( $product_data['dimensions']['height'] );
	if( !$product->height )
		$product->height = 0;
	if( isset( $product_data['dimensions']['height_unit'] ) )
		$product->height_unit = $product_data['dimensions']['height_unit'];
	if( !$product->height_unit )
		$product->height_unit = 'in';
	if( isset( $product_data['dimensions']['width'] ) )
		$product->width = trim( $product_data['dimensions']['width'] );
	if( !$product->width )
		$product->width = 0;
	if( isset( $product_data['dimensions']['width_unit'] ) )
		$product->width_unit = $product_data['dimensions']['width_unit'];
	if( !$product->width_unit )
		$product->width_unit = 'in';
	if( isset( $product_data['dimensions']['length'] ) )
		$product->length = trim( $product_data['dimensions']['length'] );
	if( !$product->length )
		$product->length = 0;
	if( isset( $product_data['dimensions']['length_unit'] ) )
		$product->length_unit = $product_data['dimensions']['length_unit'];
	if( !$product->length_unit )
		$product->length_unit = 'in';
	$product->category = wpsc_ce_clean_html( wpsc_ce_get_product_assoc_categories( $product->ID ) );
	$product->tag = wpsc_ce_clean_html( wpsc_ce_get_product_assoc_tags( $product->ID ) );
	$product->image = wpsc_ce_get_product_assoc_images( $product->ID );
	$product->quantity_limited = $product_data['quantity_limited'];
	$product->quantity = get_product_meta( $product->ID, 'stock', true );
	if( $product->quantity_limited && empty( $product->quantity ) )
		$product->quantity = 0;
	$product->notify_oos = __( 'No', 'wp-e-commerce-exporter' );
	if( isset( $product_data['notify_when_none_left'] ) )
		$product->notify_oos = __( 'Yes', 'wp-e-commerce-exporter' );
	$product->unpublish_oos = __( 'No', 'wp-e-commerce-exporter' );
	if( isset( $product_data['unpublish_when_none_left'] ) )
		$product->unpublish_oos = __( 'Yes', 'wp-e-commerce-exporter' );
	if( isset( $product_data['external_link'] ) )
		$product->external_link = $product_data['external_link'];
	if( isset( $product_data['external_link_text'] ) )
		$product->external_link_text = wpsc_ce_clean_html( $product_data['external_link_text'] );
	if( isset( $product_data['external_link_target'] ) )
		$product->external_link_target = $product_data['external_link_target'];
	if( isset( $product_data['shipping']['local'] ) )
		$product->local_shipping = $product_data['shipping']['local'];
	if( isset( $product_data['shipping']['international'] ) )
		$product->international_shipping = $product_data['shipping']['international'];
	$product->no_shipping = __( 'No', 'wp-e-commerce-exporter' );
	if( $product_data['no_shipping'] == 1 )
		$product->no_shipping = __( 'Yes', 'wp-e-commerce-exporter' );
	$product->taxable_amount = $product_data['wpec_taxes_taxable_amount'];
	$product->tax_bands = wpsc_ce_format_tax_bands( $product_data['wpec_taxes_band'] );
	$product->not_taxable = __( 'No', 'wp-e-commerce-exporter' );
	if( $product->taxable_amount )
		$product->not_taxable = __( 'Yes', 'wp-e-commerce-exporter' );
	$product->product_status = wpsc_ce_format_product_status( $product->post_status, $product );
	$product->comment_status = wpsc_ce_format_comment_status( $product->comment_status, $product );

	// Advanced Google Product Feed - http://plugins.leewillis.co.uk/downloads/wp-e-commerce-product-feeds/
	if( function_exists( 'wpec_gpf_install' ) ) {
		$product->gpf_data = get_post_meta( $product->ID, '_wpec_gpf_data', true );
		$product->gpf_availability = ( isset( $product->gpf_data['availability'] ) ? wpsc_ce_format_gpf_availability( $product->gpf_data['availability'] ) : '' );
		$product->gpf_condition = ( isset( $product->gpf_data['condition'] ) ? wpsc_ce_format_gpf_condition( $product->gpf_data['condition'] ) : '' );
		$product->gpf_brand = ( isset( $product->gpf_data['brand'] ) ? $product->gpf_data['brand'] : '' );
		$product->gpf_product_type = ( isset( $product->gpf_data['product_type'] ) ? $product->gpf_data['product_type'] : '' );
		$product->gpf_google_product_category = ( isset( $product->gpf_data['google_product_category'] ) ? $product->gpf_data['google_product_category'] : '' );
		$product->gpf_gtin = ( isset( $product->gpf_data['gtin'] ) ? $product->gpf_data['gtin'] : '' );
		$product->gpf_mpn = ( isset( $product->gpf_data['mpn'] ) ? $product->gpf_data['mpn'] : '' );
		$product->gpf_gender = ( isset( $product->gpf_data['gender'] ) ? $product->gpf_data['gender'] : '' );
		$product->gpf_age_group = ( isset( $product->gpf_data['age_group'] ) ? $product->gpf_data['age_group'] : '' );
		$product->gpf_color = ( isset( $product->gpf_data['color'] ) ? $product->gpf_data['color'] : '' );
		$product->gpf_size = ( isset( $product->gpf_data['size'] ) ? $product->gpf_data['size'] : '' );
	}

	// All in One SEO Pack - http://wordpress.org/extend/plugins/all-in-one-seo-pack/
	if( function_exists( 'aioseop_activate' ) ) {
		$product->aioseop_keywords = get_post_meta( $product->ID, '_aioseop_keywords', true );
		$product->aioseop_description = get_post_meta( $product->ID, '_aioseop_description', true );
		$product->aioseop_title = get_post_meta( $product->ID, '_aioseop_title', true );
		$product->aioseop_titleatr = get_post_meta( $product->ID, '_aioseop_titleatr', true );
		$product->aioseop_menulabel = get_post_meta( $product->ID, '_aioseop_menulabel', true );
	}

	// Custom Fields - http://wordpress.org/plugins/wp-e-commerce-custom-fields/
	if( isset( $args['custom_fields'] ) ) {
		foreach( $args['custom_fields'] as $custom_field ) {
			$product->{'attribute_' . $custom_field} = maybe_unserialize( get_product_meta( $product->ID, $custom_field, true ) );
			if( !empty( $product->{'attribute_' . $custom_field} ) ) {
				if( is_array( $product->{'attribute_' . $custom_field} ) ) {
					$output = '';
					$attributes = $product->{'attribute_' . $custom_field};
					foreach( $attributes as $attribute ) {
						$output .= $attribute . $export->category_separator;
					}
					$output = substr( $output, 0, -1 );
					$product->{'attribute_' . $custom_field} = $output;
					unset( $attributes, $attribute, $output );
				}
			}
		}
	}

	// Related Products - http://www.visser.com.au/plugins/related-products/
	if( isset( $product_data['wpsc_rp_manual'] ) )
		$product->related_products = wpsc_ce_get_product_assoc_related_products( $product->ID );

	// Simple Product Options - http://wordpress.org/plugins/wp-e-commerce-simple-product-options/
	if( isset( $simple_product_options ) ) {
		foreach( $simple_product_options as $simple_product_option )
			$product->{'simple_product_option_' . $simple_product_option} = wpsc_ce_get_product_assoc_simple_product_options( $product->ID, $simple_product_option );
	}

	// WordPress SEO - http://wordpress.org/plugins/wordpress-seo/
	if( function_exists( 'wpseo_admin_init' ) ) {
		$product->wpseo_focuskw = get_post_meta( $product->ID, '_yoast_wpseo_focuskw', true );
		$product->wpseo_metadesc = get_post_meta( $product->ID, '_yoast_wpseo_metadesc', true );
		$product->wpseo_title = get_post_meta( $product->ID, '_yoast_wpseo_title', true );
		$product->wpseo_googleplus_description = get_post_meta( $product->ID, '_yoast_wpseo_google-plus-description', true );
		$product->wpseo_opengraph_description = get_post_meta( $product->ID, '_yoast_wpseo_opengraph-description', true );
	}

	// Allow Plugin/Theme authors to add support for additional Product columns
	return apply_filters( 'wpsc_ce_product_item', $product, $product->ID );

}

function wpsc_ce_get_product_meta( $product_id = 0 ) {

	$product_data = array();
	if( $product_id ) {
		$defaults = array(
			'weight' => '',
			'weight_unit' => '',
			'dimensions' => array( 'height_unit' => '', 'width_unit' => '', 'length_unit' => '', 'height' => '', 'width' => '', 'length' => '' ),
			'shipping'   => array( 'local' => '', 'international' => '' ),
			'no_shipping' => '',
			'display_weight_as' => '',
			'quantity_limited' => '',
			'external_link' => '',
			'wpec_taxes_band' => '',
			'wpec_taxes_taxable_amount' => ''
		);
		$product_data = get_post_meta( $product_id, '_wpsc_product_metadata', true );
		$product_data = wp_parse_args( $product_data, $defaults );
	}
	return $product_data;

}

// Returns the Product Images associated to a specific Product
function wpsc_ce_get_product_assoc_images( $product_id = 0 ) {

	global $export;

	$output = '';
	$post_type = 'attachment';
	$args = array(
		'post_type' => $post_type,
		'post_parent' => $product_id,
		'post_status' => 'inherit',
		'post_mime_type' => 'image',
		'numberposts' => -1
	);
	$images = get_children( $args );
	if( $images ) {
		/* Check for Featured Image */
		$featured_image = get_post_meta( $product_id, '_thumbnail_id', true );
		if( $featured_image ) {
			$image = get_post( $featured_image );
			$output .= $image->guid . $export->category_separator;
		} else {
			$featured_image = 0;
		}
		foreach( $images as $image ) {
			if( $featured_image <> $image->ID )
				$output .= $image->guid . $export->category_separator;
		}
		unset( $featured_image );
		$output = substr( $output, 0, -1 );
	}
	unset( $images );
	return $output;

}

// Returns Product Categories associated to a specific Product
function wpsc_ce_get_product_assoc_categories( $product_id = 0 ) {

	global $export;

	$output = '';
	$term_taxonomy = 'wpsc_product_category';
	if( $product_id )
		$categories = wp_get_object_terms( $product_id, $term_taxonomy );
	if( $categories ) {
		$size = count( $categories );
		for( $i = 0; $i < $size; $i++ ) {
			if( $categories[$i]->parent == '0' ) {
				$output .= $categories[$i]->name . $export->category_separator;
			} else {
				// Check if Parent -> Child
				$parent_category = get_term( $categories[$i]->parent, $term_taxonomy );
				// Check if Parent -> Child -> Subchild
				if( $parent_category->parent == '0' ) {
					$output .= $parent_category->name . '>' . $categories[$i]->name . $export->category_separator;
					$output = str_replace( $parent_category->name . $export->category_separator, '', $output );
				} else {
					$root_category = get_term( $parent_category->parent, $term_taxonomy );
					$output .= $root_category->name . '>' . $parent_category->name . '>' . $categories[$i]->name . $export->category_separator;
					$output = str_replace( array(
						$root_category->name . '>' . $parent_category->name . $export->category_separator,
						$parent_category->name . $export->category_separator
					), '', $output );
				}
				unset( $root_category, $parent_category );
			}
		}
		$output = substr( $output, 0, -1 );
	} else {
		$output .= __( 'Uncategorized', 'wp-e-commerce-exporter' );
	}
	return $output;

}

// Returns Related Products associated to a specific Product
function wpsc_ce_get_product_assoc_related_products( $product_id ) {

	global $export;

	$output = '';
	$product_data = maybe_unserialize( get_product_meta( $product_id, 'product_metadata', true ) );
	if( isset( $product_data['wpsc_rp_manual'] ) && $product_data['wpsc_rp_manual'] ) {
		foreach( $product_data['wpsc_rp_manual'] as $related_product )
			$output .= $related_product . $export->category_separator;
		$output = substr( $output, 0, -1 );
	}
	return $output;

}

// Returns Simple Product Options associated to a specific Product
function wpsc_ce_get_product_assoc_simple_product_options( $product_id = 0, $product_option = '' ) {

	global $export;

	$output = '';
	if( $product_id ) {
		$term_taxonomy = 'wpec_product_option';
		$term = get_term_by( 'slug', $product_option, $term_taxonomy );
		$simple_product_options = wp_get_object_terms( $product_id, $term_taxonomy );
		if( $simple_product_options ) {
			$size = count( $simple_product_options );
			for( $i = 0; $i < $size; $i++ ) {
				if( $simple_product_options[$i]->parent == $term->term_id )
					$output .= $simple_product_options[$i]->name . $export->category_separator;
			}
			$output = substr( $output, 0, -1 );
		}
	}
	return $output;

}

// Returns a list of WP e-Commerce Product Categories to export process
function wpsc_ce_get_product_categories( $args = array(), $tree_structure = false ) {

	global $export;

	$output = '';
	if( $args ) {
		$orderby = $args['category_orderby'];
		$order = $args['category_order'];
	}
	$term_taxonomy = 'wpsc_product_category';
	$args = array(
		'orderby' => $orderby,
		'order' => $order,
		'hide_empty' => 0
	);
	$categories = get_terms( $term_taxonomy, $args );
	if( $tree_structure ) {
		$output = array();
		if( $categories ) {
			$size = count( $categories );
			for( $i = 0; $i < $size; $i++ ) {
				if( $categories[$i]->parent == '0' ) {
					$output[] = $categories[$i]->name;
				} else {
					// Check if Parent -> Child
					$parent_category = get_term( $categories[$i]->parent, $term_taxonomy );
					// Check if Parent -> Child -> Subchild
					if( $parent_category->parent == '0' ) {
						$temp = $parent_category->name . '>' . $categories[$i]->name . $export->category_separator;
						$output[] = substr( str_replace( $parent_category->name . $export->category_separator, '', $temp ), 0, -1 );
					} else {
						$root_category = get_term( $parent_category->parent, $term_taxonomy );
						$temp = $root_category->name . '>' . $parent_category->name . '>' . $categories[$i]->name . $export->category_separator;
						$output[] = substr( str_replace( array(
							$root_category->name . '>' . $parent_category->name . $export->category_separator,
							$parent_category->name . $export->category_separator
						), '', $temp ), 0, -1 );
					}
					unset( $root_category, $parent_category );
				}
			}
		}
	} else {
		if( $categories ) {
			foreach( $categories as $key => $category ) {
				$categories[$key]->parent_id = $category->parent;
			}
		}
		if( $categories )
			$output = $categories;
	}
	return $output;

}

// Returns a list of Category export columns
function wpsc_ce_get_category_fields( $format = 'full' ) {

	$fields = array();
	$fields[] = array(
		'name' => 'term_id',
		'label' => __( 'Term ID', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'name',
		'label' => __( 'Category Name', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'slug',
		'label' => __( 'Category Slug', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'parent_id',
		'label' => __( 'Parent Term ID', 'wp-e-commerce-exporter' )
	);

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'wp-e-commerce-exporter' )
	);
*/

	// Allow Plugin/Theme authors to add support for additional Category columns
	$fields = apply_filters( 'wpsc_ce_category_fields', $fields );

	if( $remember = wpsc_ce_get_option( 'categories_fields', array() ) ) {
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
function wpsc_ce_get_category_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = wpsc_ce_get_category_fields();
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

/* End of: Common */
?>