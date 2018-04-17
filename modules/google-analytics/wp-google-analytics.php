<?php
/*
    Copyright 2006 Aaron D. Campbell (email : wp_plugins@xavisys.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * Jetpack_Google_Analytics is the class that handles ALL of the plugin functionality.
 * It helps us avoid name collisions
 * http://codex.wordpress.org/Writing_a_Plugin#Avoiding_Function_Name_Collisions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( plugin_basename( 'classes/wp-google-analytics-utils.php' ) );
require_once( plugin_basename( 'classes/wp-google-analytics-options.php' ) );
require_once( plugin_basename( 'classes/wp-google-analytics-legacy.php' ) );
require_once( plugin_basename( 'classes/wp-google-analytics-universal.php' ) );

class Jetpack_Google_Analytics {

	/**
	 * @var Jetpack_Google_Analytics - Static property to hold our singleton instance
	 */
	static $instance = false;

	/**
	 * @var Static property to hold concrete analytics impl that does the work (universal or legacy)
	 */
	static $analytics = false;

	/**
	 * This is our constructor, which is private to force the use of get_instance()
	 *
	 * @return void
	 */
	private function __construct() {
		/**
		 * Allow for universal analytics (analytics.js) to be used outside of ecommerce contexts
		 *
		 * @since 6.1.0
		 *
		 * @param bool Whether universal analytics (analytics.js) should be used (true) or legacy (ga.js) should be used (false)
		 */
		$use_universal_analytics = apply_filters( 'jetpack_wga_use_universal_analytics',
			Jetpack_Google_Analytics_Options::enhanced_ecommerce_tracking_is_enabled()
		);

		if ( $use_universal_analytics ) {
			$analytics = new Jetpack_Google_Analytics_Universal();
		} else {
			$analytics = new Jetpack_Google_Analytics_Legacy();
		}

	}

	/**
	 * Function to instantiate our class and make it a singleton
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
}

/**
 * Bootstrap analytics on the init action. This allows filtering of things like
 * jetpack_wga_use_universal_analytics
 *
 * @since 6.1.0
 *
 * @return void
 */
function jetpack_wga_init() {
	global $jetpack_google_analytics;
	$jetpack_google_analytics = Jetpack_Google_Analytics::get_instance();
}

add_action( 'init', 'jetpack_wga_init' );
