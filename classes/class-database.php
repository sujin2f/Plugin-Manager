<?php
/**
 * Database Controller
 *
 * @package Plugin Manager
 * @since   6.0.1
 * @author  Sujin 수진 Choi http://www.sujinc.com/donation
*/

namespace Sujin\Plugin\PluginMgr;

use Sujin\Plugin\PluginMgr\Constants\Colour;

if ( !defined( "ABSPATH" ) ) {
	header( "Status: 403 Forbidden" );
	header( "HTTP/1.1 403 Forbidden" );
	exit();
}

// Colour::$COLOURS

class Database {
	/**
	 * Table Names
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @const    string TBL_PREFIX
	 * @const    string TBL_PLUGINS
	 * @const    string TBL_GROUPS
	 * @const    string TBL_RELATION
	 */
	const TBL_PREFIX   = 'plugin_manager_';
	const TBL_PLUGINS  = 'plugins';
	const TBL_GROUPS   = 'groups';
	const TBL_RELATION = 'group_plugin';

	/**
	 * Get Json Array.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @return array.
	 */
	public static function get_json_array( $selected_group ) {
		$plugins = self::get_plugins( $selected_group );

		return array(
			'groups'         => self::get_groups(),
			'plugins'        => $plugins,
			'num_hidden'     => (int) self::get_num_hidden( $selected_group ),
			'num_none_group' => count( $plugins ) - self::get_num_grouped(),
		);
	}

	/**
	 * Get Groups.
	 *
	 * @since  0.0.1
	 * @access private
	 *
	 * @return array.
	 */
	private static function get_groups() {
		global $wpdb;
		$tbl = self::get_table_names();

		$groups = $wpdb->get_results( $wpdb->prepare( "
			SELECT
				groups.*,
				COUNT( relation.group_id ) as count

				FROM {$tbl['groups']} groups

				LEFT OUTER JOIN {$tbl['relation']} relation ON relation.group_id = groups.ID

				WHERE groups.`user_id` = %d
				GROUP BY groups.ID
				ORDER BY groups.`order`;
			",
			get_current_user_id()
		), ARRAY_A );

		foreach( $groups as &$group ) {
			if ( substr( $group['colour'], 0, 1 ) == '#' ) {
				$group['colourStyle'] = sprintf( 'background-color: %s !important', $group['colour'] );
				$group['colour']      = 'Black';
			}

			$group[ 'hidden_main' ] = (bool) $group[ 'hidden_main' ];
			$group[ 'plugins' ] = self::get_plugins_by_group( $group );
		}

		return $groups;
	}

	/**
	 * Get Group by ID.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @param  int $ID
	 *
	 * @return array.
	 */
	public static function get_group_by_id( $ID ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$group = $wpdb->get_row( $wpdb->prepare( "
			SELECT
				groups.*,
				COUNT( relation.group_id ) as count

				FROM {$tbl['groups']} groups

				LEFT OUTER JOIN {$tbl['relation']} relation ON relation.group_id = groups.ID

				WHERE
					groups.`user_id` = %d AND
					groups.`ID`      = %d

				GROUP BY groups.ID

				ORDER BY groups.`order`;
			",
			get_current_user_id(), $ID
		), ARRAY_A );

		if ( $group )
			$group[ 'plugins' ] = self::get_plugins_by_group( $group );

		return $group;
	}

	/**
	 * Get Plugins by Group.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @param  array $group
	 *
	 * @return array.
	 */
	public static function get_plugins_by_group( $group_id ) {
		global $wpdb;
		$tbl = self::get_table_names();

		if ( is_array( $group_id ) ) {
			$group_id = $group_id['ID'];
		}

		$plugins   = array();

		if ( $group_id === 'not' ) {
			$plugins_t = $wpdb->get_results(
				$wpdb->prepare( "
					SELECT *
						FROM {$tbl['plugins']} plugins

						LEFT OUTER JOIN {$tbl['relation']} relation
							ON relation.plugin_id = plugins.ID

						WHERE
							plugins.`user_id` = %d AND
							relation.`group_id` IS NULL",
					get_current_user_id()
				), ARRAY_A );

		} else {
			$plugins_t = $wpdb->get_results( $wpdb->prepare( "
				SELECT plugins.*
					FROM {$tbl['plugins']} plugins

					LEFT OUTER JOIN {$tbl['relation']} relation ON relation.plugin_id = plugins.ID
					LEFT OUTER JOIN {$tbl['groups']} groups ON relation.group_id = groups.ID

					WHERE
						groups.`ID` = %d
				",
				$group_id
			), ARRAY_A );
		}

		foreach( $plugins_t as $plugin ) {
			$plugins[ $plugin['file_name'] ] = $plugin;
		}

		return $plugins;
	}

	public static function get_locked() {
		global $wpdb;
		$tbl = self::get_table_names();

		$plugins_t = $wpdb->get_results(
			$wpdb->prepare( "
				SELECT *
					FROM {$tbl['plugins']}

					WHERE
						`user_id` = %d AND
						`locked` = 1",
				get_current_user_id()
			), ARRAY_A );

		return $plugins_t;
}

	/**
	 * Get Plugins.
	 *
	 * @since  0.0.1
	 * @access private static
	 *
	 * @param  array $setting
	 *
	 * @return bool.
	 */
	private static function get_plugins( $selected_group = false ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$plugins = array();
		$plugins_temp = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$tbl['plugins']} WHERE `user_id` = %d",
				get_current_user_id()
			), ARRAY_A );

		foreach( $plugins_temp as &$plugin ) {
			$plugin[ 'groups' ] = $wpdb->get_results( $wpdb->prepare( "
				SELECT groups.* FROM {$tbl['relation']} relation
					LEFT OUTER JOIN {$tbl['plugins']} plugins ON relation.plugin_id = plugins.ID
					LEFT OUTER JOIN {$tbl['groups']} groups ON relation.group_id = groups.ID

					WHERE
						groups.`user_id` = %d AND
						plugins.`file_name` = '%s'
				",
				get_current_user_id(),
				$plugin['file_name']
			), ARRAY_A );
		}

		unset($plugin);

		foreach( $plugins_temp as $plugin ) {
			$plugins[ $plugin['file_name'] ] = $plugin;

			foreach( $plugins[ $plugin['file_name'] ][ 'groups' ] as &$group ) {
				if ( substr( $group['colour'], 0, 1 ) == '#' ) {
					$group['colourStyle'] = sprintf( 'background-color: %s !important', $group['colour'] );
					$group['colour']      = 'Black';
				}
			}
		}

		if ( empty( $selected_group ) ) {
			unset($plugin);
			unset($group);

			foreach( $plugins as &$plugin ) {
				foreach( $plugin[ 'groups' ] as $group ) {
					if ( ! empty( $group[ 'hidden_main' ] ) ) {
						$plugin[ 'hidden' ] = 1;
						continue;
					}
				}
			}
		}

		return $plugins;
	}

	/**
	 * Get Number of Hidden Plugins.
	 *
	 * @since  0.0.1
	 * @access private static
	 *
	 * @param  array $setting
	 *
	 * @return bool.
	 */
	private static function get_num_hidden( $selected_group ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$where = array();
		$where[ 'user_id' ] = 'plugins.`user_id` = %d';
		$where[ 'hidden' ]  = 'plugins.`hidden`  = 1';

		if ( $selected_group ) {
			if ( $selected_group === 'not' ) {
				$where[] = 'groups.`ID` IS NULL';
			} else {
				$where[] = 'groups.`ID` = ' . $selected_group;
			}
		} else {
			$where[ 'hidden' ] = '( plugins.`hidden`  = 1 OR groups.`hidden_main` = 1 )';
		}

		$where = implode( ' AND ', $where );

		$num_hidden_plugins = $wpdb->get_var(
			$wpdb->prepare( "
				SELECT COUNT( DISTINCT plugins.ID )
					FROM {$tbl['plugins']} plugins

					LEFT OUTER JOIN {$tbl['relation']} relation
						ON relation.plugin_id = plugins.ID

					LEFT OUTER JOIN {$tbl['groups']} groups
						ON relation.group_id = groups.ID

					WHERE {$where}
				",
				get_current_user_id()
			) );

		return $num_hidden_plugins;
	}

	/**
	 * Get Number of Grouped Plugins.
	 *
	 * @since  0.0.1
	 * @access private static
	 *
	 * @param  array $setting
	 *
	 * @return bool.
	 */
	private static function get_num_grouped() {
		global $wpdb;
		$tbl = self::get_table_names();

		return (int) $wpdb->get_var(
			$wpdb->prepare( "
				SELECT COUNT( DISTINCT plugins.ID )
					FROM {$tbl['plugins']} plugins

					LEFT OUTER JOIN {$tbl['relation']} relation
						ON relation.plugin_id = plugins.ID

					LEFT OUTER JOIN {$tbl['groups']} groups
						ON relation.group_id = groups.ID

					WHERE
						plugins.`user_id` = %d AND
						groups.ID IS NOT NULL
					",
				get_current_user_id()
			) );
	}

	/**
	 * Create Group.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @param  array $setting
	 */
	public static function create_group( $setting ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$order = self::get_last_order();

		$wpdb->insert(
			$tbl['groups'],
			array(
				'group_name'  => $setting['group_name'],
				'user_id'     => get_current_user_id(),
				'colour'      => $setting['colour'],
				'description' => $setting['description'],
				'order'       => $order,
				'hidden_main' => $setting['hidden_main'],
			),
			array( '%s', '%d', '%s', '%s', '%d', '%d' )
		);

		$group_id = $wpdb->insert_id;

		if ( !empty( $setting['plugin_id'] ) )
			self::insert_plugin_into_group( $group_id, $setting['plugin_id'] );

		return $group_id;
	}

	/**
	 * Insert Plugin into Group.
	 *
	 * @since  0.0.1
	 * @access private static
	 *
	 * @param  int    $group_id
	 * @param  string $plugin_file_name
	 */
	private static function insert_plugin_into_group( $group_id, $plugin_file_name ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$plugin_id = self::get_plugin_by_file_name( $plugin_file_name );

		$wpdb->insert(
			$tbl['relation'],
			array(
				'plugin_id'  => $plugin_id,
				'group_id'   => $group_id,
			)
		);
	}

	/**
	 * Get Plugin by its File Name.
	 *
	 * @since  0.0.1
	 * @access private static
	 *
	 * @param  string $plugin_file_name
	 *
	 * @return  int
	 */
	private static function get_plugin_by_file_name( $plugin_file_name ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$plugin_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT ID FROM {$tbl['plugins']}
				WHERE `file_name` = %s;
			",
			$plugin_file_name
		));

		if ( ! $plugin_id ) {
			$plugin_id = self::add_plugin( $plugin_file_name );
		}

		return $plugin_id;
	}

	/**
	 * Add Plugin.
	 *
	 * @since  0.0.1
	 * @access private static
	 *
	 * @param  string $plugin_file_name
	 *
	 * @return  int
	 */
	private static function add_plugin( $plugin_file_name ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$wpdb->insert(
			$tbl['plugins'],
			array(
				'file_name'  => $plugin_file_name,
				'user_id'    => get_current_user_id(),
			),
			array( '%s' )
		);

		return $wpdb->insert_id;
	}

	/**
	 * Delete Plugin.
	 *
	 * @since  0.0.1
	 * @access private static
	 *
	 * @param  string $plugin_file_name
	 */
	private static function delete_plugin( $plugin_file_name ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$wpdb->delete(
			$tbl['plugins'],
			array(
				'file_name'  => $plugin_file_name,
				'user_id'    => get_current_user_id(),
			),
			array( '%s' )
		);
	}

	/**
	 * Toggle Lock.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @param  string $plugin_file_name
	 */
	public static function toggle_lock_plugin( $plugin_file_name ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$locked = $wpdb->get_var(
			$wpdb->prepare( "
				SELECT `locked` FROM {$tbl['plugins']}
					WHERE
						`user_id` = %d AND
						`file_name` = '%s'
				",
				get_current_user_id(),
				$plugin_file_name
			) );

		$locked = !$locked;

		$wpdb->update(
			$tbl['plugins'],
			array(
				'locked' => $locked,
			),
			array(
				'user_id'   => get_current_user_id(),
				'file_name' => $plugin_file_name,
			),
			array( '%d' ),
			array( '%d', '%s' )
		);

		delete_site_transient( 'update_plugins' );
	}

	/**
	 * Toggle Hidden.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @param  string $plugin_file_name
	 */
	public static function toggle_hide_plugin( $plugin_file_name ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$hidden = $wpdb->get_var(
			$wpdb->prepare( "
				SELECT `hidden` FROM {$tbl['plugins']}
					WHERE
						`user_id` = %d AND
						`file_name` = '%s'
				",
				get_current_user_id(),
				$plugin_file_name
			) );

		$hidden = !$hidden;

		$wpdb->update(
			$tbl['plugins'],
			array(
				'hidden' => $hidden,
			),
			array(
				'user_id'   => get_current_user_id(),
				'file_name' => $plugin_file_name,
			),
			array( '%d' ),
			array( '%d', '%s' )
		);
	}

	/**
	 * Toggle Relationship.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @param  int    $group_id
	 * @param  string $plugin_file_name
	 */
	public static function toggle_relationship( $group_id, $plugin_file_name, $checked ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$plugin_id = $wpdb->get_var(
			$wpdb->prepare( "
				SELECT `ID` FROM {$tbl['plugins']}
					WHERE
						`user_id`   = %d AND
						`file_name` = '%s'
				",
				get_current_user_id(),
				$plugin_file_name
			) );

		$is_exists = $wpdb->get_var(
			$wpdb->prepare( "
				SELECT `plugin_id` FROM {$tbl['relation']}
					WHERE
						`plugin_id` = %d AND
						`group_id`  = %d
				",
				$plugin_id,
				$group_id
			) );

		if ( !$is_exists && $checked === 'true' ) {
			$wpdb->insert(
				$tbl['relation'],
				array(
					'group_id'  => $group_id,
					'plugin_id' => $plugin_id,
				),
				array(
					'%d',
					'%d',
				)
			);
		} else if ( $is_exists && $checked === 'false' ) {
			$wpdb->delete( $tbl['relation'], array( 'group_id' => $group_id, 'plugin_id' => $plugin_id ) );
		}
	}

	/**
	 * Delete Group.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @param  array $group_data
	 */
	public static function delete_group( $group_data ) {
		global $wpdb;
		$tbl = self::get_table_names();
		$wpdb->delete( $tbl['groups'], array( 'ID' => $group_data[ 'ID' ] ), array( '%d' ) );
	}

	/**
	 * Edit Group.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @param  array $group_data
	 */
	public static function edit_group( $group_data ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$wpdb->update(
			$tbl['groups'],
			array(
				'group_name'  => $group_data[ 'name' ],
				'colour'      => $group_data[ 'colour' ],
				'description' => $group_data[ 'description' ],
				'hidden_main' => ( $group_data[ 'hidden_main' ] == 'true' ) ? 1 : 0,
			),
			array( 'ID' => $group_data[ 'ID' ] ),
			array(
				'%s',
				'%s',
				'%s',
				'%d',
			),
			array( '%d' )
		);
	}

	/**
	 * Set Group Order.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @param  array $orders
	 */
	public static function set_order( $orders ) {
		global $wpdb;
		$tbl = self::get_table_names();
		$orders = explode( ',', $orders );

		foreach( $orders as $order => $ID ) {
			$wpdb->update(
				$tbl['groups'],
				array(
					'order' => $order,
				),
				array( 'ID' => $ID ),
				array( '%d' ),
				array( '%d' )
			);
		}
	}

	/**
	 * Lock.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @param  int $plugin_id
	 */
	public static function lock_plugin( $plugin_id ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$wpdb->update(
			$tbl['plugins'],
			array(
				'locked' => true,
			),
			array(
				'user_id'   => get_current_user_id(),
				'file_name' => $plugin_id,
			),
			array( '%d' ),
			array( '%d', '%s' )
		);

		delete_site_transient( 'update_plugins' );
	}

	/**
	 * Hide.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @param  int $plugin_id
	 */
	public static function hide_plugin( $plugin_id ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$wpdb->update(
			$tbl['plugins'],
			array(
				'hidden' => true,
			),
			array(
				'user_id'   => get_current_user_id(),
				'file_name' => $plugin_id,
			),
			array( '%d' ),
			array( '%d', '%s' )
		);
	}

	/**
	 * UnHide.
	 *
	 * @since  0.0.1
	 * @access public
	 *
	 * @param  int $plugin_id
	 */
	public static function unhide_plugin( $plugin_id ) {
		global $wpdb;
		$tbl = self::get_table_names();

		$wpdb->update(
			$tbl['plugins'],
			array(
				'hidden' => false,
			),
			array(
				'user_id'   => get_current_user_id(),
				'file_name' => $plugin_id,
			),
			array( '%d' ),
			array( '%d', '%s' )
		);
	}

	private static function get_last_order() {
		global $wpdb;
		$tbl = self::get_table_names();

		$order = $wpdb->get_var( $wpdb->prepare( "
			SELECT MAX(`order`) FROM {$tbl['groups']}
				WHERE `user_id` = %d;
			",
			get_current_user_id()
		));

		return ( is_null( $order ) ) ? 0 : $order + 1;
	}

	/**
	 * Get Table Names.
	 *
	 * @since  0.0.1
	 * @access private static
	 *
	 * @return array.
	 */
	private static function get_table_names() {
		global $wpdb;

		return array(
			'plugins'  => $wpdb->prefix . self::TBL_PREFIX . self::TBL_PLUGINS,
			'groups'   => $wpdb->prefix . self::TBL_PREFIX . self::TBL_GROUPS,
			'relation' => $wpdb->prefix . self::TBL_PREFIX . self::TBL_RELATION,
		);
	}

	/**
	 * Check If Table Exist.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @return void.
	 */
	public static function is_tables_exist() {
		if ( get_option( SUJIN_PLUGIN_MGR_VERSION_KEY ) )
			return true;

		global $wpdb;
		$tbl = self::get_table_names();

		$a = $wpdb->query( "SHOW TABLES LIKE '{$tbl['plugins']}';" );
		$b = $wpdb->query( "SHOW TABLES LIKE '{$tbl['groups']}';" );
		$c = $wpdb->query( "SHOW TABLES LIKE '{$tbl['relation']}';" );

		return $a && $b && $c;
	}

	/**
	 * Create Tables.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @return void.
	 */
	public static function create_tables() {
		global $wpdb;
		$tbl     = self::get_table_names();
		$blog_id = get_current_blog_id();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// Plugins
		$sql = "
			CREATE TABLE {$tbl['plugins']} (
				`ID`          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`file_name`   TEXT NOT NULL,
				`user_id`     BIGINT(20) UNSIGNED NOT NULL,
				`locked`      BOOL DEFAULT 0,
				`hidden`      BOOL DEFAULT 0,

				PRIMARY KEY (`ID`),

				CONSTRAINT `fk_Plugin_Use_{$blog_id}`
					FOREIGN KEY(`user_id`)
					REFERENCES {$wpdb->users}(`ID`)
					ON UPDATE CASCADE ON DELETE CASCADE
		    );";

		dbDelta( $sql );

		// Groups
		$sql = "
			CREATE TABLE {$tbl['groups']} (
				`ID`          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`group_name`  VARCHAR(255) NOT NULL,
				`user_id`     BIGINT(20) UNSIGNED NOT NULL,
				`colour`      VARCHAR(15) NOT NULL,
				`description` TEXT NULL,
				`order`       INT UNSIGNED,
				`hidden_main` BOOL DEFAULT 0,

				PRIMARY KEY (`ID`),

				CONSTRAINT `fk_Groups_User_{$blog_id}`
					FOREIGN KEY(`user_id`)
					REFERENCES {$wpdb->users}(`ID`)
					ON UPDATE CASCADE ON DELETE CASCADE
		    );";
		dbDelta( $sql );

		// Group - Plugin
		$sql = "
			CREATE TABLE {$tbl['relation']} (
				`plugin_id` BIGINT(20) UNSIGNED NOT NULL,
				`group_id`  BIGINT(20) UNSIGNED NOT NULL,

				PRIMARY KEY (`plugin_id`, `group_id`),

				CONSTRAINT `fk_Relation_Plugin_{$blog_id}`
					FOREIGN KEY(`plugin_id`)
					REFERENCES {$tbl['plugins']}(`ID`)
					ON UPDATE CASCADE ON DELETE CASCADE,

				CONSTRAINT `fk_Relation_Group_{$blog_id}`
					FOREIGN KEY(`group_id`)
					REFERENCES {$tbl['groups']}(`ID`)
					ON UPDATE CASCADE ON DELETE CASCADE
		    );";
		dbDelta( $sql );

		update_option( SUJIN_PLUGIN_MGR_VERSION_KEY, SUJIN_PLUGIN_MGR_VERSION );
	}

	/**
	 * Update Plugins.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @return void.
	 */
	public static function update_plugins() {
		global $wpdb;

		$wp_plugins = array_keys( get_plugins() );
		$plugins    = array_map( create_function( '$value', 'return $value["file_name"];' ), self::get_plugins() );

		$diff_ext_wp   = array_diff( $wp_plugins, $plugins );
		$diff_ext_this = array_diff( $plugins, $wp_plugins );

 		foreach( $diff_ext_wp as $plugin_file ) {
 			self::add_plugin( $plugin_file );
 		}

 		foreach( $diff_ext_this as $plugin_file ) {
 			self::delete_plugin( $plugin_file );
 		}
	}

	/**
	 * Remove Duplicate Plugins.
	 *
	 * @since  0.0.1
	 * @access public static
	 *
	 * @return void.
	 */
	public static function remove_duplicate_plugins() {
		global $wpdb;
		$tbl = self::get_table_names();

		$wpdb->query("
			DELETE plugin
				FROM
					{$tbl['plugins']} plugin,
					{$tbl['plugins']} plugin2
				WHERE
					plugin.ID          > plugin2.ID AND
					plugin.file_name   = plugin2.file_name AND
					plugin.user_id     = plugin2.user_id AND
					plugin.locked      = plugin2.locked AND
					plugin.hidden      = plugin2.hidden
		");
	}

	public static function is_updated() {
		if ( ! get_option( 'plugin_groups' ) )
			return true;

		return false;
	}

	/**
	 * Upgrade.
	 *
	 * @since  0.0.1
	 * @access private

	 TODO
	 *
	 * @return array Settings array.
	 */
	public static function upgrade_from_normal_version() {
		$plugin_groups_match = get_option( 'plugin_groups_match' );
		$groups_plugin_match = get_option( 'groups_plugin_match' );
		$plugin_groups       = get_option( 'plugin_groups' );
		$plugin_locked       = get_option( 'plugin_locked' );
		$plugin_hidden       = get_option( 'plugin_hidden' );
		$option              = get_option( '_plugin-manager_' );

		if ( !$plugin_groups_match && !$groups_plugin_match && !$plugin_groups && !$plugin_locked && !$plugin_hidden && !$option ) {
			update_option( self::UPDATED_OPTION_NAME, true );
			return;
		}
		// Update Option
		if ( $option ) {
			Option::set( $option );
			delete_option( '_plugin-manager_' );
		}

		$group_info = array();

		// Create Group
		if ( $plugin_groups ) {
			foreach( $plugin_groups as $key => $groups ) {
				$setting = array(
					'group_name'  => $groups[ 'name' ],
					'colour'      => $groups[ 'color' ],
					'description' => '',
					'hidden_main' => false,
				);

				$group_info[ $key ] = self::create_group( $setting );
			}

			delete_option( 'plugin_groups' );
		}

		// Lock
		if ( $plugin_locked ) {
			foreach( $plugin_locked as $plugin_file => $bool ) {
				if ( $bool )
					self::lock_plugin( $plugin_file );
			}

			delete_option( 'plugin_locked' );
		}

		// Hide
		if ( $plugin_hidden ) {
			foreach( $plugin_hidden as $plugin_file => $bool ) {
				if ( $bool )
					self::hide_plugin( $plugin_file );
			}

			delete_option( 'plugin_hidden' );
		}

		// Relationship
		if ( $groups_plugin_match ) {
			foreach( $groups_plugin_match as $group_name => $plugins ) {
				$group_id = $group_info[ $group_name ];

				foreach( $plugins as $plugin_file_name ) {
					self::insert_plugin_into_group( $group_id, $plugin_file_name );
				}
			}

			delete_option( 'plugin_groups_match' );
			delete_option( 'groups_plugin_match' );
		}

		delete_option( 'PIGPR_VERSION_NUM' );

		return;
	}
}
