<?php
// Returns a list of WP e-Commerce Product Tags to export process
function wpsc_ce_get_product_tags( $args = array() ) {

	$term_taxonomy = 'product_tag';
	$defaults = array(
		'orderby' => 'name',
		'order' => 'ASC',
		'hide_empty' => 0
	);
	$args = wp_parse_args( $args, $defaults );
	if( $tags = get_terms( $term_taxonomy, $args ) ) {
		$size = count( $tags );
		for( $i = 0; $i < $size; $i++ ) {
			$tags[$i]->disabled = 0;
			if( $tags[$i]->count == 0 )
				$tags[$i]->disabled = 1;
		}
		return $tags;
	}

}

// Returns a list of Product Tag export columns
function wpsc_ce_get_tag_fields( $format = 'full' ) {

	$fields = array();
	$fields[] = array(
		'name' => 'term_id',
		'label' => __( 'Term ID', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'name',
		'label' => __( 'Tag Name', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'slug',
		'label' => __( 'Tag Slug', 'wp-e-commerce-exporter' )
	);

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'wp-e-commerce-exporter' )
	);
*/

	// Allow Plugin/Theme authors to add support for additional Product Tag columns
	$fields = apply_filters( 'wpsc_ce_tag_fields', $fields );

	if( $remember = wpsc_ce_get_option( 'tags_fields', array() ) ) {
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
function wpsc_ce_get_tag_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = wpsc_ce_get_tag_fields();
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