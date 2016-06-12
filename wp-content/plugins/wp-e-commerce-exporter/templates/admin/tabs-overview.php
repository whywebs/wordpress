<div class="overview-left">

	<h3><a href="<?php echo add_query_arg( 'tab', 'export' ); ?>"><?php _e( 'Export', 'wp-e-commerce-exporter' ); ?></a></h3>
	<p><?php _e( 'Export store details out of WP e-Commerce into a CSV-formatted file.', 'wp-e-commerce-exporter' ); ?></p>
	<ul class="ul-disc">
		<li>
			<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>#export-products"><?php _e( 'Export Products', 'wp-e-commerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>#export-categories"><?php _e( 'Export Categories', 'wp-e-commerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>#export-tags"><?php _e( 'Export Tags', 'wp-e-commerce-exporter' ); ?></a>
		</li>
		<li>
			<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>#export-orders"><?php _e( 'Export Orders', 'wp-e-commerce-exporter' ); ?></a>
<?php if( !function_exists( 'wpsc_cd_admin_init' ) ) { ?>
			<span class="description">(<?php printf( __( 'available in %s', 'wp-e-commerce-exporter' ), $wpsc_cd_link ); ?>)</span>
<?php } ?>
		</li>
		<li>
			<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>#export-customers"><?php _e( 'Export Customers', 'wp-e-commerce-exporter' ); ?></a>
<?php if( !function_exists( 'wpsc_cd_admin_init' ) ) { ?>
			<span class="description">(<?php printf( __( 'available in %s', 'wp-e-commerce-exporter' ), $wpsc_cd_link ); ?>)</span>
<?php } ?>
		</li>
		<li>
			<a href="<?php echo add_query_arg( 'tab', 'export' ); ?>#export-coupons"><?php _e( 'Export Coupons', 'wp-e-commerce-exporter' ); ?></a>
<?php if( !function_exists( 'wpsc_cd_admin_init' ) ) { ?>
			<span class="description">(<?php printf( __( 'available in %s', 'wp-e-commerce-exporter' ), $wpsc_cd_link ); ?>)</span>
<?php } ?>
		</li>
	</ul>

	<h3><a href="<?php echo add_query_arg( 'tab', 'archive' ); ?>"><?php _e( 'Archives', 'wp-e-commerce-exporter' ); ?></a></h3>
	<p><?php _e( 'Download copies of prior store exports.', 'wp-e-commerce-exporter' ); ?></p>

	<h3><a href="<?php echo add_query_arg( 'tab', 'settings' ); ?>"><?php _e( 'Settings', 'wp-e-commerce-exporter' ); ?></a></h3>
	<p><?php _e( 'Manage CSV export options from a single detailed screen.', 'wp-e-commerce-exporter' ); ?></p>

	<h3><a href="<?php echo add_query_arg( 'tab', 'tools' ); ?>"><?php _e( 'Tools', 'wp-e-commerce-exporter' ); ?></a></h3>
	<p><?php _e( 'Export tools for WP e-Commerce.', 'wp-e-commerce-exporter' ); ?></p>

	<hr />
<?php if( !function_exists( 'wpsc_cd_admin_init' ) ) { ?>
	<label class="description">
		<input type="checkbox" disabled="disabled" /> <?php _e( 'Jump to Export screen in the future', 'wp-e-commerce-exporter' ); ?>
		<span class="description"> - <?php printf( __( 'available in %s', 'wp-e-commerce-exporter' ), $wpsc_cd_link ); ?></span>
	</label>
<?php } else { ?>
	<form id="skip_overview_form" method="post">
		<label><input type="checkbox" id="skip_overview" name="skip_overview"<?php checked( $skip_overview ); ?> /> <?php _e( 'Jump to Export screen in the future', 'wp-e-commerce-exporter' ); ?></label>
		<input type="hidden" name="action" value="skip_overview" />
		<?php wp_nonce_field( 'skip_overview', 'wpsc_ce_skip_overview' ); ?>
	</form>
<?php } ?>

</div>
<!-- .overview-left -->
<?php if( !function_exists( 'wpsc_cd_admin_init' ) ) { ?>
<div class="welcome-panel overview-right">
	<h3>
		<!-- <span><a href="#"><attr title="<?php _e( 'Dismiss this message', 'wp-e-commerce-exporter' ); ?>"><?php _e( 'Dismiss', 'wp-e-commerce-exporter' ); ?></attr></a></span> -->
		<?php _e( 'Upgrade to Pro', 'wp-e-commerce-exporter' ); ?>
	</h3>
	<p class="clear"><?php _e( 'Upgrade to Store Exporter Deluxe to unlock business focused e-commerce features within Store Exporter, including:', 'wp-e-commerce-exporter' ); ?></p>
	<ul class="ul-disc">
		<li><?php _e( 'Select export date ranges', 'wp-e-commerce-exporter' ); ?></li>
		<li><?php _e( 'Export Orders', 'wp-e-commerce-exporter' ); ?></li>
		<li><?php _e( 'Select Order fields to export', 'wp-e-commerce-exporter' ); ?></li>
		<li><?php _e( 'Export Customers', 'wp-e-commerce-exporter' ); ?></li>
		<li><?php _e( 'Select Customer fields to export', 'wp-e-commerce-exporter' ); ?></li>
		<li><?php _e( 'Export Coupons', 'wp-e-commerce-exporter' ); ?></li>
		<li><?php _e( 'Select Coupon fields to export', 'wp-e-commerce-exporter' ); ?></li>
		<li><?php _e( 'CRON / Scheduled Exports', 'wp-e-commerce-exporter' ); ?></li>
		<li><?php _e( 'Export to XML', 'wp-e-commerce-exporter' ); ?></li>
		<li><?php _e( 'Premium Support', 'wp-e-commerce-exporter' ); ?></li>
	</ul>
	<p>
		<a href="<?php echo $wpsc_cd_url; ?>" target="_blank" class="button"><?php _e( 'More Features', 'wp-e-commerce-exporter' ); ?></a>&nbsp;
		<a href="<?php echo $wpsc_cd_url; ?>" target="_blank" class="button button-primary"><?php _e( 'Buy Now', 'wp-e-commerce-exporter' ); ?></a>
	</p>
</div>
<!-- .overview-right -->
<?php } ?>
