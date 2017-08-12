<?php
/**
 * Bootstrap
 *
 * @package Plugin Manager
 * @since   6.0.0
 * @author  Sujin 수진 Choi http://www.sujinc.com/donation
 *
 * @todo    그룹 보기 모드일 때 한글 깨짐.
 * @todo    번역
*/

namespace Sujin\Plugin\PluginMgr;

use Sujin\Plugin\PluginMgr\Traits\Config;
use Sujin\Plugin\PluginMgr\Constants\Colour;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Bootstrap extends Plugin_Base {
	use Config;

	/**
	 * Constructor.
	 *
	 * Initialization and Setting the first hooking points.
	 *
	 * @since  0.0.1
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		if ( ! is_admin() )
			return;

		add_action( 'admin_init',         array( $this, 'activate_plugin' ) );
		add_filter( 'wp_get_update_data', array( $this, 'set_update_data' ) );
	}

	public function set_update_data( $update_data )  {
		All_Plugins::block_upgrade();
		return $update_data;
	}

	/**
	 * Plugin Activation.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @global string $pagenow
	 */
	public function activate_plugin() {
		if ( $this->maybe_ajax() )
			return;

		// If it's not on a plugins pages, terminate Plugin Manager
		global $pagenow;
		if ( $pagenow !== "plugins.php" )
			return;

		// Create DB
		if ( ! Database::is_tables_exist() )
			Database::create_tables();

		// Arrange DB
		Database::remove_duplicate_plugins();
		Database::update_plugins();

		if ( ! Database::is_updated() )
			Database::upgrade_from_normal_version();

		Modal::get_instance();
		Table::get_instance();
		All_Plugins::get_instance();

		// Text Domain, Script, Style, and Redirection
		add_action( 'plugins_loaded',        array( $this, 'load_text_domain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'wp_redirect',           array( $this, 'wp_redirect' ) );

		// Modifying Admin HTML for Angular
		add_action( 'admin_xml_ns',          array( $this, 'add_angular_app' ) );
		add_action( 'admin_head',            array( $this, 'add_angular_controller' ) );
		add_action( 'admin_footer',          array( $this, 'close_angular_controller' ) );
		add_action( 'admin_footer',          array( $this, 'print_colour_style' ) );
	}

	/**
	 * Ajax.
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @return bool
	 *
	 * @global bool   DOING_AJAX
	 * @global string $_REQUEST['action'] Ajax Mode.
	 */
	private function maybe_ajax() {
		// AJAX
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) && strstr( $_REQUEST['action'], 'Plugin Manager Pro' ) !== false ) {
			check_ajax_referer( SUJIN_PLUGIN_MGR_SLUG, 'security' );
			Ajax::get_instance();

			return true;
		}

		return false;
	}

	/**
	 * Text Domain.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @global string SUJIN_PLUGIN_MGR_BASE_NAME
	 *
	 * @return void
	 */
	public function load_text_domain() {
		$lang_dir = SUJIN_PLUGIN_MGR_BASE_NAME . '/languages';
		load_plugin_textdomain( SUJIN_PLUGIN_MGR_SLUG, 'wp-content/plugins/' . $lang_dir, $lang_dir );
	}

	/**
	 * Load Scripts and Styles.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @global string SUJIN_PLUGIN_MGR_URL
	 * @global string SUJIN_PLUGIN_MGR_SLUG
	 * @global string SUJIN_PLUGIN_MGR_VERSION
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'angular',                     SUJIN_PLUGIN_MGR_URL . 'assets/angular/angular.min.js' );
		wp_enqueue_script( 'angular-drag-and-drop-lists', SUJIN_PLUGIN_MGR_URL . 'assets/angular/angular-drag-and-drop-lists.js', array( 'angular' ) );
		wp_enqueue_script( 'angular-indeterminate',       SUJIN_PLUGIN_MGR_URL . 'assets/angular/angular-indeterminate.min.js'  , array( 'angular' ) );

		# Adding Grouping Actions on Dropdown Menu
		$script_url = SUJIN_PLUGIN_MGR_URL . 'assets/dist/scripts/app.js';
		$style_url  = SUJIN_PLUGIN_MGR_URL . 'assets/dist/css/style.css';

		wp_enqueue_script( SUJIN_PLUGIN_MGR_SLUG, $script_url, array(  ), SUJIN_PLUGIN_MGR_VERSION );
		wp_enqueue_style(  SUJIN_PLUGIN_MGR_SLUG, $style_url,  array(), SUJIN_PLUGIN_MGR_VERSION );

		/**
		 * Localization.
		 *
		 * Use objectL10n.{key} in your javascript file.
		 */
		// Localization // objectL10n.delete_group

		wp_localize_script( SUJIN_PLUGIN_MGR_SLUG, 'objectL10n', array(
			'message'  => array(
				'text_length' => __( 'Group name is empty.',  SUJIN_PLUGIN_MGR_SLUG ),
				'something'   => __( 'Something went wrong.', SUJIN_PLUGIN_MGR_SLUG ),
			),

			'terms'    => array(
				'group'  => __( 'Group',  SUJIN_PLUGIN_MGR_SLUG ),
				'lock'   => __( 'Lock',   SUJIN_PLUGIN_MGR_SLUG ),
				'unlock' => __( 'Unlock', SUJIN_PLUGIN_MGR_SLUG ),
				'hide'   => __( 'Hide',   SUJIN_PLUGIN_MGR_SLUG ),
				'unhide' => __( 'Unhide', SUJIN_PLUGIN_MGR_SLUG ),
			),

			'colours'      => Colour::$COLOURS,
			'settings'     => Option::get(),
			'data'         => Database::get_json_array( $this->group ),
			'plugin_group' => $this->group,

			'nonce'        => wp_create_nonce( SUJIN_PLUGIN_MGR_SLUG ),
		));
	}

	public function add_angular_app() {
		echo ' ng-app="PluginManager" ';
	}

	public function add_angular_controller() {
		echo '<div ng-controller="PluginManagerController" ng-show="ng_loaded" class="ng-hide">';
	}

	public function close_angular_controller() {
		echo '</div>';
	}

	public function print_colour_style() {
		?>
		<style>
			<?php foreach( Colour::$COLOURS as $key => $colour ) : ?>
			.<?php echo $key ?> {
				background-color: <?php echo $colour ?> !important;
				border-color    : <?php echo $colour ?> !important;
				color           : <?php echo $this->get_contrast_colour( $colour ); ?> !important;
			}

			.<?php echo $key ?> .count {
				color           : <?php echo $colour ?> !important;
				background-color: <?php echo $this->get_contrast_colour( $colour ); ?> !important;
			}

			.<?php echo $key ?> .button {
				color           : <?php echo $colour ?> !important;
				background-color: <?php echo $this->get_contrast_colour( $colour ); ?> !important;
			}
			<?php endforeach; ?>
		</style>
		<?php
	}

	/**
	 * Get black or white colour along with the input colour.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @param  string $hex
	 *
	 * @return string Black or white colour.
	 */
	private function get_contrast_colour( $hex ) {
		$hex = str_replace( '#', '', $hex );

		if( strlen( $hex ) == 3 ) {
			$r = hexdec( substr( $hex, 0, 1 ).substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ).substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ).substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}

		$contrast = ( $r + $g + $b ) / 3;

		return ( $contrast < 128 ) ? "#FFFFFF" : "#000000";
	}

	/**
	 * Change Redirection Method.
	 *
	 * If HTTP header was already sent, print meta HTML tag.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @param  string $location
	 *
	 * @return string
	 */
	public function wp_redirect( $location ) {
		if ( headers_sent() ) {
			$this->redirect_html_meta( $location );
		}

		return $location;
	}

	/**
	 * Print meta HTML tag for redirection.
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @param  string $location
	 *
	 * @return void
	 */
	private function redirect_html_meta( $location ) {
		printf( '<meta http-equiv="refresh" content="0; url=%s">', $location );
		wp_die();
	}
}
