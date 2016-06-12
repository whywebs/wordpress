<h3><?php _e( 'Manage Attributes', 'wpsc_cf' ); ?></h3>
<table class="widefat">
	<thead>

		<tr>
			<th class="manage-column column-name"><?php _e( 'Name', 'wpsc_cf' ); ?></th>
			<th class="manage-column column-name"><?php _e( 'Type', 'wpsc_cf' ); ?></th>
			<th class="manage-column column-name"><?php _e( 'Slug', 'wpsc_cf' ); ?></th>
			<th class="manage-column column-name"><?php _e( 'Description', 'wpsc_cf' ); ?></th>
			<th class="manage-column column-name"><?php _e( 'Example', 'wpsc_cf' ); ?></th>
		</tr>

	</thead>
	<tbody>

<?php
if( $data ) {
	$i = 0;
	foreach( $data as $id => $field ) { ?>
		<tr id="custom-field-<?php echo $id; ?>">
			<td class="name column-name">
				<a href="<?php echo add_query_arg( array( 'action' => 'edit', 'id' => $id ) ); ?>" class="row-title"><strong><?php echo $field['name']; ?></strong></a>
				<div class="row-actions">
					<span class="edit"><a href="<?php echo add_query_arg( array( 'action' => 'edit', 'id' => $id ) ); ?>"><?php _e( 'Edit', 'wpsc_cf' ); ?></a></span> | 
					<span class="submitdelete delete"><a href="<?php echo add_query_arg( array( 'action' => 'delete', 'id' => $id ) ); ?>"><?php _e( 'Delete', 'wpsc_cf' ); ?></a></span>
				</div>
			</td>
			<td class="name column-name"><?php echo wpsc_cf_return_type_label( $field['type'] ); ?></td>
			<td class="name column-name"><?php echo $field['slug']; ?></td>
			<td class="name column-name"><?php echo $field['description']; ?></td>
			<td class="name column-name"><?php echo stripslashes( $field['prefix'] ) . '[value]' . stripslashes( $field['suffix'] ); ?></td>
		</tr>

<?php
		$i++;
	}
}
if( $i == 0 ) { ?>
		<tr>
			<td colspan="5">
				<?php _e( 'No Attributes have been created.', 'wpsc_cf' ); ?>
			</td>
		</tr>

<?php
} ?>
	</tbody>
</table>