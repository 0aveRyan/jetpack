<?php
/**
 * Module Name: Protect
 * Module Description: Adds brute force protection to your login page. Formerly BruteProtect
 * Sort Order: 1
 * First Introduced: 3.4
 * Requires Connection: Yes
 * Auto Activate: Yes
 */

class Jetpack_Protect_Module {

	private static $__instance = null;
	public $api_key;
	public $api_key_error;
	public $whitelist;
	public $whitelist_error;
	private $user_ip;
	private $local_host;
	private $api_endpoint;
	public $last_request;
	public $last_response_raw;
	public $last_response;

	/**
	 * Singleton implementation
	 *
	 * @return object
	 */
	public static function instance() {
		if ( ! is_a( self::$__instance, 'Jetpack_Protect_Module' ) )
			self::$__instance = new Jetpack_Protect_Module();

		return self::$__instance;
	}

	/**
	 * Registers actions
	 */
	private function __construct() {
		add_action( 'jetpack_activate_module_protect', array( $this, 'on_activation' ) );
		add_action( 'jetpack_modules_loaded', array( $this, 'modules_loaded' ) );
		add_action( 'admin_init', array( $this, 'register_assets' ) );
		add_action( 'login_head', array( $this, 'check_use_math' ) );
		add_filter( 'authenticate', array( $this, 'check_preauth' ), 10, 3 );
	}

	/**
	 * On module activation, try to get an api key
	 */
	public function on_activation() {
		$this->get_protect_key();
	}

	/**
	 * Request an api key from wordpress.com
	 *
	 * @return bool | string
	 */
	public function get_protect_key() {

		$protect_blog_id = Jetpack_Protect_Module::get_main_blog_jetpack_id();

		// if we can't find the the blog id, that means we are on multisite, and the main site never connected
		// the protect api key is linked to the main blog id - instruct the user to connect their main blog
		if ( ! $protect_blog_id ) {
			$this->api_key_error = __( 'Your main blog is not connected to WordPress.com. Please connect to get an API key.', 'jetpack' );
			return false;
		}

		$request = array(
			'jetpack_blog_id'           => $protect_blog_id,
			'bruteprotect_api_key'      => get_site_option( 'bruteprotect_api_key' ),
			'multisite'                 => '0',
		);

		// send the number of blogs on the network if we are on multisite
		if ( is_multisite() ) {
			$request[ 'multisite' ] = get_blog_count();
			if( ! $request['multisite'] ) {
				global $wpdb;
				$request['multisite'] = $wpdb->get_var( "SELECT COUNT(blog_id) as c FROM $wpdb->blogs WHERE spam = '0' AND deleted = '0' and archived = '0'" );
			}
		}

		// request the key
		Jetpack::load_xml_rpc_client();
		$xml = new Jetpack_IXR_Client( array(
			'user_id' => get_current_user_id()
		) );
		$xml->query( 'jetpack.protect.requestKey', $request );

		// hmm, can't talk to wordpress.com
		if ( $xml->isError() ) {
			$code = $xml->getErrorCode();
			$message = $xml->getErrorMessage();
			$this->api_key_error = __( 'Error connecting to WordPress.com. Code: ' . $code . ', '. $message, 'jetpack');
			return false;
		}

		$response = $xml->getResponse();

		// hmm. can't talk to the protect servers ( api.bruteprotect.com )
		if( ! isset( $response['data'] ) ) {
			$this->api_key_error = __( 'No reply from Protect servers', 'jetpack' );
			return false;
		}

		// there was an issue generating the key
		if (  empty( $response['success'] ) ) {
			$this->api_key_error = $response['data'];
			return false;
		}

		// hey, we did it!
		$active_plugins = Jetpack::get_active_plugins();

		// we only want to deactivate bruteprotect if we successfully get a key
		if ( in_array( 'bruteprotect/bruteprotect.php', $active_plugins ) ) {
			Jetpack_Client_Server::deactivate_plugin( 'bruteprotect/bruteprotect.php', 'BruteProtect' );
		}

		$key = $response['data'];
		update_site_option( 'jetpack_protect_key', $key );
		return $key;
	}

	/**
	 * Set up the Protect configuration page
	 */
	public function modules_loaded() {
		Jetpack::enable_module_configurable( __FILE__ );
		Jetpack::module_configuration_load( __FILE__, array( $this, 'configuration_load' ) );
		Jetpack::module_configuration_head( __FILE__, array( $this, 'configuration_head' ) );
		Jetpack::module_configuration_screen( __FILE__, array( $this, 'configuration_screen' ) );
	}

	public function register_assets() {
		wp_register_script( 'jetpack-protect', plugins_url( 'modules/protect/protect.js', JETPACK__PLUGIN_FILE ), array( 'jquery', 'underscore') );
		wp_register_style( 'jetpack-protect',  plugins_url( 'modules/protect/protect.css', JETPACK__PLUGIN_FILE ) );
	}

	/**
	 * Checks for loginability BEFORE authentication so that bots don't get to go around the log in form.
	 *
	 * If we are using our math fallback, authenticate via math-fallback.php
	 */
	function check_preauth( $user = 'Not Used By Protect', $username = 'Not Used By Protect', $password = 'Not Used By Protect' ) {

		$this->check_loginability( true );
		$use_math = get_site_transient( 'brute_use_math' );

		if ( $use_math == 1 && isset( $_POST[ 'log' ] ) ) :
			Jetpack_Protect_Math_Authenticate::math_authenticate();
		endif;

		return $user;
	}

	function get_ip() {
		if ( isset( $this->user_ip ) ) {
			return $this->user_ip;
		}

		$server_headers = array(
			'HTTP_CLIENT_IP',
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR'
		);

		if ( function_exists( 'filter_var' ) ) :
			foreach ( $server_headers as $key ) :
				if ( array_key_exists( $key, $_SERVER ) === true ) :
					foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) :
						$ip = trim( $ip ); // just to be safe

						//Check for IPv4 IP cast as IPv6
						if ( preg_match('/^::ffff:(\d+\.\d+\.\d+\.\d+)$/', $ip, $matches ) )
						{
							$ip = $matches[1];
						}

						//if the IP is private, return REMOTE_ADDR to help prevent spoofing
						if ( $ip == '127.0.0.1' || $ip == '::1' || $this->ip_is_private( $ip ) ) {
							$this->user_ip = $_SERVER[ 'REMOTE_ADDR' ];

							return $_SERVER[ 'REMOTE_ADDR' ];
						}

						if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) :
							$this->user_ip = $ip;

							return $this->user_ip;
						endif;
					endforeach;
				endif;
			endforeach;
		else : // PHP filter extension isn't available
			foreach ( $server_headers as $key ) :
				if ( array_key_exists( $key, $_SERVER ) === true ) :
					foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) :
						$ip = trim( $ip ); // just to be safe

						//Check for IPv4 IP cast as IPv6
						if ( preg_match('/^::ffff:(\d+\.\d+\.\d+\.\d+)$/', $ip, $matches ) )
						{
							$ip = $matches[1];
						}

						//if the IP is private, return REMOTE_ADDR to help prevent spoofing
						if ( $ip == '127.0.0.1' || $ip == '::1' || $this->ip_is_private( $ip ) ) {
							$this->user_ip = $_SERVER[ 'REMOTE_ADDR' ];

							return $_SERVER[ 'REMOTE_ADDR' ];
						}

						$this->user_ip = $ip;

						return $this->user_ip;
					endforeach;
				endif;
			endforeach;
		endif;
	}

	/**
	 * Get all IP headers so that we can process on our server...
	 *
	 * @return string
	 */
	function get_headers()
	{
		$ip_related_headers = array(
			'GD_PHP_HANDLER',
			'HTTP_AKAMAI_ORIGIN_HOP',
			'HTTP_CF_CONNECTING_IP',
			'HTTP_CLIENT_IP',
			'HTTP_FASTLY_CLIENT_IP',
			'HTTP_FORWARDED',
			'HTTP_FORWARDED_FOR',
			'HTTP_INCAP_CLIENT_IP',
			'HTTP_TRUE_CLIENT_IP',
			'HTTP_X_CLIENTIP',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_X_FORWARDED',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_IP_TRAIL',
			'HTTP_X_REAL_IP',
			'HTTP_X_VARNISH',
			'REMOTE_ADDR'

		);

		foreach( $ip_related_headers as $header) :
			if( isset( $_SERVER[ $header ] ) ) {
				$o[ $header ] = $_SERVER[ $header ];
			}
		endforeach;

		return $o;
	}

	/**
	 * Checks an IP to see if it is within a private range
	 *
	 * @return bool
	 */
	function ip_is_private( $ip )
	{
		$pri_addrs = array(
			'10.0.0.0|10.255.255.255', // single class A network
			'172.16.0.0|172.31.255.255', // 16 contiguous class B network
			'192.168.0.0|192.168.255.255', // 256 contiguous class C network
			'169.254.0.0|169.254.255.255', // Link-local address also refered to as Automatic Private IP Addressing
			'127.0.0.0|127.255.255.255' // localhost
		);

		$long_ip = ip2long( $ip );
		if ( $long_ip != -1 ) {

			foreach ( $pri_addrs AS $pri_addr ) {
				list ( $start, $end ) = explode( '|', $pri_addr );

				// IF IS PRIVATE
				if ( $long_ip >= ip2long( $start ) && $long_ip <= ip2long( $end ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Checks the status for a given IP. API results are cached as transients
	 *
	 * @param bool $preauth Wether or not we are checking prior to authorization
	 *
	 * @return bool Either returns true, fires $this->brute_kill_login, or includes a math fallback
	 */
	function check_loginability( $preauth = false ) {

		$ip                 = $this->get_ip();
		$headers            = $this->get_headers();
		$header_hash        = md5( json_encode( $headers ) );
		// TODO: Sam, this transient name is a bit too long. they are limited to 45 chars
		$transient_name     = 'brute_loginable_' . $header_hash;
		$transient_value    = get_site_transient( $transient_name );

		// TODO: Sam, note the new whitelist schema
		//Never block login from whitelisted IPs
		if ( defined( 'JETPACK_IP_ADDRESS_OK' ) && 'JETPACK_IP_ADDRESS_OK' == $ip ) { // found an exact match in wp-config
			return true;
		}

		$whitelist = get_site_option( 'jetpack_protect_whitelist', array() );
		$ip_long = inet_pton( $ip );

		if ( ! empty( $whitelist ) ) :
			foreach ( $whitelist as $item ) :

				if ( ! $item->range && isset( $item->ip_address ) && $item->ip_address == $ip ) { // exact match
					return true;
				}

				if ( $item->range && isset( $item->range_low ) && isset( $item->range_high ) ) {
					$ip_low     = inet_pton( $item->range_low );
					$ip_high    = inet_pton( $item->range_high );
					if ( strcmp( $ip_long, $ip_low ) >= 0 && strcmp( $ip_long, $ip_high ) <= 0 ) { //IP is within range
						return true;
					}
				}

			endforeach;
		endif;


		//Check out our transients
		if ( isset( $transient_value ) && $transient_value[ 'status' ] == 'ok' ) {
			return true;
		}

		if ( isset( $transient_value ) && $transient_value[ 'status' ] == 'blocked' ) {
			//there is a current block-- prevent login
			$this->kill_login();
		}

		//If we've reached this point, this means that the IP isn't cached.
		//Now we check with the Protect API to see if we should allow login
		$response = $this->protect_call( $action = 'check_ip' );

		if ( isset( $response[ 'math' ] ) && !function_exists( 'brute_math_authenticate' ) ) {
			include_once dirname( __FILE__ ) . '/protect/math-fallback.php';
		}

		if ( $response[ 'status' ] == 'blocked' ) {
			$this->kill_login( $response[ 'blocked_attempts' ] );
		}

		return true;
	}

	function kill_login() {
		$ip = $this->get_ip();
		do_action( 'brute_kill_login', $ip );
		wp_die(
			'Your IP (' . $ip . ') has been flagged for potential security violations.  Please try again in a little while...',
			'Login Blocked by BruteProtect',
			array( 'response' => 403 )
		);
	}


	public function check_use_math() {
		$use_math = get_site_transient( 'brute_use_math' );
		if ( $use_math ) {
			include_once dirname( __FILE__ ) . '/protect/math-fallback.php';
			new Jetpack_Protect_Math_Authenticate;
		}
	}

	/**
	 * Get key or delete key
	 */
	public function configuration_load() {

		if ( isset( $_POST['action'] ) && $_POST['action'] == 'save_protect_whitelist' && wp_verify_nonce( $_POST['_wpnonce'], 'jetpack-protect' ) ) {
			$this->save_whitelist();
		}

		// TODO: REMOVE THIS, IT'S FOR BETA TESTING ONLY
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'remove_protect_key' && wp_verify_nonce( $_POST['_wpnonce'], 'jetpack-protect' ) ) {
			delete_site_option( 'jetpack_protect_key' );
		}

		// TODO: REMOVE THIS, IT'S FOR BETA TESTING ONLY
		if ( isset( $_POST['action'] ) && $_POST['action'] == 'add_whitelist_placeholder_data' && wp_verify_nonce( $_POST['_wpnonce'], 'jetpack-protect' ) ) {
			$this->add_whitelist_placeholder_data();
		}

		if ( isset( $_POST['action'] ) && $_POST['action'] == 'get_protect_key' && wp_verify_nonce( $_POST['_wpnonce'], 'jetpack-protect' ) ) {
			$result = $this->get_protect_key();
			// only redirect on success
			// if it fails we need access to $this->api_key_error
			if( $result ) {
				wp_safe_redirect( Jetpack::module_configuration_url( 'protect' ) );
			}
		}

		$this->api_key     = get_site_option( 'jetpack_protect_key', false );
		$this->whitelist   = get_site_option( 'jetpack_protect_whitelist', array() );
	}

	public function configuration_head() {
		wp_enqueue_script( 'jetpack-protect' );
		wp_enqueue_style( 'jetpack-protect' );
	}

	/**
	 * Prints the configuration screen
	 */
	public function configuration_screen() {
		require_once dirname( __FILE__ ) . '/protect/config-ui.php';
	}

	/**
	 * Get jetpack blog id, or the jetpack blog id of the main blog in the network
	 *
	 * @return int
	 */
	public function get_main_blog_jetpack_id() {
		$id = Jetpack::get_option( 'id' );
		if ( is_multisite() && get_current_blog_id() != 1 ) {
			switch_to_blog( 1 );
			$id = Jetpack::get_option( 'id', false );
			restore_current_blog();
		}
		return $id;
	}

	public function save_whitelist() {

		global $current_user;
		$whitelist = is_array( $_POST['whitelist'] ) ? $_POST['whitelist'] : array();
		$new_items = array();

		// validate each item
		foreach( $whitelist as $item ) {

			if ( ! isset( $item['range'] ) ) {
				$this->whitelist_error = true;
				break;
			}

			if ( ! in_array( $item['range'], array( '1', '0' ) ) ) {
				$this->whitelist_error = true;
				break;
			}

			$range              = $item['range'];
			$new_item           = new stdClass();
			$new_item->range    = (bool) $range;
			$new_item->global   = false;
			$new_item->user_id  = $current_user->ID;

			if ( $range ) {

				if ( ! isset( $item['range_low'] ) || ! isset( $item['range_high'] ) ) {
					$this->whitelist_error = true;
					break;
				}

				if ( ! inet_pton( $item['range_low'] ) || ! inet_pton( $item['range_high'] ) ) {
					$this->whitelist_error = true;
					break;
				}

				$new_item->range_low    = $item['range_low'];
				$new_item->range_high   = $item['range_high'];

			} else {

				if ( ! isset( $item['ip_address'] ) ) {
					$this->whitelist_error = true;
					break;
				}

				if ( ! inet_pton( $item['ip_address'] ) ) {
					$this->whitelist_error = true;
					break;
				}

				$new_item->ip_address = $item['ip_address'];
			}

			$new_items[] = $new_item;

		} // end item loop

		if ( ! empty( $this->whitelist_error ) ) {
			return false;
		}

		// merge new items with un-editable items
		$this->whitelist                = get_site_option( 'jetpack_protect_whitelist', array() );
		$current_user_global_whitelist  = wp_list_filter( $this->whitelist, array( 'user_id' => $current_user->ID, 'global'=> true) );
		$other_user_whtielist           = wp_list_filter( $this->whitelist, array( 'user_id' => $current_user->ID ), 'NOT' );
		$new_whitelist                  = array_merge( $new_items, $current_user_global_whitelist, $other_user_whtielist );
		
		update_site_option( 'jetpack_protect_whitelist', $new_whitelist );
		return true;
	}

	/**
	 * Calls over to the api using wp_remote_post
	 *
	 * @param string $action 'check_ip', 'check_key', or 'failed_attempt'
	 * @param array  $request Any custom data to post to the api
	 *
	 * @return array
	 */
	function protect_call( $action = 'check_ip', $request = array() )
	{
		global $wp_version, $wpdb, $current_user;

		$api_key = get_site_option( 'jetpack_protect_key' );

		$user_agent = "WordPress/{$wp_version} | ";

		$request[ 'action' ] = $action;
		$request[ 'ip' ] = $this->get_ip();
		$request[ 'host' ] = $this->get_local_host();
		$request[ 'headers' ] = json_encode( $this->get_headers() );
		$request[ 'wordpress_version' ] = strval( $wp_version );
		$request[ 'api_key' ] = $api_key;
		$request[ 'multisite' ] = "0";

		if ( is_multisite() ) {
			$request[ 'multisite' ] = get_blog_count();
			if ( ! $request[ 'multisite' ] ) {
				$request[ 'multisite' ] = $wpdb->get_var( "SELECT COUNT(blog_id) as c FROM $wpdb->blogs WHERE spam = '0' AND deleted = '0' and archived = '0'" );
			}
		}

		$args = array(
			'body'        => $request,
			'user-agent'  => $user_agent,
			'httpversion' => '1.0',
			'timeout'     => 15
		);

		$response_json              = wp_remote_post( $this->get_api_host(), $args );
		$this->last_response_raw    = $response_json;
		$headers                    = $this->get_headers();
		$header_hash                = md5( json_encode( $headers ) );
		// TODO: Sam, this transient name is a bit too long. they are limited to 45 chars
		$transient_name             = 'brute_loginable_' . $header_hash;
		delete_site_transient( $transient_name );

		if ( is_array( $response_json ) ) {
			$response = json_decode( $response_json[ 'body' ], true );
		}

		if ( isset( $response[ 'status' ] ) && ! isset( $response[ 'error' ] ) ) :
			$response[ 'expire' ] = time() + $response[ 'seconds_remaining' ];
			set_site_transient( $transient_name, $response, $response[ 'seconds_remaining' ] );
			delete_site_transient( 'brute_use_math' );
		else : //no response from the API host?  Let's use math!
			set_site_transient( 'brute_use_math', 1, 600 );
			$response[ 'status' ] = 'ok';
			$response[ 'math' ] = true;
		endif;

		if ( isset( $response[ 'error' ] ) ) :
			update_site_option( 'jetpack_protect_error', $response[ 'error' ] );
		else :
			delete_site_option( 'jetpack_protect_error' );
		endif;

		return $response;
	}

	/**
	 * Checks if server can use https, and returns api endpoint
	 *
	 * @return string URL of api with either http or https protocol
	 */
	function get_api_host()
	{
		if ( isset( $this->api_endpoint ) ) {
			return $this->api_endpoint;
		}

		//Some servers can't access https-- we'll check once a day to see if we can.
		$use_https  = get_site_transient( 'jetpack_protect_https' );
		$api_url    = get_site_option( 'jetpack_protect_api_host', 'api.bruteprotect.com/' );
		if ( $use_https == 'yes' ) {
			$this->api_endpoint = 'https://' . $api_url;
		} else {
			$this->api_endpoint = 'http://' . $api_url;
		}

		if ( ! $use_https ) {
			$test = wp_remote_get( 'https://api.bruteprotect.com/https_check.php' );
			$use_https = 'no';
			if ( ! is_wp_error( $test ) && $test[ 'body' ] == 'ok' ) {
				$use_https = 'yes';
			}
			set_site_transient( 'jetpack_protect_https', $use_https, 86400 );
		}

		return $this->api_endpoint;
	}

	function get_local_host() {
		if ( isset( $this->local_host ) ) {
			return $this->local_host;
		}

		$uri = 'http://' . strtolower( $_SERVER[ 'HTTP_HOST' ] );

		if ( is_multisite() ) {
			$uri = network_home_url();
		}

		$uridata = parse_url( $uri );

		$domain = $uridata[ 'host' ];

		//if we still don't have it, get the site_url
		if ( !$domain ) {
			$uri = get_site_url( 1 );
			$uridata = parse_url( $uri );
			$domain = $uridata[ 'host' ];
		}

		$this->local_host = $domain;

		return $this->local_host;
	}

	// TODO: REMOVE THIS, BETA TESTING ONLY
	public function add_whitelist_placeholder_data() {
		$ip1_1 = new stdClass();
		$ip1_1->user_id = 1;
		$ip1_1->global = false;
		$ip1_1->range = false;
		$ip1_1->ip_address = '22.22.22.22';
		$ip1_2 = new stdClass();
		$ip1_2->user_id = 1;
		$ip1_2->global = false;
		$ip1_2->range = false;
		$ip1_2->ip_address = 'FE80:0000:0000:0000:0202:B3FF:FE1E:8329';
		$ip1_3 = new stdClass();
		$ip1_3->user_id = 1;
		$ip1_3->global = true;
		$ip1_3->range = false;
		$ip1_3->ip_address = 'FE80::0202:B3FF:FE1E:8329';
		$ip1_4 = new stdClass();
		$ip1_4->user_id = 1;
		$ip1_4->global = false;
		$ip1_4->range = true;
		$ip1_4->range_low = '44.44.10.44';
		$ip1_4->range_high = '44.44.100.44';
		$ip1_5 = new stdClass();
		$ip1_5->user_id = 1;
		$ip1_5->global = false;
		$ip1_5->range = true;
		$ip1_5->range_low = '2001:db8::';
		$ip1_5->range_high = '2001:db8:0000:0000:0000:0000:0000:0003';
		$ip1_6 = new stdClass();
		$ip1_6->user_id = 1;
		$ip1_6->global = true;
		$ip1_6->range = true;
		$ip1_6->range_low = '200.145.20.12';
		$ip1_6->range_high = '200.145.50.12';
		$ip2_1 = new stdClass();
		$ip2_1->user_id = 2;
		$ip2_1->global = true;
		$ip2_1->range = true;
		$ip2_1->range_low = '62.33.1.14';
		$ip2_1->range_high = '62.33.50.14';
		$ip2_2 = new stdClass();
		$ip2_2->user_id = 2;
		$ip2_2->global = true;
		$ip2_2->range = true;
		$ip2_2->range_low = '2001:db8::';
		$ip2_2->range_high = '2001:db8:0000:0000:0000:0000:0000:0007';
		$ip3_1 = new stdClass();
		$ip3_1->user_id = 3;
		$ip3_1->global = false;
		$ip3_1->range = false;
		$ip3_1->ip_address = '202.1.19.4';
		$ip3_2 = new stdClass();
		$ip3_2->user_id = 3;
		$ip3_2->global = false;
		$ip3_2->range = false;
		$ip3_2->ip_address = '2001:db8:0000:0000:0000:0000:0000:3fff';
		$whitelist = array( $ip1_1, $ip1_2, $ip1_3, $ip1_4, $ip1_5, $ip1_6, $ip2_1, $ip2_2, $ip3_1, $ip3_2 );
		update_site_option( 'jetpack_protect_whitelist', $whitelist );
	}

}

Jetpack_Protect_Module::instance();