<h3><?php _e( 'Demo Mode', 'wp-e-commerce-store-toolkit' ); ?></h3>
<p><?php _e( 'Show a banner at the top of every page stating this shop is currently in testing mode.', 'wp-e-commerce-store-toolkit' ); ?></p>
<form method="post">

	<div id="poststuff">

		<div class="postbox">
			<h3 class="hndle"><?php _e( 'Demo Store', 'wp-e-commerce-store-toolkit' ); ?></h3>
			<div class="inside">
				<table class="form-table">

					<tr>
						<td>
							<label><input type="checkbox" id="demo_store" name="options[demo_store]"<?php checked( $options['demo_store'], 1 ); ?>/> <?php _e( 'Enable Demo Store', 'wp-e-commerce-store-toolkit' ); ?></label>
						</td>
					</tr>

					<tr>
						<td>
							<label for="demo_store_text"><?php _e( 'Message', 'wp-e-commerce-store-toolkit' ); ?></label><br />
							<input type="text" id="demo_store_text" name="options[demo_store_text]" value="<?php echo $options['demo_store_text']; ?>" size="100" />
							<p class="description"><?php _e( 'Customise the message that appears at the top of every page.', 'wp-e-commerce-store-toolkit' ); ?></p>
						</td>
					</tr>

					<tr>
						<td>
							<label for="demo_store_text_color"><?php _e( 'Text Colour', 'wp-e-commerce-store-toolkit' ); ?></label><br />
							# <input type="text" id="demo_store_text_color" name="options[demo_store_text_color]" value="<?php echo $options['demo_store_text_color']; ?>" size="6" maxlength="6" />
							<p class="description"><?php _e( 'Personalise the banner text colour.', 'wp-e-commerce-store-toolkit' ); ?></p>
						</td>
					</tr>

					<tr>
						<td>
							<label><?php _e( 'Background Gradient Colours', 'wp-e-commerce-store-toolkit' ); ?></label><br />
							# <input type="text" id="demo_store_bg_color_top" name="options[demo_store_bg_color_top]" value="<?php echo $options['demo_store_bg_color_top']; ?>" size="6" maxlength="6" /> <span class="description">Top</span><br />
							# <input type="text" id="demo_store_bg_color_bottom" name="options[demo_store_bg_color_bottom]" value="<?php echo $options['demo_store_bg_color_bottom']; ?>" size="6" maxlength="6" /> <span class="description">Bottom</span>
							<p class="description"><?php _e( 'Personalise the background gradient colours.', 'wp-e-commerce-store-toolkit' ); ?></p>
						</td>
					</tr>

					<tr>
						<td>
							<label for="demo_store_border_color"><?php _e( 'Border Colour', 'wp-e-commerce-store-toolkit' ); ?></label><br />
							# <input type="text" id="demo_store_border_color" name="options[demo_store_border_color]" value="<?php echo $options['demo_store_border_color']; ?>" size="6" maxlength="6" />
							<p class="description"><?php _e( 'Personalise the banner border colour.', 'wp-e-commerce-store-toolkit' ); ?></p>
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
	<input type="hidden" name="action" value="demo" />
	<?php wp_nonce_field( 'demo', 'wpsc_st_demo' ); ?>

</form>