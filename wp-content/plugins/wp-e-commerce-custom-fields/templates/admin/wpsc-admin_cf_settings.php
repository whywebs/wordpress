<form method="post" action="<?php the_permalink(); ?>" id="your-profile">

	<h3><?php _e( 'General', 'wpsc_cf' ); ?></h3>
	<table class="form-table">

		<tr>
			<th scope="row"><label for="position"><?php _e( 'Position', 'wpsc_cf' ); ?>:</label></th>
			<td>
				<select name="position" class="chzn-select">
<?php
$output = '';
for( $i = 0; $i < count( $positions ); $i++ )
	$output .= '<option value="' . $positions[$i][0] . '"' . selected( $positions[$i][0], $position, false ) . '>' . $positions[$i][1] . '</option>' . "\n";
echo $output; ?>
				</select> <span class="description"><?php _e( 'The placement of Attributes within the Product details template.', 'wpsc_cf' ); ?></span>
			</td>
		</tr>

	</table>
	<p><?php _e( 'For manual positioning use the following PHP template tag within the Single Product template', 'wpsc_cf' ); ?>.</p>
	<p><code><?php echo htmlentities2( '<?php if( function_exists( \'wpsc_the_custom_fields\' ) ) wpsc_the_custom_fields(); ?>' ); ?></code></p>
	<p><?php _e( 'To display individual custom fields use the following PHP template tag with the \'slug\' property', 'wpsc_cf' ); ?>.</p>
	<p><code><?php echo htmlentities2( '<?php if( function_exists( \'wpsc_the_custom_fields\' ) ) wpsc_the_custom_fields( \'slug=' . $field['slug'] . '\' ); ?>' ); ?></code></p>

	<h3><?php _e( 'Presentation', 'wpsc_cf' ); ?></h3>
	<table class="form-table">

		<tr>
			<th scope="row"><label for="layout"><?php _e( 'Layout', 'wpsc_cf' ); ?>:</label></th>
			<td>
				<select name="layout" class="chzn-select">
<?php
$output = '';
foreach( $layouts as $layout )
	$output .= '<option value="' . $layout['filename'] . '"' . selected( $layout['filename'], $selected_layout, false ) . '>' . $layout['label'] . '</option>' . "\n";
echo $output; ?>
				</select>
				<span class="description"><?php _e( 'The layout of Attributes within the Product details template.', 'wpsc_cf' ); ?></span>
			</td>
		</tr>

		<tr>
			<th scope="row"><label><?php _e( 'Header Visibility', 'wpsc_cf' ); ?>:</label></th>
			<td>
				<fieldset>
					<label><input type="radio" name="display_title" value="1"<?php checked( $display_title, 1 ); ?> /> <?php _e( 'I would like to display the Attributes header', 'wpsc_cf' ); ?></label><br />
					<label><input type="radio" name="display_title" value="0"<?php checked( $display_title, 0 ); ?> /> <?php _e( 'I would like to hide the Attributes header', 'wpsc_cf' ); ?></label>
				</fieldset>
				<p class="description"><?php _e( 'Show or hide the Related Products header.', 'wpsc_cf' ); ?></p>
			</td>
		</tr>

		<tr>
			<th scope="row"><label><?php _e( 'Header Title', 'wpsc_cf' ); ?>:</label></th>
			<td>
				<input type="text" name="title_text" value="<?php echo $title_text; ?>" size="10" class="regular-text" />
				<span class="description"><?php _e( 'The header text for Attributes.', 'wpsc_cf' ); ?></span>
			</td>
		</tr>

	</table>

	<p class="submit">
		<input type="submit" value="<?php _e( 'Save Changes', 'wpsc_cf' ); ?>" class="button-primary" />
	</p>
	<input type="hidden" name="action" value="update" />

</form>