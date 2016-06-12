<form method="post" action="<?php the_permalink(); ?>" id="your-profile">

	<h3><?php _e( 'Presentation', 'wp-e-commerce-store-toolkit' ); ?></h3>

	<h4><?php _e( 'Status Indicator', 'wp-e-commerce-store-toolkit' ); ?></h4>
	<p><?php _e( 'Manage the colour styles assigned to each Sale Status from the Manage Sales screen in WP e-Commerce.', 'wp-e-commerce-store-toolkit' ); ?></p>
<?php if( $sale_statuses ) { ?>
	<table class="form-table">

	<?php foreach( $sale_statuses as $sale_status ) { ?>

					<tr id="status-<?php echo $sale_status['internalname']; ?>">
						<td>
							<label><strong><?php echo $sale_status['label']; ?></strong></label><br />
							<?php _e( 'Background', 'wp-e-commerce-store-toolkit' ); ?>: # <input type="text" name="options[sale_status_background][<?php echo $sale_status['internalname']; ?>]" size="6" value="<?php echo $sale_status_background[$sale_status['internalname']]; ?>" />
							<?php _e( 'Border', 'wp-e-commerce-store-toolkit' ); ?>: # <input type="text" name="options[sale_status_border][<?php echo $sale_status['internalname']; ?>]" size="6" value="<?php echo $sale_status_border[$sale_status['internalname']]; ?>" />
							<p class="description"><?php echo sprintf( __( 'Default background is: %s, with a border of: %s', 'wp-e-commerce-store-toolkit' ), '<code>' . $sale_status['default_background'] . '</code>', '<code>' . $sale_status['default_border'] . '</code>' ); ?>
						</td>
					</tr>

	<?php } ?>

	</table>
	<p class="description"><?php _e( 'Customise the colours for each Sale Status.', 'wp-e-commerce-store-toolkit' ); ?></p>
<?php } ?>

	<h4><?php _e( 'Buttons', 'wp-e-commerce-store-toolkit' ); ?></h4>
	<p><?php _e( 'Change the button text on Add To Cart button elements on the Products Page and Single Product screens.', 'wp-e-commerce-store-toolkit' ); ?></p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="addtocart-label"><?php _e( 'Add To Cart', 'wpsc_pe' ); ?>:</label></th>
			<td>
				<input type="text" id="addtocart-label" name="options[addtocart_label]" value="<?php echo $options['addtocart_label']; ?>" />
				<p class="description"><?php echo sprintf( __( 'Default: %s', 'wp-e-commerce-store-toolkit' ), '<code>' . __( 'Add To Cart', 'wpsc' ) . '</code>' ); ?></p>
			</td>
		</tr>
	</table>
	<p class="description"><?php _e( 'This option affects purchasable Products and does not affect Buy Now buttons which link to external pages.', 'wp-e-commerce-store-toolkit' ); ?></p>
	<p class="submit">
		<input type="submit" value="<?php _e( 'Save Changes', 'wp-e-commerce-store-toolkit' ); ?>" class="button-primary" />
	</p>
	<input type="hidden" name="action" value="update" />
	<?php wp_nonce_field( 'update', 'wpsc_st_update' ); ?>
</form>

<form method="post" action="<?php the_permalink(); ?>" class="nuke" id="uninstall-wpecommerce">
	<div>
		<h3><?php _e( 'Uninstall WP e-Commerce', 'wp-e-commerce-store-toolkit' ); ?></h3>
		<p><?php _e( 'Remove all traces of WP e-Commerce from the WordPress database as well as physical directories created by WP e-Commerce, this includes:', 'wp-e-commerce-store-toolkit' ); ?></p>
		<ul class="ul-disc">
			<li><?php _e( 'All WordPress tables prefixed by wpsc_*', 'wp-e-commerce-store-toolkit' ); ?></li>
			<li><?php _e( 'All Terms and Custom Post Types associated with WP e-Commerce', 'wp-e-commerce-store-toolkit' ); ?></li>
			<li><?php _e( 'All directories within Uploads (/wp-content/uploads/...) created by WP e-Commerce', 'wp-e-commerce-store-toolkit' ); ?></li>
			<li><?php _e( 'The \'wp-e-commerce\' directory within Plugins (/wp-content/plugins/...)', 'wp-e-commerce-store-toolkit' ); ?></li>
		</ul>
		<p><?php _e( 'Put bluntly, anything related to WP e-Commerce that existed before this point will not survive.', 'wp-e-commerce-store-toolkit' ); ?>
		<p class="submit">
<?php if( function_exists( 'wpsc_find_purchlog_status_name' ) && version_compare( wpsc_get_major_version(), '3.8', '>=' ) ) { ?> 
			<input type="button" class="button button-disabled" value="<?php _e( 'Uninstall WP e-Commerce', 'wp-e-commerce-store-toolkit' ); ?>" />
<?php } else { ?>
			<input type="submit" value="<?php _e( 'Uninstall WP e-Commerce', 'wp-e-commerce-store-toolkit' ); ?>" class="button-primary" />
			<input type="hidden" name="action" value="uninstall" />
			<?php wp_nonce_field( 'uninstall', 'wpsc_st_uninstall' ); ?>
<?php } ?>
		</p>
		<p class="description"><?php _e( '* WP e-Commerce must be de-activated to ensure there are no Plugin conflicts, only then will the Nuke button become available.', 'wp-e-commerce-store-toolkit' ); ?></p>
	</div>
</form>