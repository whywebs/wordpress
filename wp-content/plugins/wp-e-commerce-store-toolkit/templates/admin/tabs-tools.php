<h3><?php _e( 'WP e-Commerce Tools', 'wp-e-commerce-store-toolkit' ); ?></h3>
<p><?php _e( 'A growing set of commonly-used WP e-Commerce administration tools aimed at web developers and store maintainers.', 'wp-e-commerce-store-toolkit' ); ?></p>
<form method="post">

	<div id="poststuff">

		<div class="postbox">
			<h3 class="hndle"><?php _e( 'Tools', 'wp-e-commerce-store-toolkit' ); ?></h3>
			<div class="inside">
				<table class="form-table">

					<tr>
						<td>
							<a href="<?php echo add_query_arg( array( 'page' => 'wpsc_st-toolkit', 'action' => 'relink-pages', '_wpnonce' => wp_create_nonce( 'wpsc_st_relink_pages' ) ) ); ?>"><?php _e( 'Re-link WP e-Commerce Pages', 'wp-e-commerce-store-toolkit' ); ?></a>
							<p class="description"><?php _e( 'This tool will re-link the default WP e-Commerce Pages where customers are encountering dead shop links (e.g. Products Page, Checkout, Transaction Results, etc.)', 'wp-e-commerce-store-toolkit' ); ?></p>
						</td>
					</tr>

					<tr>
						<td>
							<a href="<?php echo add_query_arg( array( 'page' => 'wpsc_st-toolkit', 'action' => 'relink-existing-preregistered-sales', '_wpnonce' => wp_create_nonce( 'wpsc_st_relink_existing_preregistered_sales' ) ) ); ?>"><?php _e( 'Re-link existing Sales from pre-registered Users', 'wp-e-commerce-store-toolkit' ); ?></a>
							<p class="description"><?php _e( 'This tool will attempt to re-link Sales with no User linked to existing Users, this is common where a customer makes a purchase then later registers for the site. Using this tool their Sale will appear within My Account.', 'wp-e-commerce-store-toolkit' ); ?></p>
						</td>
					</tr>

					<tr>
						<td>
							<a href="<?php echo add_query_arg( array( 'page' => 'wpsc_st-toolkit', 'action' => 'fix-wpsc_version', '_wpnonce' => wp_create_nonce( 'wpsc_st_fix_wpsc_version' ) ) ); ?>"><?php _e( 'Repair WordPress option \'wpsc_version\'', 'wp-e-commerce-store-toolkit' ); ?></a>
							<p class="description"><?php _e( 'If you have upgraded to WP e-Commerce 3.8 then rolled back to 3.7 this will fix common store issues.', 'wp-e-commerce-store-toolkit' ); ?></p>
						</td>
					</tr>

					<tr>
						<td>
							<a href="<?php echo add_query_arg( array( 'page' => 'wpsc_st-toolkit', 'action' => 'clear-claimed_stock', '_wpnonce' => wp_create_nonce( 'wpsc_st_clear_claimed_stock' ) ) ); ?>"><?php _e( 'Empty the \'claimed_stock\' table', 'wp-e-commerce-store-toolkit' ); ?></a>
							<p class="description"><?php _e( 'This tool will dump all pending stock that has not been purchased for (e.g. abandoned carts).', 'wp-e-commerce-store-toolkit' ); ?></p>
						</td>
					</tr>

				</table>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->

		<div class="postbox">
			<h3 class="hndle"><?php _e( 'Tools', 'wp-e-commerce-store-toolkit' ); ?></h3>
			<div class="inside">
				<table class="form-table">

					<tr>
						<td>
							<label for="maximum_cart_quantity"><?php _e( 'Maximum cart quantity limit', 'wp-e-commerce-store-toolkit' ); ?></label><br />
							<input type="text" id="maximum_cart_quantity" name="maximum_cart_quantity" size="5" class="small-text" value="<?php echo $options['maximum_cart_quantity_limit']; ?>" />
							<p class="description"><?php _e( 'Override the default maximum cart quantity limit imposed by WP e-Commerce. Default is 10000.', 'wp-e-commerce-store-toolkit' ); ?></p>
						</td>
					</tr>

				</table>
			</div>
			<!-- .inside -->
		</div>
		<!-- .postbox -->

	</div>
	<!-- #poststuff -->
	<input type="submit" value="<?php _e( 'Save Changes', 'wp-e-commerce-store-toolkit' ); ?>" class="button-primary" />
	<input type="hidden" name="action" value="tools" />
	<?php wp_nonce_field( 'tools', 'wpsc_st_tools' ); ?>
</form>