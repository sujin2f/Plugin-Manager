<?php
/**
 *
 * Hide
 *
 * project	Plugin Manager
 * version: 3.0.1
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

class Hide {
	private $subsubsub_info = array();
	private $option;

	function __construct() {
		$this->option = get_option( '_plugin-manager_', array() );

		// Ajax
		add_action( 'wp_ajax_PIGPR_HIDE', array( $this, 'AjaxHide' ) );
		add_action( 'wp_ajax_PIGPR_SHOW', array( $this, 'AjaxShow' ) );

		// 액션 링크
		add_filter( 'network_admin_plugin_action_links' , array( $this, 'AddActionLink' ), 15, 4 );
		add_filter( 'plugin_action_links' , array( $this, 'AddActionLink' ), 15, 4 );

		// Subsubsub
		add_filter( "views_plugins", array( $this, 'ModifySubsubsub' ) );
		add_filter( "views_plugins-network", array( $this, 'ModifySubsubsub' ) );

		add_filter( 'all_plugins', array( $this, 'GetHiddenPluginsQuery' ), 30 );
	}

	public function GetHiddenPluginsQuery( $all_plugins ) {
		if ( !empty( $_GET[ 'plugin_status' ] ) && $_GET[ 'plugin_status' ] == 'hidden' ) {
			// Get Numbers : From class-wp-plugins-list-table.php
			$plugins = array(
				'all'                => $all_plugins,
				'search'             => array(),
				'active'             => array(),
				'inactive'           => array(),
				'recently_activated' => array(),
				'upgrade'            => array(),
				'mustuse'            => array(),
				'dropins'            => array(),
			);

			$screen = get_current_screen();

			if ( ! is_multisite() || ( $screen->in_admin( 'network' ) && current_user_can( 'manage_network_plugins' ) ) ) {

				/**
				 * Filter whether to display the advanced plugins list table.
				 *
				 * There are two types of advanced plugins - must-use and drop-ins -
				 * which can be used in a single site or Multisite network.
				 *
				 * The $type parameter allows you to differentiate between the type of advanced
				 * plugins to filter the display of. Contexts include 'mustuse' and 'dropins'.
				 *
				 * @since 3.0.0
				 *
				 * @param bool   $show Whether to show the advanced plugins for the specified
				 *                     plugin type. Default true.
				 * @param string $type The plugin type. Accepts 'mustuse', 'dropins'.
				 */
				if ( apply_filters( 'show_advanced_plugins', true, 'mustuse' ) ) {
					$plugins['mustuse'] = get_mu_plugins();
				}

				/** This action is documented in wp-admin/includes/class-wp-plugins-list-table.php */
				if ( apply_filters( 'show_advanced_plugins', true, 'dropins' ) )
					$plugins['dropins'] = get_dropins();

				if ( current_user_can( 'update_plugins' ) ) {
					$current = get_site_transient( 'update_plugins' );
					foreach ( (array) $plugins['all'] as $plugin_file => $plugin_data ) {
						if ( isset( $current->response[ $plugin_file ] ) ) {
							$plugins['all'][ $plugin_file ]['update'] = true;
							$plugins['upgrade'][ $plugin_file ] = $plugins['all'][ $plugin_file ];
						}
					}
				}
			}

			if ( ! $screen->in_admin( 'network' ) ) {
				$show = current_user_can( 'manage_network_plugins' );
				/**
				 * Filter whether to display network-active plugins alongside plugins active for the current site.
				 *
				 * This also controls the display of inactive network-only plugins (plugins with
				 * "Network: true" in the plugin header).
				 *
				 * Plugins cannot be network-activated or network-deactivated from this screen.
				 *
				 * @since 4.4.0
				 *
				 * @param bool $show Whether to show network-active plugins. Default is whether the current
				 *                   user can manage network plugins (ie. a Super Admin).
				 */
				$show_network_active = apply_filters( 'show_network_active_plugins', $show );
			}

			set_transient( 'plugin_slugs', array_keys( $plugins['all'] ), DAY_IN_SECONDS );

			if ( $screen->in_admin( 'network' ) ) {
				$recently_activated = get_site_option( 'recently_activated', array() );
			} else {
				$recently_activated = get_option( 'recently_activated', array() );
			}

			foreach ( $recently_activated as $key => $time ) {
				if ( $time + WEEK_IN_SECONDS < time() ) {
					unset( $recently_activated[$key] );
				}
			}

			if ( $screen->in_admin( 'network' ) ) {
				update_site_option( 'recently_activated', $recently_activated );
			} else {
				update_option( 'recently_activated', $recently_activated );
			}

			$plugin_info = get_site_transient( 'update_plugins' );

			foreach ( (array) $plugins['all'] as $plugin_file => $plugin_data ) {
				// Extra info if known. array_merge() ensures $plugin_data has precedence if keys collide.
				if ( isset( $plugin_info->response[ $plugin_file ] ) ) {
					$plugins['all'][ $plugin_file ] = $plugin_data = array_merge( (array) $plugin_info->response[ $plugin_file ], $plugin_data );
					// Make sure that $plugins['upgrade'] also receives the extra info since it is used on ?plugin_status=upgrade
					if ( isset( $plugins['upgrade'][ $plugin_file ] ) ) {
						$plugins['upgrade'][ $plugin_file ] = $plugin_data = array_merge( (array) $plugin_info->response[ $plugin_file ], $plugin_data );
					}

				} elseif ( isset( $plugin_info->no_update[ $plugin_file ] ) ) {
					$plugins['all'][ $plugin_file ] = $plugin_data = array_merge( (array) $plugin_info->no_update[ $plugin_file ], $plugin_data );
					// Make sure that $plugins['upgrade'] also receives the extra info since it is used on ?plugin_status=upgrade
					if ( isset( $plugins['upgrade'][ $plugin_file ] ) ) {
						$plugins['upgrade'][ $plugin_file ] = $plugin_data = array_merge( (array) $plugin_info->no_update[ $plugin_file ], $plugin_data );
					}
				}

				// Filter into individual sections
				if ( is_multisite() && ! $screen->in_admin( 'network' ) && is_network_only_plugin( $plugin_file ) && ! is_plugin_active( $plugin_file ) ) {
					if ( $show_network_active ) {
						// On the non-network screen, show inactive network-only plugins if allowed
						$plugins['inactive'][ $plugin_file ] = $plugin_data;
					} else {
						// On the non-network screen, filter out network-only plugins as long as they're not individually active
						unset( $plugins['all'][ $plugin_file ] );
					}
				} elseif ( ! $screen->in_admin( 'network' ) && is_plugin_active_for_network( $plugin_file ) ) {
					if ( $show_network_active ) {
						// On the non-network screen, show network-active plugins if allowed
						$plugins['active'][ $plugin_file ] = $plugin_data;
					} else {
						// On the non-network screen, filter out network-active plugins
						unset( $plugins['all'][ $plugin_file ] );
					}
				} elseif ( ( ! $screen->in_admin( 'network' ) && is_plugin_active( $plugin_file ) )
					|| ( $screen->in_admin( 'network' ) && is_plugin_active_for_network( $plugin_file ) ) ) {
					// On the non-network screen, populate the active list with plugins that are individually activated
					// On the network-admin screen, populate the active list with plugins that are network activated
					$plugins['active'][ $plugin_file ] = $plugin_data;
				} else {
					if ( isset( $recently_activated[ $plugin_file ] ) ) {
						// Populate the recently activated list with plugins that have been recently activated
						$plugins['recently_activated'][ $plugin_file ] = $plugin_data;
					}
					// Populate the inactive list with plugins that aren't activated
					$plugins['inactive'][ $plugin_file ] = $plugin_data;
				}
			}

			if ( strlen( $s ) ) {
				$status = 'search';
				$plugins['search'] = array_filter( $plugins['all'], array( $this, '_search_callback' ) );
			}

			$this->subsubsub_info = $plugins;

			// Modify Query
			$hidden_plugins = get_option( 'plugin_hidden' );

			$plugins_ = array();
			$plugin_info = get_site_transient( 'update_plugins' );

			foreach ( (array) $all_plugins as $plugin_file => $plugin_data ) {
				if ( isset( $plugin_info->no_update[ $plugin_file ] ) ) {
					$plugins[ $plugin_file ] = $plugin_data = array_merge( (array) $plugin_info->no_update[ $plugin_file ], $plugin_data );
				}
			}

			foreach( $all_plugins as $key => $plugin_data ) {
				if ( array_key_exists( $key, $hidden_plugins ) ) {
					$plugins_[$key] = $plugin_data;
				}
			}

			return $plugins_;
		}

		return $all_plugins;
	}

	public function ModifySubsubsub( $views ) {
		if ( $this->subsubsub_info ) {
			$views = array();
			foreach( $this->subsubsub_info as $key => $value ) {
				if ( count( $value ) ) {
					$link = add_query_arg( 'plugin_status', $key );
					$views[ $key ] = sprintf( '<a href="%s">%s <span class="count">(%s)</span></a>', $link, ucfirst( $key ), count( $value ) );
				}
			}
		}

		$hidden_plugins = get_option( 'plugin_hidden', array() );
		$nof_hidden = 0;

		// Get Number of Hidden Items
		if ( !empty( $_GET[ 'plugin_group' ] ) ) {
			$group_id = $_GET[ 'plugin_group' ];
			$groups_plugin_match = get_option( 'groups_plugin_match' );

			if ( !empty( $groups_plugin_match[ $group_id ] ) ) {
				foreach( $groups_plugin_match[ $group_id ] as $plugin_id => $v ) {
					if ( !empty( $hidden_plugins[$plugin_id] ) )
						$nof_hidden++;
				}
			}
		} else {
			$nof_hidden = count( $hidden_plugins );
		}

		$class_current = ( !empty( $_GET[ 'plugin_status' ] ) && $_GET[ 'plugin_status' ] == 'hidden' ) ? 'class="current"' : '';
		$link_new = add_query_arg( 'plugin_status', 'hidden' );

		$views[ 'view_hidden' ] =  sprintf('<a href="%s" %s>Hidden <span class="count">(%s)</span></a>', $link_new, $class_current, $nof_hidden );
		return $views;
	}

	public function AjaxHide() {
		$plugin_file = $_POST['plugin_file'];
		$hidden_plugins = get_option( 'plugin_hidden' );
		if ( is_array( $hidden_plugins ) && isset( $hidden_plugins[ $plugin_file ] ) ) wp_die();

		$hidden_plugins[ $plugin_file ] = true;
		update_option( 'plugin_hidden', $hidden_plugins );
		wp_die();
	}

	public function AjaxShow() {
		$plugin_file = $_POST['plugin_file'];
		$hidden_plugins = get_option( 'plugin_hidden' );
		if ( is_array( $hidden_plugins ) && !isset( $hidden_plugins[ $plugin_file ] ) ) wp_die();

		unset( $hidden_plugins[ $plugin_file ] );
		update_option( 'plugin_hidden', $hidden_plugins );
		wp_die();
	}

	public function AddActionLink( $actions, $plugin_file, $plugin_data, $a ) {
		$text_class = !empty( $this->option[ 'hide_text' ] ) ? 'hidden' : '';

		$class = $this->isHidden( $plugin_file ) ? 'hidden' : '';
		$actions['hide'] = sprintf(
			'<a href="#" class="button-show button-plugin-manager" data-id="%s" data-plugin_file="%s">
				<span class="dashicons dashicons-visibility"></span>
				<span class="text %s">%s</span>
			</a>

			<a href="#" class="button-hide button-plugin-manager" data-hidden="%s" data-id="%s" data-plugin_file="%s">
				<span class="dashicons dashicons-hidden"></span>
				<span class="text  %s">%s</span>
			</a>',

			sanitize_title( $plugin_data['Name'] ),
			$plugin_file,
			$text_class,
			__( 'Show', PIGPR_TEXTDOMAIN ),

			$class,
			sanitize_title( $plugin_data['Name'] ),
			$plugin_file,
			$text_class,
			__( 'Hide', PIGPR_TEXTDOMAIN )
		);

		return $actions;
	}

	private function isHidden( $plugin_file ) {
		$hidden_plugins = get_option( 'plugin_hidden' );
		if ( is_array( $hidden_plugins ) && !empty( $hidden_plugins[ $plugin_file ] ) ) return true;

		if ( isset( $hidden_plugins[ $plugin_file ] ) ) return true;

		return false;
	}
}
