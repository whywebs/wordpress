<?php
if( is_admin() ) {

	/* Start of: WordPress Administration */

	// Returns number of an Export type prior to export, used on Store Exporter screen
	function wpsc_ce_return_count( $export_type ) {

		global $wpdb;

		$count_sql = null;
		switch( $export_type ) {

			case 'products':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_product_list`";
				break;

			case 'variations':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_product_variations`";
				break;

			case 'images':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_product_images`";
				break;

			case 'files':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_product_files`";
				break;

			case 'categories':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_product_categories`";
				break;

			case 'tags':
				$term_taxonomy = 'product_tag';
				$count = wp_count_terms( $term_taxonomy );
				break;

			case 'orders':
			case 'coupons':
			case 'customers':
				if( function_exists( 'wpsc_cd_return_count' ) )
					$count = wpsc_cd_return_count( $export_type );
				break;

			// 3rd Party

			case 'wishlist':
				$count_sql = "SELECT COUNT(`id`) FROM `" . $wpdb->prefix . "wpsc_wishlist`";
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

	// Returns a list of Category export columns
	function wpsc_ce_get_category_fields( $format = 'full' ) {
	
		$fields = array();
		$fields[] = array(
			'name' => 'name',
			'label' => __( 'Category', 'wp-e-commerce-exporter' ),
			'default' => 1
		);
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

	if( !function_exists( 'wpsc_find_purchlog_status_name' ) ) {
		function wpsc_find_purchlog_status_name( $status ) {

			global $wpdb;

			$output = $status;
			if( !empty( $status ) ) {
				$status_name_sql = $wpdb->prepare( "SELECT `name` FROM `" . $wpdb->prefix . "wpsc_purchase_statuses` WHERE `id` = %d LIMIT 1", $status );
				$status_name = $wpdb->get_var( $status_name_sql );
				if( $status_name )
					$output = $status_name;
			}
			return $output;

		}
	}

	/* End of: WordPress Administration */

}

/* Start of: Common */

// Export process for CSV file
function wpsc_ce_export_dataset( $export_type, &$output = null ) {

	global $wpdb, $export;

	$separator = $export->delimiter;
	$export->columns = array();
	$export->total_rows = 0;
	$export->total_columns = 0;
	set_transient( WPSC_CE_PREFIX . '_running', time(), wpsc_ce_get_option( 'timeout', MINUTE_IN_SECONDS ) );

	switch( $export_type ) {

		// Products
		case 'products':
			$export->columns = array(
				__( 'SKU', 'wp-e-commerce-exporter' ),
				__( 'Product Name', 'wp-e-commerce-exporter' ),
				__( 'Description', 'wp-e-commerce-exporter' ),
				__( 'Additional Description', 'wp-e-commerce-exporter' ),
				__( 'Price', 'wp-e-commerce-exporter' ),
				__( 'Sale Price', 'wp-e-commerce-exporter' ),
				__( 'Slug', 'wp-e-commerce-exporter' ),
				__( 'Permalink', 'wp-e-commerce-exporter' ),
				__( 'Weight', 'wp-e-commerce-exporter' ),
				__( 'Weight Unit', 'wp-e-commerce-exporter' ),
				__( 'Height', 'wp-e-commerce-exporter' ),
				__( 'Height Unit', 'wp-e-commerce-exporter' ),
				__( 'Width', 'wp-e-commerce-exporter' ),
				__( 'Width Unit', 'wp-e-commerce-exporter' ),
				__( 'Length', 'wp-e-commerce-exporter' ),
				__( 'Length Unit', 'wp-e-commerce-exporter' ),
				__( 'Category', 'wp-e-commerce-exporter' ),
				__( 'Tag', 'wp-e-commerce-exporter' ),
				__( 'Image', 'wp-e-commerce-exporter' ),
				__( 'Quantity', 'wp-e-commerce-exporter' ),
				__( 'File Download', 'wp-e-commerce-exporter' ),
				__( 'External Link', 'wp-e-commerce-exporter' ),
				__( 'Merchant Notes', 'wp-e-commerce-exporter' ),
				__( 'Local Shipping Fee', 'wp-e-commerce-exporter' ),
				__( 'International Shipping Fee', 'wp-e-commerce-exporter' ),
				__( 'Product Status', 'wp-e-commerce-exporter' )
			);
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
				foreach( $products as $product ) {

					if( $export->export_format == 'xml' )
						$child = $output->addChild( substr( $export->type, 0, -1 ) );

					$product->sku = get_product_meta( $product->ID, 'sku' );
					$product->slug = get_product_meta( $product->ID, 'url_name' );
					$product->permalink = wpsc_product_url( $product->ID );
					$product->dimensions = get_product_meta( $product->ID, 'dimensions' );
					if( $product->dimensions ) {
						$product->height = $product->dimensions['height'];
						$product->height_unit = $product->dimensions['height_unit'];
						$product->width = $product->dimensions['width'];
						$product->width_unit = $product->dimensions['width_unit'];
						$product->length = $product->dimensions['length'];
						$product->length_unit = $product->dimensions['length_unit'];
					}
					$product->external_link = get_product_meta( $product->ID, 'external_link' );
					$product->merchant_notes = get_product_meta( $product->ID, 'merchant_notes' );
					$product->category = wpsc_ce_get_product_assoc_categories( $product->ID );
					$product->tags = wpsc_ce_get_product_assoc_tags( $product->ID );

					foreach( $product as $key => $value ) {
						if( $export->export_format == 'csv' )
							$product->$key = wpsc_ce_escape_csv_value( $value );
						else if( $export->export_format == 'xml' )
							$child->addChild( $key, htmlspecialchars( $product->$value ) );
					}

					if( $export->export_format == 'csv' ) {
						$output .= 
							$product->sku . $separator . 
							$product->name . $separator . 
							$product->description . $separator . 
							$product->additional_description . $separator . 
							$product->price . $separator . 
							$product->sale_price . $separator . 
							$product->permalink . $separator . 
							$product->weight . $separator . 
							$product->weight_unit . $separator . 
							$product->height . $separator . 
							$product->height_unit . $separator . 
							$product->width . $separator . 
							$product->width_unit . $separator . 
							$product->length . $separator . 
							$product->length_unit . $separator . 
							$product->category . $separator . 
							$product->tag . $separator . 
							$product->image . $separator . 
							$product->quantity . $separator . 
							$product->file_download . $separator . 
							$product->external_link . $separator . 
							$product->merchant_notes . $separator . 
							$product->local_shipping . $separator . 
							$product->international_shipping . $separator . 
							$product->status . 
						"\n";
					}
				}
				unset( $products, $product );
			}
			$export->data_memory_end = wpsc_ce_current_memory_usage();
			break;

		// Categories
		case 'categories':
			$export->columns = array(
				__( 'Category', 'wp-e-commerce-exporter' )
			);
			$export->data_memory_start = wpsc_ce_current_memory_usage();
			if( $categories = wpsc_ce_get_product_categories() ) {
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
				foreach( $categories as $category ) {

					if( $export->export_format == 'xml' )
						$child = $output->addChild( str_replace( 'ies', 'y', $export->type ) );

					if( $export->export_format == 'csv' )
						$output .= wpsc_ce_escape_csv_value( $category->name, $export->delimiter, $export->escape_formatting ) . "\n";
					else if( $export->export_format == 'xml' )
						$child->addChild( $key, htmlspecialchars( $category->name ) );
				}
				unset( $categories, $category );
			}
			$export->data_memory_end = wpsc_ce_current_memory_usage();
			break;

		// Tags
		case 'tags':
			$export->columns = array(
				__( 'Tags', 'wp-e-commerce-exporter' )
			);
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
				foreach( $tags as $tag ) {

					if( $export->export_format == 'xml' )
						$child = $output->addChild( substr( $export->type, 0, -1 ) );

					if( $export->export_format == 'csv' )
						$output .= wpsc_ce_escape_csv_value( $tag->name, $export->delimiter, $export->escape_formatting ) . "\n";
					else if( $export->export_format == 'xml' )
						$child->addChild( $key, htmlspecialchars( $tag->$name ) );
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

	global $wpdb;

	$products_sql = "SELECT `id` AS ID, `name`, `description`, `additional_description`, `publish` as status, `price`, `weight`, `weight_unit`, `pnp` as local_shipping, `international_pnp` as international_shipping, `quantity`, `special_price` as sale_price FROM `" . $wpdb->prefix . "wpsc_product_list` WHERE `active` = 1";
	$products = $wpdb->get_results( $products_sql );
	return $products;

}

// Returns Product Categories associated to a specific Product
function wpsc_ce_get_product_assoc_categories( $product_id = 0 ) {

	global $export, $wpdb;

	$output = '';
	if( $product_id ) {
		$categories_sql = $wpdb->prepare( "SELECT wpsc_product_categories.`name` FROM `" . $wpdb->prefix . "wpsc_item_category_assoc` as item_category_assoc, `" . $wpdb->prefix . "wpsc_product_categories` as wpsc_product_categories WHERE item_category_assoc.category_id = wpsc_product_categories.id AND item_category_assoc.`product_id` = %d", $product_id );
		$categories = $wpdb->get_results( $categories_sql );
		if( $categories ) {
			foreach( $categories as $category ) {
				$output .= $category->name . $export->category_separator;
			}
			$output = substr( $output, 0, -1 );
		} else {
			$output .= __( 'Uncategorized', 'wp-e-commerce-exporter' );
		}
	}
	return $output;

}

// Returns a list of WP e-Commerce Product Categories to export process
function wpsc_ce_get_product_categories( $args = array() ) {

	global $wpdb;

	$output = '';
	$categories_sql = "SELECT `name` FROM `" . $wpdb->prefix . "wpsc_product_categories` WHERE `active` = 1";
	$categories = $wpdb->get_results( $categories_sql );
	if( !empty( $categories ) )
		$output = $categories;
	return $output;

}

/* End of: Common */
?>