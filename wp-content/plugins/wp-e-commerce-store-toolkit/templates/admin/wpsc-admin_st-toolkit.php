<div id="content">

	<h2 class="nav-tab-wrapper">
		<a data-tab-id="overview" class="nav-tab<?php wpsc_st_admin_active_tab( 'overview' ); ?>" href="<?php echo add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_st-toolkit' ), 'edit.php' ); ?>"><?php _e( 'Overview', 'wp-e-commerce-store-toolkit' ); ?></a>
		<a data-tab-id="nuke" class="nav-tab<?php wpsc_st_admin_active_tab( 'nuke' ); ?>" href="<?php echo add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_st-toolkit', 'tab' => 'nuke' ), 'edit.php' ); ?>"><?php _e( 'Nuke', 'wp-e-commerce-store-toolkit' ); ?></a>
		<a data-tab-id="tools" class="nav-tab<?php wpsc_st_admin_active_tab( 'tools' ); ?>" href="<?php echo add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_st-toolkit', 'tab' => 'tools' ), 'edit.php' ); ?>"><?php _e( 'Tools', 'wp-e-commerce-store-toolkit' ); ?></a>
		<a data-tab-id="demo" class="nav-tab<?php wpsc_st_admin_active_tab( 'demo' ); ?>" href="<?php echo add_query_arg( array( 'post_type' => 'wpsc-product', 'page' => 'wpsc_st-toolkit', 'tab' => 'demo' ), 'edit.php' ); ?>"><?php _e( 'Demo Mode', 'wp-e-commerce-store-toolkit' ); ?></a>
	</h2>
	<?php wpsc_st_tab_template( $tab ); ?>

</div>
<!-- #content -->

<div id="progress" style="display:none;">
	<p><?php _e( 'Chosen WP e-Commerce details are being nuked, this process can take awhile. Time for a beer?', 'wp-e-commerce-store-toolkit' ); ?></p>
	<img src="<?php echo plugins_url( '/templates/admin/images/progress.gif', $wpsc_st['relpath'] ); ?>" alt="" />
</div>
<!-- #progress -->