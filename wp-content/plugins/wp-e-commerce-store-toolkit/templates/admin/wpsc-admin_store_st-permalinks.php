<h3 class="form_group"><?php _e( 'Permalink Settings', 'wp-e-commerce-store-toolkit' ); ?></h3>
<p><?php _e( 'From time to time you may need to adjust the Permalink Settings for WP e-Commerce Pages.', 'wp-e-commerce-store-toolkit' ); ?></p>
<table class="wpsc_options form-table">

	<tr>
		<th><strong><?php _e( 'Products Page', 'wp-e-commerce-store-toolkit' ); ?></strong></th>
		<td>
<?php if( $pages ) { ?>
			<select name="wpsc_options[product_list_url]">
				<option><?php _e( 'Unassigned', 'wp-e-commerce-store-toolkit' ); ?></option>
	<?php foreach( $pages as $page ) { ?>
				<option value="<?php echo $page->guid; ?>"<?php selected( $page->guid, $product_list_url ); ?>><?php echo $page->post_title; ?> (#<?php echo $page->ID; ?>)</option>
	<?php } ?>
			</select>
<?php } ?>
			<a href="<?php echo $product_list_url; ?>" target="_blank"><?php _e( 'Preview', 'wp-e-commerce-store-toolkit' ); ?></a>
		</td>
	</tr>

	<tr>
		<th><strong><?php _e( 'Checkout', 'wp-e-commerce-store-toolkit' ); ?></strong></th>
		<td>
<?php if( $pages ) { ?>
			<select name="wpsc_options[checkout_url]">
				<option><?php _e( 'Unassigned', 'wp-e-commerce-store-toolkit' ); ?></option>
	<?php foreach( $pages as $page ) { ?>
				<option value="<?php echo $page->guid; ?>"<?php selected( $page->guid, $checkout_url ); ?>><?php echo $page->post_title; ?> (#<?php echo $page->ID; ?>)</option>
	<?php } ?>
			</select>
<?php } ?>
			<a href="<?php echo $checkout_url; ?>" target="_blank"><?php _e( 'Preview', 'wp-e-commerce-store-toolkit' ); ?></a><br />
<?php if( $pages ) { ?>
			<select name="wpsc_options[shopping_cart_url]">
				<option><?php _e( 'Unassigned', 'wp-e-commerce-store-toolkit' ); ?></option>
	<?php foreach( $pages as $page ) { ?>
				<option value="<?php echo $page->guid; ?>"<?php selected( $page->guid, $shopping_cart_url ); ?>><?php echo $page->post_title; ?> (#<?php echo $page->ID; ?>)</option>
	<?php } ?>
			</select>
<?php } ?>
			<a href="<?php echo $shopping_cart_url; ?>" target="_blank"><?php _e( 'Preview', 'wp-e-commerce-store-toolkit' ); ?></a>
			<p class="description"><?php _e( 'Set both values of these dropdowns to the same Checkout page, this is due to WP e-Commerce storing the Checkout URL in two places.', 'wp-e-commerce-store-toolkit' ); ?></p>
		</td>
	</tr>

	<tr>
		<th><strong><?php _e( 'Transaction Results', 'wp-e-commerce-store-toolkit' ); ?></strong></th>
		<td>
<?php if( $pages ) { ?>
			<select name="wpsc_options[transact_url]">
				<option><?php _e( 'Unassigned', 'wp-e-commerce-store-toolkit' ); ?></option>
	<?php foreach( $pages as $page ) { ?>
				<option value="<?php echo $page->guid; ?>"<?php selected( $page->guid, $transact_url ); ?>><?php echo $page->post_title; ?> (#<?php echo $page->ID; ?>)</option>
	<?php } ?>
			</select>
<?php } ?>
			<a href="<?php echo $transact_url; ?>" target="_blank"><?php _e( 'Preview', 'wp-e-commerce-store-toolkit' ); ?></a>
		</td>
	</tr>

	<tr>
		<th><strong><?php _e( 'My Account', 'wp-e-commerce-store-toolkit' ); ?></strong></th>
		<td>
<?php if( $pages ) { ?>
			<select name="wpsc_options[user_account_url]">
				<option><?php _e( 'Unassigned', 'wp-e-commerce-store-toolkit' ); ?></option>
	<?php foreach( $pages as $page ) { ?>
				<option value="<?php echo $page->guid; ?>"<?php selected( $page->guid, $user_account_url ); ?>><?php echo $page->post_title; ?> (#<?php echo $page->ID; ?>)</option>
	<?php } ?>
			</select>
<?php } ?>
			<a href="<?php echo $user_account_url; ?>" target="_blank"><?php _e( 'Preview', 'wp-e-commerce-store-toolkit' ); ?></a>
		</td>
	</tr>

</table>