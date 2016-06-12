<div id="content">

	<h2 class="nav-tab-wrapper">
		<a data-tab-id="overview" class="nav-tab<?php wpsc_ce_admin_active_tab( 'overview' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'wpsc_ce', 'tab' => 'overview' ), $wpsc_ce_url ); ?>"><?php _e( 'Overview', 'wp-e-commerce-exporter' ); ?></a>
		<a data-tab-id="export" class="nav-tab<?php wpsc_ce_admin_active_tab( 'export' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'wpsc_ce', 'tab' => 'export' ), $wpsc_ce_url ); ?>"><?php _e( 'Export', 'wp-e-commerce-exporter' ); ?></a>
		<a data-tab-id="archive" class="nav-tab<?php wpsc_ce_admin_active_tab( 'archive' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'wpsc_ce', 'tab' => 'archive' ), $wpsc_ce_url ); ?>"><?php _e( 'Archives', 'wp-e-commerce-exporter' ); ?></a>
		<a data-tab-id="settings" class="nav-tab<?php wpsc_ce_admin_active_tab( 'settings' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'wpsc_ce', 'tab' => 'settings' ), $wpsc_ce_url ); ?>"><?php _e( 'Settings', 'wp-e-commerce-exporter' ); ?></a>
		<a data-tab-id="tools" class="nav-tab<?php wpsc_ce_admin_active_tab( 'tools' ); ?>" href="<?php echo add_query_arg( array( 'page' => 'wpsc_ce', 'tab' => 'tools' ), $wpsc_ce_url ); ?>"><?php _e( 'Tools', 'wp-e-commerce-exporter' ); ?></a>
	</h2>
	<?php wpsc_ce_tab_template( $tab ); ?>

</div>
<!-- #content -->
