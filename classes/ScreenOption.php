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
		$this->option = get_option( 'plugin_manager' );

		add_filter( 'screen_settings', array( $this, 'AddOptionTextMode' ) );
		add_action( 'wp_ajax_PIGPR Setting Text', array( $this, 'ChangeSettingText' ) );
		add_action( 'wp_ajax_PIGPR Setting Hidden', array( $this, 'ChangeSettingHidden' ) );
	}

	public function ChangeSettingText() {
		$this->option = get_option( 'plugin_manager' );
		$this->option[ 'hide_text' ] = $_POST[ 'status' ];

		update_option( 'plugin_manager', $this->option );

		echo $this->option[ 'hide_text' ];
		wp_die();
	}

	public function ChangeSettingHidden() {
		$this->option = get_option( 'plugin_manager' );
		$this->option[ 'show_hidden' ] = $_POST[ 'status' ];

		update_option( 'plugin_manager', $this->option );

		echo $this->option[ 'show_hidden' ];
		wp_die();
	}

	public function AddOptionTextMode( $screen_settings ) {
		$this->hide_text = ( is_array( $this->option ) && !empty( $this->option[ 'hide_text' ] ) ) ? true : false;
		$this->show_hidden = ( is_array( $this->option ) && !empty( $this->option[ 'show_hidden' ] ) ) ? true : false;

		ob_start();
		?>
		<fieldset class="screen-options">
			<legend><?php _e( 'Option', PIGPR_TEXTDOMAIN ) ?></legend>
			<label><input name="gm-text" type="checkbox" id="group-manager-setting-text" value="gm-text" <?php echo $this->hide_text ? 'checked="checked"' : '' ?>> Hide Group Manager Text</label>&nbsp;&nbsp;&nbsp;
			<label><input name="gm-hidden" type="checkbox" id="group-manager-setting-hidden" value="gm-hidden" <?php echo $this->show_hidden ? 'checked="checked"' : '' ?>> Show Hidden Plugins</label>
		</fieldset>
		<?php
		$screen_settings .= ob_get_clean();

		return $screen_settings;
	}
}
