<div id="Grouping-Row" style="display:none;">
	<h4><?php _e( 'Grouping', PIGPR_TEXTDOMAIN ) ?></h4>

	<?php do_action( 'before_plugin_group' ) ?>

	<ul>
		<?php if ( $this->plugin_groups ) : ?>
			<?php
			foreach( $this->plugin_groups as $key => $value ) :
				if ( !is_array( $value ) ) {
					$value = $this->upgrade( $key );
				}
			?>
				<li>
					<label  for="#">
						<input id="#" type="checkbox" data-id="<?php echo $key ?>" data-name="<?php echo $value['name'] ?>" data-plugin-id="" />
						<?php echo $value['name'] ?>
					</label>
					<input type="text" value="<?php echo $value['color'] ?>" class="group_colour_picker" data-id="<?php echo $key ?>" />
				</li>
			<?php endforeach; ?>
		<?php endif; ?>
	</ul>

	<?php do_action( 'after_plugin_group' ) ?>

	<img class="loading_spinner" src="<?php bloginfo('url') ?>/wp-admin/images/loading.gif" style="display:none;" />

	<div class="clear"></div>

	<input type="text" class="inp-create_group" />
	<a href="#" class="button button-primary btn-create_group"><?php _e( 'Create Group', PIGPR_TEXTDOMAIN ) ?></a>
	<a href="#" class="button btn-close_group"><?php _e( 'Close', PIGPR_TEXTDOMAIN ) ?></a>
</div>
