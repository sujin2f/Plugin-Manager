<?php
/**
 * Init
 *
 * Bootstrap Class.
 *
 * @package     WordPress
 * @subpackage  Plugin Manager PRO
 * @since       0.0.1
 * @author      Sujin 수진 Choi http://www.sujinc.com/
*/

// TODO : 그룹 보기 모드일 때 한글 깨짐.
// TODO : 번역

namespace PLGINMNGRPRO;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Init extends Base {
	/**
	 * Colours
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @var bool $test_mode
	 */
	private $colours = array(
		'Red'         => '#A60000',
		'Orange'      => '#FF7000',
		'Apricot'     => '#FFD060',

		'Blue'        => '#0020A0',
		'LightBlue'   => '#3777CD',
		'SkyBlue'     => '#B0C0EA',

		'Brown'       => '#464000',
		'LightBrown'  => '#948700',
		'Yellow'      => '#FFE900',

		'Green'       => '#007039',
		'LightGreen'  => '#67C700',
		'PaleGreen'   => '#B5F167',

		'DeepPurple'  => '#620056',
		'Purple'      => '#A1008D',
		'LightPurple' => '#EDA4D6',

		'Black'       => '#000000',
		'Grey'        => '#737373',
		'White'       => '#FFFFFF',
	);

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

		add_action( 'admin_init', array( $this, 'activate_plugin' ) );
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
		// Ajax
		if ( $this->activate_ajax() )
			return;

		// Plugin Pages
		global $pagenow;
		if ( $pagenow !== "plugins.php" )
			return;

		if ( !Database::is_tables_exist() )
			Database::create_tables();

		Database::remove_duplicate_plugins();
		Database::update_plugins();

		if ( ! Database::is_updated() )
			Database::upgrade_from_normal_version();

		// Deactivate Older Version
		if ( defined( 'PIGPR_PLUGIN_NAME' ) ) {
			deactivate_plugins( PIGPR_PLUGIN_DIR . PIGPR_PLUGIN_FILE_NAME );
		}

		// Set Vars
		new Modal();
		new Table();
		new All_Plugins();

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
	private function activate_ajax() {
		// AJAX
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) && strstr( $_REQUEST['action'], 'Plugin Manager Pro' ) !== false ) {
			check_ajax_referer( PLGINMNGRPRO_TEXTDOMAIN, 'security' );
			new Ajax();

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
	 * @global string PLGINMNGRPRO_PLUGIN_NAME
	 *
	 * @return void
	 */
	public function load_text_domain() {
		$lang_dir = PLGINMNGRPRO_PLUGIN_NAME . '/languages';
		load_plugin_textdomain( PLGINMNGRPRO_TEXTDOMAIN, 'wp-content/plugins/' . $lang_dir, $lang_dir );
	}

	/**
	 * Load Scripts and Styles.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @global string PLGINMNGRPRO_ASSETS_URL
	 * @global string PLGINMNGRPRO_TEXTDOMAIN
	 * @global string PLGINMNGRPRO_VERSION_NUM
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'angular',               PLGINMNGRPRO_ASSETS_URL . 'script/angular.min.js' );
// 		wp_enqueue_script( 'angular-animate',       PLGINMNGRPRO_ASSETS_URL . 'script/angular-animate.min.js' );
		wp_enqueue_script( 'angular-indeterminate', PLGINMNGRPRO_ASSETS_URL . 'script/angular-indeterminate.min.js' );

		# Adding Grouping Actions on Dropdown Menu
		$script_url = PLGINMNGRPRO_ASSETS_URL . 'script-min/plugin-manager-min.js';
		$style_url  = PLGINMNGRPRO_ASSETS_URL . 'css/plugin-manager.css';

		wp_enqueue_script( PLGINMNGRPRO_TEXTDOMAIN, $script_url, array( 'angular' ), PLGINMNGRPRO_VERSION_NUM );
		wp_enqueue_style(  PLGINMNGRPRO_TEXTDOMAIN, $style_url,  array(), PLGINMNGRPRO_VERSION_NUM );

		/**
		 * Localization.
		 *
		 * Use objectL10n.{key} in your javascript file.
		 */
		// Localization // objectL10n.delete_group

		wp_localize_script( PLGINMNGRPRO_TEXTDOMAIN, 'objectL10n', array(
			'message'  => array(
				'text_length' => __( 'Group name is empty.',  PLGINMNGRPRO_TEXTDOMAIN ),
				'something'   => __( 'Something went wrong.', PLGINMNGRPRO_TEXTDOMAIN ),
			),

			'terms'    => array(
				'group'  => __( 'Group',  PLGINMNGRPRO_TEXTDOMAIN ),
				'lock'   => __( 'Lock',   PLGINMNGRPRO_TEXTDOMAIN ),
				'unlock' => __( 'Unlock', PLGINMNGRPRO_TEXTDOMAIN ),
				'hide'   => __( 'Hide',   PLGINMNGRPRO_TEXTDOMAIN ),
				'unhide' => __( 'Unhide', PLGINMNGRPRO_TEXTDOMAIN ),
			),

			'colours'      => $this->colours,
			'settings'     => Option::get(),
			'data'         => Database::get_json_array( $this->group ),
			'plugin_group' => $this->group,

			'nonce'        => wp_create_nonce( PLGINMNGRPRO_TEXTDOMAIN ),
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
			<?php foreach( $this->colours as $key => $colour ) : ?>
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
