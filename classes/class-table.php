<?php
/**
 * Table Controller
 *
 * @package Plugin Manager
 * @since   6.0.0
 * @author  Sujin 수진 Choi http://www.sujinc.com/donation
*/

namespace Sujin\Plugin\PluginMgr;

use Sujin\Plugin\PluginMgr\Traits\Config;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

class Table extends Plugin_Base {
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

		$this->add_actions_header();
		$this->add_layout_filters();
		$this->execute_bulk_actions();
	}

	/**
	 * Header Filters.
	 *
	 * @since  0.0.1
	 * @access private
	 */
	private function add_actions_header() {
		add_action( 'in_admin_header',            array( $this, 'change_admin_title' ) );
		add_action( 'pre_current_active_plugins', array( $this, 'print_settings_icons' ) );
	}

	/**
	 * Layout Filters.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @return void.
	 */
	private function add_layout_filters() {
		// Buttons
		add_filter( 'network_admin_plugin_action_links' , array( $this, 'print_buttons' ), 15, 2 );
		add_filter( 'plugin_action_links' ,               array( $this, 'print_buttons' ), 15, 2 );

		// Subsubsub
		add_filter( "views_plugins",         array( $this, 'modify_subsubsub' ) );
		add_filter( "views_plugins-network", array( $this, 'modify_subsubsub' ) );

		// On Description Column
		add_filter( "plugin_row_meta", array( $this, 'print_groups_on_description' ), 15, 3 );
	}

	public function change_admin_title() {
		global $title;

		switch( $this->group ) {
			case false :
				break;

			case 'not' :
				$title .= ' : None';
				break;

			default :
				$group = Database::get_group_by_id( $this->group );
				if ( $group )
					$title .= ' : {{ group_name() }}';
				break;
		}
	}

	public function print_settings_icons() {
		echo '<a href="" class="gmr-options-button" ng-click="showOptions()"><span class="dashicons dashicons-admin-generic"></span></a>';
		printf( '
			<a href="" class="gmr-options-button" ng-click="toggleHidden()" ng-show="num_of_hidden()">
				<span ng-class="{\'dashicons-hidden\': mode_show_hidden == true, \'dashicons-visibility\': mode_show_hidden == false}" class="dashicons"></span>
				{{ mode_show_hidden ? \'%s\' : \'%s\'}}
				<span class="count">{{num_of_hidden()}}</span>
			</a>
			', __( 'Hide Hidden', SUJIN_PLUGIN_MGR_SLUG ), __( 'Show Hidden', SUJIN_PLUGIN_MGR_SLUG ) );
		echo '<div class="clear" />';

		if ( ! empty( $this->group ) && $this->group !== 'not' ) {
			printf( '<div id="gmr-group-description" class="description">{{getGroupDescription(%s)}}</div>', $this->group );
		}
	}

	/**
	 * Bulk Actions.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @return void
	 */
	public function execute_bulk_actions() {
		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );

		if ( $action = $wp_list_table->current_action() ) {
			$checked = $_REQUEST['checked'];

			switch( $action ) {
				case 'lock-selected' :
					foreach( $checked as $value )
						Database::lock_plugin( $value );

					break;

				case 'hide-selected' :
					foreach( $checked as $value )
						Database::hide_plugin( $value );

					break;

				case 'unhide-selected' :
					foreach( $checked as $value )
						Database::unhide_plugin( $value );

					break;
				default :
					return;
			}
		}
	}

	/**
	 * Print Button on Plugin Title Area.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @return array Actions.
	 */
	public function print_buttons( $actions, $plugin_file ) {
		$hide_text = Option::get( 'hide_text' );
		$text_class = !empty( $hide_text ) ? 'ng-hide' : '';

		ob_start();
		?>
		<a href="" class="button-grouping button-plugin-manager" ng-click="showModal('%s')">
			<span class="dashicons dashicons-groups"></span>
			<span class="text %s" ng-hide="isHideText()">%s</span>
		</a>
		<?php
		$group_html = ob_get_clean();

		ob_start();
		?>
		<a href="" class="button-lock button-plugin-manager" ng-click="lockPlugin('%s')">
			<span class="dashicons dashicons-{{ isLocked('%s') ? 'unlock' : 'lock' }}"></span>
			<span class="text %s" ng-hide="isHideText()">{{ isLocked('%s') ? '%s' : '%s' }}</span>
		</a>
		<?php
		$lock_html = ob_get_clean();

		ob_start();
		?>
		<a href="" class="button-hide button-plugin-manager" ng-click="hidePlugin('%s')">
			<span class="dashicons dashicons-{{ isHidden('%s') ? 'visibility' : 'hidden' }}"></span>
			<span class="text %s" ng-hide="isHideText()">{{ isHidden('%s') ? '%s' : '%s'}}</span>
		</a>
		<?php
		$hide_html = ob_get_clean();

		$actions['group'] = sprintf( $group_html, $plugin_file, $text_class, __( 'Group', SUJIN_PLUGIN_MGR_SLUG ) );
		$actions['lock']  = sprintf( $lock_html, $plugin_file, $plugin_file, $text_class, $plugin_file, __( 'Unlock', SUJIN_PLUGIN_MGR_SLUG ), __( 'Lock', SUJIN_PLUGIN_MGR_SLUG ) );
		$actions['hide']  = sprintf( $hide_html, $plugin_file, $plugin_file, $text_class, $plugin_file, __( 'Unhide', SUJIN_PLUGIN_MGR_SLUG ), __( 'Hide', SUJIN_PLUGIN_MGR_SLUG ) );

		return $actions;
	}

	/**
	 * Modify SubSubSub Area
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @param  array $views
	 *
	 * @return array view data.
	 */
	public function modify_subsubsub( $views ) {
		$this->echo_group_subsubsub();

		// When Plugin Group Mode, change urls.
		return $this->modify_subsubsub_urls( $views );
	}

	/**
	 * Echo Groups SubSubSub
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @param  array $views
	 *
	 * @return array view data.
	 */
	private function echo_group_subsubsub() {
		?>
		<section ng-controller="MenuController as menu" ng-show="showMenu" class="ng-hide {{key}}">
			<ul class='subsubsub plugin-groups'>
				<li><strong><?php _e( 'Groups', SUJIN_PLUGIN_MGR_SLUG ) ?></strong></li>
				<li class="">
					<a href="<?php echo $this->get_plugins_admin_uri() ?>" class="<?php echo ( empty( $this->group ) ) ? 'current' : '' ?>">
						<?php _e( 'All', SUJIN_PLUGIN_MGR_SLUG ) ?>
						<span class="count">({{ getNumPlugins()}} )</span>
					</a>
				</li>
				<li class="not-in-any-groups">
					<a href="<?php echo $this->get_plugins_admin_uri() ?>?group=not" class="<?php echo ( $this->group == 'not' ) ? 'current' : '' ?>">
						<?php _e( 'None', SUJIN_PLUGIN_MGR_SLUG ) ?>
						<span class="count">({{numNoneGroup()}})</span>
					</a>
				</li>

				<li ng-repeat="group in getGroups()" class="colour group {{group.colour}}" style="{{group.colourStyle}}">
					<a href="<?php echo $this->get_plugins_admin_uri() ?>?group={{group.ID}}" ng-class="{'current':plugin_group == group.ID}">
						{{group.group_name}}
						<span class="count">{{group.count}}</span>
					</a>
				</li>
			</ul>

			<div class='clear'></div>
		</section>
		<?php
	}

	/**
	 * When Group View Mode, change the url parameters.
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @param  array $views
	 *
	 * @return array view data.
	 */
	private function modify_subsubsub_urls( $views ) {
		if ( !empty( $this->group ) ) {
			foreach( $views as $key => &$html ) {
				$doc = new \DOMDocument();
				$doc->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );

				foreach( $doc->getElementsByTagName( 'a' ) as $link ) {
					$link_new = add_query_arg( 'plugin_group', $this->group, $link->getAttribute('href') );
					$link->setAttribute( 'href', $link_new );
				}

				$html = $doc->saveHTML();
			}
		}



		return $views;
	}

	/**
	 * Print Groups on Description Area
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @param  array  $plugin_meta
	 * @param  string $plugin_file
	 * @param  array  $plugin_data
	 *
	 * @return array $plugin_meta.
	 */
	public function print_groups_on_description( $plugin_meta, $plugin_file, $plugin_data ) {
		?>
		<div class="groups">
			<a ng-repeat="group in getPluginGroups('<?php echo $plugin_file ?>')" href="<?php $this->get_plugins_admin_uri() ?>?group={{group.ID}}" class="{{group.colour}}" style="{{group.colourStyle}}">{{group.group_name}}</a>
		</div>
		<?php

		return $plugin_meta;
	}

	/**
	 * Get Plugins Page URL
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @return string URL.
	 */
	private function get_plugins_admin_uri() {
		$strings   = array();
		$strings[] = get_bloginfo( 'url' );
		$strings[] = 'wp-admin';
		( ! is_network_admin() ) || $strings[] = 'network';
		$strings[] = 'plugins.php';

		return implode( '/', $strings );
	}
}
