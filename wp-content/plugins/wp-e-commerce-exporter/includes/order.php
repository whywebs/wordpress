<?php
// Returns a list of Order export columns
function wpsc_ce_get_order_fields( $format = 'full' ) {

	$fields = array();
	$fields[] = array(
		'name' => 'purchase_id',
		'label' => __( 'Purchase ID', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_total',
		'label' => __( 'Purchase Total', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'payment_gateway',
		'label' => __( 'Payment Gateway', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'shipping_method',
		'label' => __( 'Shipping Method', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'payment_status',
		'label' => __( 'Payment Status', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'payment_status_int',
		'label' => __( 'Payment Status (number)', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_date',
		'label' => __( 'Purchase Date', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'purchase_time',
		'label' => __( 'Purchase Time', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'tracking_id',
		'label' => __( 'Tracking ID', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'transaction_id',
		'label' => __( 'Transaction ID', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'session_id',
		'label' => __( 'Session ID', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_personalisation',
		'label' => __( 'Order Personalisation', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_id',
		'label' => __( 'User ID', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'username',
		'label' => __( 'Username', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'user_role',
		'label' => __( 'User Role', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'notes',
		'label' => __( 'Notes', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'referral',
		'label' => __( 'Referral Source', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_product_id',
		'label' => __( 'Order Items: Product ID', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_sku',
		'label' => __( 'Order Items: SKU', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_product_name',
		'label' => __( 'Order Items: Product Name', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_price',
		'label' => __( 'Order Items: Price', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_rrp',
		'label' => __( 'Order Items: RRP', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_quantity',
		'label' => __( 'Order Items: Quantity', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_shipping',
		'label' => __( 'Order Items: Shipping', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_tax',
		'label' => __( 'Order Items: Tax', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_total',
		'label' => __( 'Order Items: Total', 'wp-e-commerce-exporter' )
	);
	$fields[] = array(
		'name' => 'order_items_product_personalisation',
		'label' => __( 'Order Items: Product Personalisation', 'wp-e-commerce-exporter' )
	);
	$fields = array_merge_recursive( $fields, wpsc_ce_get_checkout_fields() );
/*
	$fields[] = array(
		'name' => '',
		'label' => __( '', 'wp-e-commerce-exporter' )
	);
*/

	// Allow Plugin/Theme authors to add support for additional Order columns
	$fields = apply_filters( 'wpsc_ce_order_fields', $fields );

	if( $remember = wpsc_ce_get_option( 'orders_fields', array() ) ) {
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
function wpsc_ce_get_order_field( $name = null, $format = 'name' ) {

	$output = '';
	if( $name ) {
		$fields = wpsc_ce_get_order_fields();
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

function wpsc_ce_get_checkout_fields( $format = 'full' ) {

	global $wpdb;

	$fields = array();
	$checkout_fields_sql = "SELECT * FROM `" . $wpdb->prefix . "wpsc_checkout_forms` WHERE `active` = 1 AND `type` <> 'heading'";
	$checkout_fields = $wpdb->get_results( $checkout_fields_sql );
	if( $checkout_fields ) {
		foreach( $checkout_fields as $key => $checkout_field ) {
			$fields[] = array(
				'name' => sprintf( 'checkout_%d', $checkout_field->id ),
				'label' => sprintf( 'Checkout: %s', $checkout_field->name )
			);
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

function wpsc_ce_get_submited_form_data( $checkout_fields = '', $order_id = 0 ) {

	global $wpdb;

	$output = array();
	if( $checkout_fields ) {
		foreach( $checkout_fields as $checkout_key => $checkout_field ) {
			$key = str_replace( 'checkout_', '', $checkout_key );
			if( $key ) {
				$value_sql = $wpdb->prepare( "SELECT `value` FROM `" . $wpdb->prefix . "wpsc_submited_form_data` WHERE `form_id` = %d AND `log_id` = %d LIMIT 1", $key, $order_id );
				$value = $wpdb->get_var( $value_sql );
				if( $value )
					$checkout_fields[$checkout_key] = $value;
				else
					unset( $checkout_fields[$checkout_key] );
			}
		}
		$output = $checkout_fields;
	}
	return $output;

}


// HTML template for disabled Filter Orders by Date widget on Store Exporter screen
function wpsc_ce_orders_filter_by_date() {

	$current_month = date( 'F' );
	$last_month = date( 'F', mktime( 0, 0, 0, date( 'n' )-1, 1, date( 'Y' ) ) );
	$order_dates_from = '-';
	$order_dates_to = '-';

	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-date" /> <?php _e( 'Filter Orders by Order Date', 'wp-e-commerce-exporter' ); ?></label></p>
<div id="export-orders-filters-date" class="separator">
	<ul>
		<li>
			<label><input type="radio" name="order_dates_filter" value="current_month" disabled="disabled" /> <?php _e( 'Current month', 'wp-e-commerce-exporter' ); ?> (<?php echo $current_month; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="last_month" disabled="disabled" /> <?php _e( 'Last month', 'wp-e-commerce-exporter' ); ?> (<?php echo $last_month; ?>)</label>
		</li>
		<li>
			<label><input type="radio" name="order_dates_filter" value="manual" disabled="disabled" /> <?php _e( 'Manual', 'wp-e-commerce-exporter' ); ?></label>
			<div style="margin-top:0.2em;">
				<input type="text" size="10" maxlength="10" id="order_dates_from" name="order_dates_from" value="<?php echo $order_dates_from; ?>" class="text" disabled="disabled" /> to <input type="text" size="10" maxlength="10" id="order_dates_to" name="order_dates_to" value="<?php echo $order_dates_to; ?>" class="text" disabled="disabled" />
				<p class="description"><?php _e( 'Filter the dates of Orders to be included in the export. Default is the date of the first order to today.', 'wp-e-commerce-exporter' ); ?></p>
			</div>
		</li>
	</ul>
</div>
<!-- #export-orders-filters-date -->
<?php
	ob_end_flush();

}

// HTML template for disabled Filter Orders by Customer widget on Store Exporter screen
function wpsc_ce_orders_filter_by_customer() {

	ob_start(); ?>
<p><label for="order_customer"><?php _e( 'Filter Orders by Customer', 'wp-e-commerce-exporter' ); ?></label></p>
<div id="export-orders-filters-date" class="separator">
	<select id="order_customer" name="order_customer" disabled="disabled">
		<option value=""><?php _e( 'Show all customers', 'wp-e-commerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Filter Orders by Customer (unique e-mail address) to be included in the export. Default is to include all Orders.', 'wp-e-commerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-date -->
<?php
	ob_end_flush();

}

// HTML template for disabled Filter Orders by Order Status widget on Store Exporter screen
function wpsc_ce_orders_filter_by_status() {

	global $wpsc_purchlog_statuses;

	$order_statuses = $wpsc_purchlog_statuses;
	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-status" /> <?php _e( 'Filter Orders by Order Status', 'wp-e-commerce-exporter' ); ?></label></p>
<div id="export-orders-filters-status" class="separator">
	<ul>
<?php foreach( $order_statuses as $order_status ) { ?>
		<li><label><input type="checkbox" name="order_filter_status[<?php echo $order_status['order']; ?>]" value="<?php echo $order_status['order']; ?>" disabled="disabled" /> <?php echo $order_status['label']; ?></label></li>
<?php } ?>
	</ul>
	<p class="description"><?php _e( 'Select the Order Status you want to filter exported Orders by. Default is to include all Order Status options.', 'wp-e-commerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-status -->
<?php
	ob_end_flush();

}

// HTML template for disabled Filter Orders by Product widget on Store Exporter screen
function wpsc_ce_orders_filter_by_product() {

	$order_products = wpsc_ce_get_products();
	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-product" /> <?php _e( 'Filter Orders by Product', 'wp-e-commerce-exporter' ); ?></label></p>
<div id="export-orders-filters-product" class="separator">
<?php if( $order_products ) { ?>
	<ul>
	<?php foreach( $order_products as $order_product ) { ?>
		<li><label><input type="checkbox" name="order_filter_product[<?php echo $order_product; ?>]" value="<?php echo $order_product; ?>" disabled="disabled" /> <?php printf( '%s (#%d)', get_the_title( $order_product ), $order_product ); ?></label></li>
	<?php } ?>
	</ul>
<?php } ?>
	<p class="description"><?php _e( 'Filter Orders by Product(s) to be included in the export. Default is to include all Products.', 'wp-e-commerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-product -->
<?php
	ob_end_flush();

}

// HTML template for disabled Filter Orders by User Role widget on Store Exporter screen
function wpsc_ce_orders_filter_by_user_role() {

	$user_roles = wpsc_ce_get_user_roles();
	ob_start(); ?>
<p><label><input type="checkbox" id="orders-filters-user_role" /> <?php _e( 'Filter Orders by User Role', 'wp-e-commerce-exporter' ); ?></label></p>
<div id="export-orders-filters-user_role" class="separator">
	<ul>
<?php foreach( $user_roles as $key => $user_role ) { ?>
		<li><label><input type="checkbox" name="order_filter_user_role[<?php echo $key; ?>]" value="<?php echo $key; ?>" disabled="disabled" /> <?php echo ucfirst( $user_role['name'] ); ?></label></li>
<?php } ?>
	</ul>
	<p class="description"><?php _e( 'Select the User Roles you want to filter exported Orders by. Default is to include all User Role options.', 'wp-e-commerce-exporter' ); ?></p>
</div>
<!-- #export-orders-filters-status -->
<?php
	ob_end_flush();

}

// HTML template for disabled Order Sorting widget on Store Exporter screen
function wpsc_ce_orders_order_sorting() {

	ob_start(); ?>
<p><label><?php _e( 'Order Sorting', 'wp-e-commerce-exporter' ); ?></label></p>
<div>
	<select name="order_orderby" disabled="disabled">
		<option value="ID"><?php _e( 'Order ID', 'wp-e-commerce-exporter' ); ?></option>
		<option value="date"><?php _e( 'Date Created', 'wp-e-commerce-exporter' ); ?></option>
		<option value="payment_method"><?php _e( 'Payment Method', 'wp-e-commerce-exporter' ); ?></option>
		<option value="shipping_method"><?php _e( 'Shipping Method', 'wp-e-commerce-exporter' ); ?></option>
		<option value="rand"><?php _e( 'Random', 'wp-e-commerce-exporter' ); ?></option>
	</select>
	<select name="order_order" disabled="disabled">
		<option value="ASC"><?php _e( 'Ascending', 'wp-e-commerce-exporter' ); ?></option>
		<option value="DESC"><?php _e( 'Descending', 'wp-e-commerce-exporter' ); ?></option>
	</select>
	<p class="description"><?php _e( 'Select the sorting of Orders within the exported file. By default this is set to export Orders by Order ID in Desending order.', 'wp-e-commerce-exporter' ); ?></p>
</div>
<?php
	ob_end_flush();

}
?>