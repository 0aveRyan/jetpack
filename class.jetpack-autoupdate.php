<?php

// Update any plugins that have been flagged for automatic updates
class Jetpack_Autoupdate {

	private static $instance = null;
	protected $updates_allowed;
	public $is_updating = false;
	public $autoupdate_expected = array(
		'plugin' => array(),
	);
	public $autoupdate_results;

	static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Jetpack_Autoupdate;
		}
		return self::$instance;
	}

	private function __construct() {
		$this->updates_allowed = Jetpack::is_module_active( 'manage' );

		if ( $this->updates_allowed ) {
			add_filter( 'auto_update_plugin',  array( $this, 'autoupdate_plugin' ), 10, 2 );
			add_filter( 'auto_update_theme',   array( $this, 'autoupdate_theme' ), 10, 2 );
			add_filter( 'auto_update_core',    array( $this, 'autoupdate_core' ), 10, 2 );
			add_action( 'automatic_updates_complete', array( $this, 'automatic_updates_complete' ), 10, 1 );
			add_action( 'shutdown', array( $this, 'log_results' ) );
		}

		/**
		 * Anytime WordPress saves update data, we'll want to update our Jetpack option as well.
		 */
		if ( is_main_site() ) {
			add_action( 'set_site_transient_update_plugins', array( $this, 'save_update_data' ) );
			add_action( 'set_site_transient_update_themes', array( $this, 'save_update_data' ) );
			add_action( 'set_site_transient_update_core', array( $this, 'save_update_data' ) );
		}

	}

	function autoupdate_plugin( $update, $item ) {
		$autoupdate_plugin_list = Jetpack_Options::get_option( 'autoupdate_plugins', array() );
		if ( in_array( $item->plugin, $autoupdate_plugin_list ) ) {
			$this->is_updating = true;
			// this will let our loggin know that this plugin was attempted
			// the update could fail for various reasons and the item may be absent from the results
			$this->autoupdate_expected['plugin'][] = $item;
 			return true;
		}

		return $update;
	}

	function autoupdate_theme( $update, $item ) {
		$autoupdate_theme_list = Jetpack_Options::get_option( 'autoupdate_themes', array() );
		if ( in_array( $item->theme , $autoupdate_theme_list) ) {
			return true;
		}
		return $update;
	}

	function autoupdate_core( $update, $item ) {
		$autoupdate_core = Jetpack_Options::get_option( 'autoupdate_core', false );
		if ( $autoupdate_core ) {
			return $autoupdate_core;
		}
		return $update;
	}

	/**
	 * Calculates available updates and stores them to a Jetpack Option
	 * Update data is saved in the following schema:
	 *
	 * array (
	 *      'plugins' => (int) number of plugin updates available,
	 *      'themes' => (int) number of theme updates available,
	 *      'wordpress' => (int) number of wordpress core updates available,
	 *      'translations' => (int) number of translation updates available,
	 *      'total' => (int) total of all available updates,
	 *      'wp_version' => (string) the current version of WordPress that is running,
	 *      'wp_update_version' => (string) the latest available version of WordPress, only present if a WordPress update is needed
	 *      'site_is_version_controlled' => (bool) is the site under version control
	 * )
	 */
	function save_update_data() {
		global $wp_version;

		$update_data = wp_get_update_data();

		// stores the individual update counts as well as the total count
		if ( isset( $update_data['counts'] ) ) {
			$updates = $update_data['counts'];
		}

		// stores the current version of WordPress
		$updates['wp_version'] = $wp_version;

		// if we need to update WordPress core, let's find the latest version number
		if ( ! empty( $updates['wordpress'] ) ) {
			$cur = get_preferred_from_update_core();
			if ( isset( $cur->response ) && 'upgrade' === $cur->response ) {
				$updates['wp_update_version'] = $cur->current;
			}
		}

		$updates['site_is_version_controlled'] = (bool) $this->is_version_controlled();
		Jetpack_Options::update_option( 'updates', $updates );
	}

	/**
	 * Finds out if a site is using a version control system.
	 * We'll store that information as a transient with a 24 expiration.
	 * We only need to check once per day.
	 *
	 * @return string ( '1' | '0' )
	 */
	function is_version_controlled() {
		$is_version_controlled = get_transient( 'jetpack_site_is_vcs' );

		if ( false === $is_version_controlled ) {
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			$updater = new WP_Automatic_Updater();
			$is_version_controlled  = strval( $updater->is_vcs_checkout( $context = ABSPATH ) );
			// transients should not be empty
			if ( empty( $is_version_controlled ) ) {
				$is_version_controlled = '0';
			}
			set_transient( 'jetpack_site_is_vcs', $is_version_controlled, DAY_IN_SECONDS );
		}

		return $is_version_controlled;
	}

	function automatic_updates_complete( $results ) {
		$this->autoupdate_results = $results;
	}

	private function get_successful_updates( $results = 'plugin' ) {
		$successful_updates = array();

		if ( ! isset( $this->autoupdate_results[ $results ] ) ) {
			return $successful_updates;
		}

		foreach( $this->autoupdate_results[ $results ] as $result ) {
			if ( $result->result ) {
				switch( $result ) {
					case 'theme':
						$successful_updates[] = $result->item->theme;
						break;
					default:
						$successful_updates[] = $result->item->slug;
				}
			}
		}

		return $successful_updates;
	}

	function log_results() {

		// if we've run an update, lets compare the expected results to the actual results, and log our findings
		if( $this->is_updating === true  ) {

			$plugins_updated = 0;
			$plugins_failed  = 0;
			$plugin_results  = $this->get_successful_updates();

			foreach( $this->autoupdate_expected['plugin'] as $plugin ) {
				if ( in_array( $plugin->slug, $plugin_results ) ) {
					$plugins_updated++;
				} else {
					$plugins_failed++;
				}
			}

			$stats = array();

			if ( $plugins_updated ) {
				$stats['x_jetpack_autoupdates/plugin-success'] = $plugins_updated;
			}

			if ( $plugins_failed ) {
				$stats['x_jetpack_autoupdates/plugin-fail'] = $plugins_failed;
			}


			if( ! empty( $stats ) ) {
				$stats['v'] = 'wpcom-no-pv';
				Jetpack::do_server_side_stat( $stats );
			}

		}
	}

}
Jetpack_Autoupdate::init();