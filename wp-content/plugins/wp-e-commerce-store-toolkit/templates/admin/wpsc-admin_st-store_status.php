<h3><?php _e( 'Versions', 'wp-e-commerce-store-toolkit' ); ?></h3>
<table class="widefat fixed">

	<tr>
		<th><?php _e( 'WordPress', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td><?php echo get_bloginfo( 'version' ); ?></td>
	</tr>

	<tr>
		<th><?php _e( 'WP e-Commerce', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td><?php echo WPSC_VERSION; ?></td>
	</tr>

	<tr>
		<th><?php _e( 'Gold Cart', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td>
			<?php echo $gold_cart; ?>
		</td>
	</tr>

	<tr>
		<th><?php _e( 'Theme', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td>
			<?php echo $theme['name']; ?> (by <a href="<?php echo $theme['author_url']; ?>" target="_blank"><?php echo $theme['author']; ?></a>) Version: <?php echo $theme['version']; ?>
		</td>
	</tr>

	<tr>
		<th><?php _e( 'Active Plugins', 'wp-e-commerce-store-toolkit' ); ?>:</th>
		<td>
<?php if( $active_plugins ) { ?>
			<ul>
	<?php foreach( $active_plugins as $active_plugin ) { ?>
				<li><a href="<?php echo $active_plugin['url']; ?>"><?php echo $active_plugin['name']; ?></a> (by <a href="<?php echo $active_plugin['author_url']; ?>" target="_blank"><?php echo $active_plugin['author']; ?></a>): Version <?php echo $active_plugin['version']; ?></li>
	<?php } ?>
			</ul>
<?php } ?>
		</td>
	</tr>

	<tr>
		<th><?php _e( 'Visser Labs', 'wp-e-commerce-store-toolkit' ); ?>:</th>
		<td>
			<ul>
<?php if( function_exists( 'wpsc_get_major_version' ) ) { ?>
				<li><code>wpsc_get_major_version(): <?php echo wpsc_get_major_version(); ?></code></li>
<?php } ?>
<?php if( function_exists( 'wpsc_get_minor_version' ) ) { ?>
				<li><code>wpsc_get_minor_version(): <?php echo wpsc_get_minor_version(); ?></code></li>
<?php } ?>
			</ul>
		</td>
	</tr>

</table>

<h3><?php _e( 'Server Environment', 'wp-e-commerce-store-toolkit' ); ?></h3>
<table class="widefat fixed">

	<tr>
		<th><?php _e( 'PHP Version', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td><?php echo PHP_VERSION; ?></td>
	</tr>

	<tr>
		<th><?php _e( 'MySQL Version', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td><?php echo mysql_get_server_info(); ?></td>
	</tr>
	

	<tr>
		<th><?php _e( 'Max. Upload Filesize', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td><?php echo $max_upload; ?></td>
	</tr>

	<tr>
		<th><?php _e( 'Max. POST Size', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td><?php echo $max_post; ?></td>
	</tr>

	<tr>
		<th><?php _e( 'Maximum Execution Time', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td><?php echo $max_execution_time; ?></td>
	</tr>

	<tr>
		<th><?php _e( 'Memory Allocation', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td><?php echo $memory_limit; ?></td>
	</tr>

</table>

<h3><?php _e( 'Pages', 'wp-e-commerce-store-toolkit' ); ?></h3>
<?php if( $wpsc_pages ) { ?>
<table class="widefat fixed">

	<?php foreach( $wpsc_pages as $wpsc_page ) { ?>
	<tr id="<?php echo $wpsc_page['post_ID']; ?>">
		<th><?php echo $wpsc_page['title']; ?></th>
		<td>
			<code><?php echo $wpsc_page['url']; ?></code>
	<?php if( $wpsc_page['post_ID'] ) { ?>
	<a href="<?php echo add_query_arg( array( 'post' => $wpsc_page['post_ID'], 'action' => 'edit' ), 'post.php' ); ?>"><attr title="<?php _e( 'Edit Page', 'wp-e-commerce-store-toolkit' ); ?>"><?php _e( 'Edit', 'wp-e-commerce-store-toolkit' ); ?></attr></a>
	<?php } ?>
	<?php if( isset( $wpsc_page['shortcode'] ) ) { ?>
			<p class="description"><?php _e( 'This Page contains the correct ' . $wpsc_page['title'] . ' shortcode.', 'wp-e-commerce-store-toolkit' ); ?></p>
	<?php } else { ?>
			<p class="description"><?php _e( 'This Page does not contain the expected shortcode for ' . $wpsc_page['title'] . ' and may not work.', 'wp-e-commerce-store-toolkit' ); ?></p>
	<?php } ?>
			<!-- (#<?php echo $wpsc_page['post_ID']; ?>) -->
		</td>
	</tr>
	<?php } ?>

</table>
<?php } ?>

<h3><?php _e( 'Store Management', 'wp-e-commerce-store-toolkit' ); ?></h3>
<table class="widefat fixed">

	<tr>
		<th><?php _e( 'Store E-mail', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td>
			<?php echo $store_email; ?>
		</td>
	</tr>

	<tr>
		<th><?php _e( 'Base Country', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td>
			<?php echo $base_country; ?>
		</td>
	</tr>

</table>

<h3><?php _e( 'Storefront', 'wp-e-commerce-store-toolkit' ); ?></h3>
<table class="widefat fixed">

	<tr>
		<th><?php _e( 'Products', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td>
			<?php echo wpsc_st_return_count( 'products' ); ?>
		</td>
	</tr>

	<tr>
		<th><?php _e( 'Product Variations', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td>
			<?php echo wpsc_st_return_count( 'variations' ); ?>
		</td>
	</tr>

	<tr>
		<th><?php _e( 'Product Images', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td>
			<?php echo wpsc_st_return_count( 'images' ); ?>
		</td>
	</tr>

	<tr>
		<th><?php _e( 'Download Files', 'wp-e-commerce-store-toolkit' ); ?></th>
		<td>
			<?php echo wpsc_st_return_count( 'files' ); ?>
		</td>
	</tr>

</table>