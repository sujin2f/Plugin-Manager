<?php
/**
 * Screen Option
 *
 * project	Plugin Manager
 * version: 5.0.0
 * Author: Sujin 수진 Choi
 * Author URI: http://www.sujinc.com/
 *
*/

namespace PIGPR;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class ScreenOption {
	public $option = [];
	public $hide_text, $show_hidden;

	function __construct() {
		$this->option = get_option( '_plugin-manager_', array() );

		add_filter( 'screen_settings', array( $this, 'AddOptionTextMode' ) );
		add_action( 'wp_ajax_PIGPR Setting Text', array( $this, 'ChangeSettingText' ) );
	}

	public function ChangeSettingText() {
		$this->option[ 'hide_text' ] = $_POST[ 'status' ];

		update_option( '_plugin-manager_', $this->option );

		echo $this->option[ 'hide_text' ];
		wp_die();
	}

	public function AddOptionTextMode( $screen_settings ) {
		$this->hide_text = ( is_array( $this->option ) && !empty( $this->option[ 'hide_text' ] ) ) ? true : false;

		$plugin_groups = get_option( 'plugin_groups' );

		ob_start();
		?>
		<fieldset class="screen-options">
			<legend><?php _e( 'Option', PIGPR_TEXTDOMAIN ) ?></legend>
			<label><input name="gm-text" type="checkbox" id="group-manager-setting-text" value="gm-text" <?php echo $this->hide_text ? 'checked="checked"' : '' ?>> Hide Group Manager Text</label>&nbsp;&nbsp;&nbsp;

			<legend><?php _e( 'Click to Delete Group', PIGPR_TEXTDOMAIN ) ?></legend>
			<?php foreach( $plugin_groups as $key => $value ) { ?>
			<a href="<?php echo add_query_arg( array( 'mode' => 'delete_group', 'group' => $key ) ) ?>" class="btn-delete_group button">Delete <?php echo $value[ 'name' ] ?></a>
			<?php } ?>
		</fieldset>
		<?php
		$screen_settings .= ob_get_clean();

		return $screen_settings;
	}
}
