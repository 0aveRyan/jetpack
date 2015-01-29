<?php

class Jetpack_JSON_API_Protect_Whitelist extends Jetpack_JSON_API_Endpoint {
	protected $needed_capabilities = 'manage_options';

	protected function validate_input( $object ) {
		if( $this->method == 'GET' ) {
			return true;
		}
		$args = $this->input();
		if ( ! isset( $args['whitelist'] ) || ! isset( $args['global'] ) ) {
			return new WP_Error( 'invalid_arguments', __( 'Invalid arguments', 'jetpack' ));
		}

		$result = $this->save_whitelist( $args['whitelist'], $args['global'] );

		if( ! $result ) {
			return new WP_Error( 'invalid_ip', __( 'One or more of your IP Addresses are invalid.', 'jetpack' ));
		}
		return true;
	}

	public function result() {
		$response = array(
			'whitelist' => $this->format_whitelist(),
		);
		return $response;
	}

	public function format_whitelist() {
		$whitelist = get_site_option( 'jetpack_protect_whitelist', array() );
		global $current_user;
		$current_user_whitelist = wp_list_filter( $whitelist, array( 'user_id' => $current_user->ID, 'global'=>false ) );
		$current_user_global_whitelist = wp_list_filter( $whitelist, array( 'user_id' => $current_user->ID, 'global'=> true) );
		$other_user_whtielist = wp_list_filter( $whitelist, array( 'user_id' => $current_user->ID ), 'NOT' );
		$formatted = array(
			'local'=>'',
			'global'=>'',
			'other_user'=>'',
		);
		foreach( $current_user_whitelist as $item ) {
			if ( $item->range ) {
				$formatted['local'] .= $item->range_low . ' &ndash; ' . $item->range_high . PHP_EOL;
			} else {
				$formatted['local'] .= $item->ip_address . PHP_EOL;
			}
		}

		foreach( $current_user_global_whitelist as $item ) {
			if ( $item->range ) {
				$formatted['global'] .= $item->range_low . ' &ndash; ' . $item->range_high . PHP_EOL;
			} else {
				$formatted['global'] .= $item->ip_address . PHP_EOL;
			}
		}

		foreach( $other_user_whtielist as $item ) {
			if ( $item->range ) {
				$formatted['other_user'] .= $item->range_low . ' &ndash; ' . $item->range_high . PHP_EOL;
			} else {
				$formatted['other_user'] .= $item->ip_address . PHP_EOL;
			}
		}

		return $formatted;
	}

	public function save_whitelist( $whitelist, $global ) {
		global $current_user;
		$whitelist_error = false;
		$whitelist = is_array( $whitelist ) ? $whitelist : array();
		$new_items = array();

		// validate each item
		foreach( $whitelist as $item ) {

			if ( ! isset( $item['range'] ) ) {
				$whitelist_error = true;
				break;
			}

			if ( ! in_array( $item['range'], array( '1', '0' ) ) ) {
				$whitelist_error = true;
				break;
			}

			$range              = $item['range'];
			$new_item           = new stdClass();
			$new_item->range    = (bool) $range;
			$new_item->global   = $global;
			$new_item->user_id  = $current_user->ID;

			if ( $range ) {

				if ( ! isset( $item['range_low'] ) || ! isset( $item['range_high'] ) ) {
					$whitelist_error = true;
					break;
				}

				if ( ! inet_pton( $item['range_low'] ) || ! inet_pton( $item['range_high'] ) ) {
					$whitelist_error = true;
					break;
				}

				$new_item->range_low    = $item['range_low'];
				$new_item->range_high   = $item['range_high'];

			} else {

				if ( ! isset( $item['ip_address'] ) ) {
					$whitelist_error = true;
					break;
				}

				if ( ! inet_pton( $item['ip_address'] ) ) {
					$whitelist_error = true;
					break;
				}

				$new_item->ip_address = $item['ip_address'];
			}

			$new_items[] = $new_item;

		} // end item loop

		if ( ! empty( $whitelist_error ) ) {
			return false;
		}

		// merge new items with un-editable items
		$existing_whitelist     = get_site_option( 'jetpack_protect_whitelist', array() );
		$current_user_whitelist = wp_list_filter( $existing_whitelist, array( 'user_id' => $current_user->ID, 'global'=>  ! $global) );
		$other_user_whtielist   = wp_list_filter( $existing_whitelist, array( 'user_id' => $current_user->ID ), 'NOT' );
		$new_whitelist          = array_merge( $new_items, $current_user_whitelist, $other_user_whtielist );

		update_site_option( 'jetpack_protect_whitelist', $new_whitelist );
		return true;
	}
}
