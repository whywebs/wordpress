<?php
// Returns a list of Customer export columns
function wpsc_ce_get_customer_fields( $format = 'full' ) {

	$fields = array();
	$fields[] = array(
		'name' => 'user_id',
		'label' => __( 'User ID', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_name',
		'label' => __( 'Username', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_role',
		'label' => __( 'User Role', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_full_name',
		'label' => __( 'Billing: Full Name', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_first_name',
		'label' => __( 'Billing: First Name', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_last_name',
		'label' => __( 'Billing: Last Name', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_street_address',
		'label' => __( 'Billing: Street Address', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_city',
		'label' => __( 'Billing: City', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_state',
		'label' => __( 'Billing: State (prefix)', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_zip_code',
		'label' => __( 'Billing: ZIP Code', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_country',
		'label' => __( 'Billing: Country (prefix)', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_country_full',
		'label' => __( 'Billing: Country', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_phone_number',
		'label' => __( 'Billing: Phone Number', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'billing_email',
		'label' => __( 'E-mail Address', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_full_name',
		'label' => __( 'Shipping: Full Name', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_first_name',
		'label' => __( 'Shipping: First Name', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_last_name',
		'label' => __( 'Shipping: Last Name', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_street_address',
		'label' => __( 'Shipping: Street Address', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_city',
		'label' => __( 'Shipping: City', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_state',
		'label' => __( 'Shipping: State (prefix)', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_zip_code',
		'label' => __( 'Shipping: ZIP Code', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_country',
		'label' => __( 'Shipping: Country (prefix)', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_country_full',
		'label' => __( 'Shipping: Country', 'wp-e-commerce-exporter' )
	);

/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'wp-e-commerce-exporter' )
	);
*/

	// Allow Plugin/Theme authors to add support for additional Customer columns
	$fields = apply_filters( 'wpsc_ce_customer_fields', $fields );

	if( $remember = wpsc_ce_get_option( 'customers_fields', array() ) ) {
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
function wpsc_ce_get_customer_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = wpsc_ce_get_customer_fields();
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