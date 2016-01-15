<?php
/**
 * Plugin Name: Display Recent WordPress Posts Widget
 * Description: Displays recent posts from a WordPress.com or Jetpack-enabled self-hosted WordPress site.
 * Version: 1.0
 * Author: Brad Angelcyk, Kathryn Presner, Justin Shreve, Carolyn Sonnek
 * Author URI: http://automattic.com
 * License: GPL2
 */
add_action( 'widgets_init', 'jetpack_display_posts_widget' );
function jetpack_display_posts_widget() {
	register_widget( 'Jetpack_Display_Posts_Widget' );
}


/**
 * Cron tasks
 */

/**
 * Add a 10 minute interval to cron intervals.
 */
add_filter( 'cron_schedules', 'jetpack_display_posts_widget_cron_intervals' );
function jetpack_display_posts_widget_cron_intervals() {
	$interval['minutes_10'] = array( 'interval' => 10 * MINUTE_IN_SECONDS, 'display' => 'Every 10 minutes' );

	return $interval;
}

/**
 * Execute the cron task
 */
add_action( 'display_posts_widget_cron_update', 'display_posts_update_cron_action' );
function display_posts_update_cron_action() {
	$widget = new Jetpack_Display_Posts_Widget();
	$widget->cron_task();
}

/**
 * End of Cron tasks
 */
/*
 * Display a list of recent posts from a WordPress.com or Jetpack-enabled blog.
 */

class Jetpack_Display_Posts_Widget extends WP_Widget {

	/**
	 * @var string Remote service API URL prefix.
	 */
	public $service_url = 'https://public-api.wordpress.com/rest/v1.1/';

	/**
	 * @var string Widget options key prefix.
	 */
	public $widget_options_key_prefix = 'display_posts_site_data_';

	public function __construct() {
		parent::__construct(
		// internal id
			'jetpack_display_posts_widget',
			/** This filter is documented in modules/widgets/facebook-likebox.php */
			apply_filters( 'jetpack_widget_name', __( 'Display WordPress Posts', 'jetpack' ) ),
			array(
				'description' => __( 'Displays a list of recent posts from another WordPress.com or Jetpack-enabled blog.', 'jetpack' ),
			)
		);

		/**
		 * Check the status of the cron task.
		 */
		self::check_for_cron();
	}

	/**
	 * Expiring transients have a name length maximum of 45 characters,
	 * so this function returns an abbreviated MD5 hash to use instead of
	 * the full URI.
	 *
	 * @param string $site Site to get the hash for.
	 *
	 * @return string
	 */
	public function get_site_hash( $site ) {
		return substr( md5( $site ), 0, 21 );
	}

	/**
	 * Fetch site information
	 *
	 * @param string $site Site to fetch the information for.
	 *
	 * @return mixed|WP_Error
	 */
	public function get_site_info( $site ) {
		$site_hash       = $this->get_site_hash( $site );
		$data_from_cache = get_transient( 'display_posts_site_info_' . $site_hash );
		if ( false === $data_from_cache ) {
			$raw_data = $this->fetch_site_info( $site );
			$response = $this->parse_site_info_response( $raw_data );

			set_transient( 'display_posts_site_info_' . $site_hash, $response, 10 * MINUTE_IN_SECONDS );
		}
		else {
			$response = $data_from_cache;
		}

		return $response;
	}


	/**
	 * Fetch a remote service endpoint and parse it.
	 *
	 * Timeout is set to 15 seconds right now, because sometimes the WordPress API
	 * takes more than 5 seconds to fully respond.
	 *
	 * @param string $endpoint Parametrized endpoint to call.
	 *
	 * @param int    $timeout  How much time to wait for the API to respond before failing.
	 *
	 * @return array|WP_Error
	 */
	public function fetch_service_endpoint( $endpoint, $timeout = 15 ) {
		$raw_data = wp_remote_get( $this->service_url . ltrim( $endpoint, '/' ), array( 'timeout' => $timeout ) );

		$parsed_data = $this->parse_service_response( $raw_data );

		return $parsed_data;
	}


	/**
	 * Parse data from service response.
	 * Do basic error handling for general service and data errors
	 *
	 * @param array $service_response Response from the service.
	 *
	 * @return array|WP_Error
	 */
	public function parse_service_response( $service_response ) {
		/**
		 * If there is an error, we add the error message to the parsed response
		 */
		if ( is_wp_error( $service_response ) ) {
			return new WP_Error(
				'general_error',
				__( 'An error occurred while fetching data from remote.', 'jetpack' ),
				$service_response->get_error_messages()
			);
		}

		/**
		 * Validate HTTP response code.
		 */
		if ( 200 !== wp_remote_retrieve_response_code( $service_response ) ) {
			return new WP_Error(
				'http_error',
				__( 'An error occurred while fetching data from remote.', 'jetpack' ),
				wp_remote_retrieve_response_message( $service_response )
			);
		}

		/**
		 * No body has been set in the response. This should be pretty bad.
		 */
		if ( ! isset( $service_response['body'] ) ) {
			return new WP_Error(
				'no_body',
				__( 'Invalid data returned by remote.', 'jetpack' ),
				'No body in response.'
			);
		}

		/**
		 * Parse the JSON response from the API. Convert to associative array.
		 */
		$parsed_data = json_decode( $service_response['body'] );

		/**
		 * If there is a problem with parsing the posts return an empty array.
		 */
		if ( is_null( $parsed_data ) ) {
			return new WP_Error(
				'no_body',
				__( 'Invalid data returned by remote.', 'jetpack' ),
				'Invalid JSON from remote.'
			);
		}

		/**
		 * Check for errors in the parsed body.
		 */
		if ( isset( $parsed_data->error ) ) {
			return new WP_Error(
				'remote_error',
				__( 'We cannot display information for this blog.', 'jetpack' ),
				$parsed_data->error
			);
		}


		/**
		 * No errors found, return parsed data.
		 */
		return $parsed_data;
	}

	/**
	 * Fetch site information and posts list for a site.
	 *
	 * @param string $site          Site to fetch the data for.
	 * @param array  $original_data Optional original data to updated.
	 *
	 * @return array Updated or new data.
	 */
	public function fetch_blog_data( $site, $original_data = array() ) {

		/**
		 * If no optional data is supplied, initialize a new structure
		 */
		if ( ! empty( $original_data ) ) {
			$widget_data = $original_data;
		}
		else {
			$widget_data = array(
				'site_info' => array(
					'last_check'  => null,
					'last_update' => null,
					'error'       => null,
					'data'        => array(),
				),
				'posts'     => array(
					'last_check'  => null,
					'last_update' => null,
					'error'       => null,
					'data'        => array(),
				)
			);
		}

		/**
		 * Update check time and fetch site information.
		 */
		$widget_data['site_info']['last_check'] = time();

		$site_info_raw_data    = $this->fetch_site_info( $site );
		$site_info_parsed_data = $this->parse_site_info_response( $site_info_raw_data );


		/**
		 * If there is an error with the fetched site info, save the error and update the checked time.
		 */
		if ( is_wp_error( $site_info_parsed_data ) ) {
			$widget_data['site_info']['error'] = $site_info_parsed_data;

			return $widget_data;
		}
		/**
		 * If data is fetched successfully, update the data and set the proper time.
		 *
		 * Data is only updated if we have valid results. This is done this way so we can show
		 * something if external service is down.
		 *
		 */
		else {
			$widget_data['site_info']['last_update'] = time();
			$widget_data['site_info']['data']        = $site_info_parsed_data;
			$widget_data['site_info']['error']       = null;
		}


		/**
		 * Update check time and fetch posts list.
		 */
		$widget_data['posts']['last_check'] = time();

		$site_posts_raw_data    = $this->fetch_posts_for_site( $site_info_parsed_data->ID );
		$site_posts_parsed_data = $this->parse_posts_response( $site_posts_raw_data );


		/**
		 * If there is an error with the fetched posts, save the error and update the checked time.
		 */
		if ( is_wp_error( $site_posts_parsed_data ) ) {
			$widget_data['posts']['error'] = $site_posts_parsed_data;

			return $widget_data;
		}
		/**
		 * If data is fetched successfully, update the data and set the proper time.
		 *
		 * Data is only updated if we have valid results. This is done this way so we can show
		 * something if external service is down.
		 *
		 */
		else {
			$widget_data['posts']['last_update'] = time();
			$widget_data['posts']['data']        = $site_posts_parsed_data;
			$widget_data['posts']['error']       = null;
		}

		return $widget_data;
	}


	/**
	 * Gets blog data from the cache.
	 *
	 * @param string $site
	 *
	 * @return array|WP_Error
	 */
	public function get_blog_data( $site ) {
		// load from cache, if nothing return an error
		$site_hash = $this->get_site_hash( $site );

		$cached_data = $this->wp_get_option( $this->widget_options_key_prefix . $site_hash );

		/**
		 * If the cache is empty, return an empty_cache error.
		 */
		if ( false === $cached_data ) {
			return new WP_Error(
				'empty_cache',
				__( 'Information about this blog is being currently retrieved.', 'jetpack' )
			);
		}

		return $cached_data;

	}

	/**
	 * Fetch site information from the WordPress public API
	 *
	 * @param string $site URL of the site to fetch the information for.
	 *
	 * @return array|WP_Error
	 */
	public function fetch_site_info( $site ) {

		$response = $this->fetch_service_endpoint( sprintf( '/sites/%s', urlencode( $site ) ) );

		return $response;
	}

	/**
	 * Fetch list of posts from the WordPress public API.
	 *
	 * @param int $site_id The site to fetch the posts for.
	 *
	 * @return array|WP_Error
	 */
	public function fetch_posts_for_site( $site_id ) {

		$response = $this->fetch_service_endpoint(
			sprintf(
				'/sites/%1$d/posts/%2$s',
				$site_id,
				/**
				 * Filters the parameters used to fetch for posts in the Display Posts Widget.
				 *
				 * @see    https://developer.wordpress.com/docs/api/1.1/get/sites/%24site/posts/
				 *
				 * @module widgets
				 *
				 * @since  3.6.0
				 *
				 * @param string $args Extra parameters to filter posts returned from the WordPress.com REST API.
				 */
				apply_filters( 'jetpack_display_posts_widget_posts_params', '' )
			)
		);

		return $response;
	}

	/**
	 * Parse external API response from the posts list request and handle errors if any occur.
	 *
	 * @param array|WP_Error $service_response The raw response to be parsed.
	 *
	 * @return array|WP_Error
	 */
	public function parse_posts_response( $service_response ) {

		/**
		 * If the service returned an error, we pass it on.
		 */
		if ( is_wp_error( $service_response ) ) {
			return $service_response;
		}

		/**
		 * Check if the service returned proper posts array.
		 */
		if ( ! is_array( $service_response->posts ) ) {
			return new WP_Error(
				'no_posts',
				__( 'No posts data returned by remote.', 'jetpack' ),
				'No posts information set in the returned data.'
			);
		}

		/**
		 * Format the posts to preserve storage space.
		 */

		return $this->format_posts_for_storage( $service_response );
	}


	/**
	 * Parse external API response from the site info call and handle errors if they occur.
	 *
	 * @param array|WP_Error $service_response The raw response to be parsed.
	 *
	 * @return array|WP_Error
	 */
	public function parse_site_info_response( $service_response ) {

		/**
		 * If the service returned an error, we pass it on.
		 */
		if ( is_wp_error( $service_response ) ) {
			return $service_response;
		}

		/**
		 * Check if the service returned proper site information.
		 */
		if ( ! isset( $service_response->ID ) ) {
			return new WP_Error(
				'no_site_info',
				__( 'Invalid site information returned from remote.', 'jetpack' ),
				'No site ID present in the response.'
			);
		}

		return $service_response;
	}

	/**
	 * Format the posts for better storage. Drop all the data that is not used.
	 *
	 * @param array $parsed_data Array of posts returned by the APIs.
	 *
	 * @return array Formatted posts or an empty array if no posts were found.
	 */
	public function format_posts_for_storage( $parsed_data ) {

		$formatted_posts = array();

		/**
		 * Loop through all the posts and format them appropriately.
		 */
		foreach ( $parsed_data->posts as $single_post ) {

			$prepared_post = array(
				'title'          => $single_post->title ? $single_post->title : '',
				'excerpt'        => $single_post->excerpt ? $single_post->excerpt : '',
				'featured_image' => $single_post->featured_image ? $single_post->featured_image : '',
				'url'            => $single_post->URL,
			);

			/**
			 * Append the formatted post to the results.
			 */
			$formatted_posts[] = $prepared_post;
		}

		return $formatted_posts;
	}


	/**
	 * Checks if the cron task is enabled or not. If it is not - enable it.
	 */
	public static function check_for_cron() {
		if ( ! wp_next_scheduled( 'display_posts_widget_cron_update' ) ) {
			wp_schedule_event( time(), 'minutes_10', 'display_posts_widget_cron_update' );
		}
	}

	/**
	 * Main cron code. Updates all instances of the widget.
	 *
	 * @return bool
	 */
	public function cron_task() {

		$instances_to_update = $this->get_instances_sites();

		/**
		 * If no instances are found to be updated - stop.
		 */
		if ( empty( $instances_to_update ) || ! is_array( $instances_to_update ) ) {
			return true;
		}

		foreach ( $instances_to_update as $site_url ) {
			$this->update_instance( $site_url );
		}

		return true;
	}

	/**
	 * Get a list of unique sites from all instances of the widget.
	 *
	 * @return array|bool
	 */
	public function get_instances_sites() {

		$widget_settings = get_option( 'widget_jetpack_display_posts_widget' );

		/**
		 * If the widget still hasn't been added anywhere, the config will not be present.
		 *
		 * In such case we don't want to continue execution.
		 */
		if ( false === $widget_settings ) {
			return false;
		}

		$urls = array();

		foreach ( $widget_settings as $widget_instance_data ) {
			if ( isset( $widget_instance_data['url'] ) && ! empty( $widget_instance_data['url'] ) ) {
				$urls[] = $widget_instance_data['url'];
			}
		}

		/**
		 * Make sure only unique URLs are returned.
		 */
		$urls = array_unique( $urls );

		return $urls;

	}

	/**
	 * Update a widget instance.
	 *
	 * @param string $site The site to fetch the latest data for.
	 */
	public function update_instance( $site ) {

		/**
		 * Fetch current information for a site.
		 */
		$site_hash = $this->get_site_hash( $site );

		$option_key = $this->widget_options_key_prefix . $site_hash;

		$instance_data = get_option( $option_key );

		if ( empty( $instance_data ) ) {
			$instance_data = array();
		}

		/**
		 * Fetch blog data and save it in $instance_data.
		 */
		$new_data = $this->fetch_blog_data( $site, $instance_data );

		/**
		 * If the option doesn't exist yet - create a new option
		 */
		if ( false === $instance_data ) {
			add_option( $option_key, $new_data );
		}
		else {
			update_option( $option_key, $new_data );
		}
	}

	/**
	 * Set up the widget display on the front end.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		/** This filter is documented in core/src/wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', $instance['title'] );

		wp_enqueue_style( 'jetpack_display_posts_widget', plugins_url( 'wordpress-post-widget/style.css', __FILE__ ) );

		echo $args['before_widget'];

		$data = $this->get_blog_data( $instance['url'] );

		// check for errors
		if ( is_wp_error( $data ) || empty( $data['site_info']['data'] ) ) {
			echo '<p>' . __( 'Cannot load blog information at this time.', 'jetpack' ) . '</p>';
			echo $args['after_widget'];

			return;
		}

		$site_info = $data['site_info']['data'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title . ': ' . $site_info->name ) . $args['after_title'];
		}
		else {
			echo $args['before_title'] . esc_html( $site_info->name ) . $args['after_title'];
		}

		echo '<div class="jetpack-display-remote-posts">';

		if ( is_wp_error( $data['posts']['data'] ) || empty( $data['posts']['data'] ) ) {
			echo '<p>' . __( 'Cannot load blog posts at this time.', 'jetpack' ) . '</p>';
			echo '</div><!-- .jetpack-display-remote-posts -->';
			echo $args['after_widget'];

			return;
		}

		$posts_list = $data['posts']['data'];

		/**
		 * Show only as much posts as we need. If we have less than configured amount,
		 * we must show only that much posts.
		 */
		$number_of_posts = min( $instance['number_of_posts'], count( $posts_list ) );

		for ( $i = 0; $i < $number_of_posts; $i ++ ) {
			$single_post = $posts_list[ $i ];
			$post_title  = ( $single_post['title'] ) ? $single_post['title'] : '( No Title )';

			$target = '';
			if ( isset( $instance['open_in_new_window'] ) && $instance['open_in_new_window'] == true ) {
				$target = ' target="_blank"';
			}
			echo '<h4><a href="' . esc_url( $single_post['url'] ) . '"' . $target . '>' . esc_html( $post_title ) . '</a></h4>' . "\n";
			if ( ( $instance['featured_image'] == true ) && ( ! empty ( $single_post['featured_image'] ) ) ) {
				$featured_image = $single_post['featured_image'];
				/**
				 * Allows setting up custom Photon parameters to manipulate the image output in the Display Posts widget.
				 *
				 * @see    https://developer.wordpress.com/docs/photon/
				 *
				 * @module widgets
				 *
				 * @since  3.6.0
				 *
				 * @param array $args Array of Photon Parameters.
				 */
				$image_params = apply_filters( 'jetpack_display_posts_widget_image_params', array() );
				echo '<a title="' . esc_attr( $post_title ) . '" href="' . esc_url( $single_post['url'] ) . '"><img src="' . jetpack_photon_url( $featured_image, $image_params ) . '" alt="' . esc_attr( $post_title ) . '"/></a>';
			}

			if ( $instance['show_excerpts'] == true ) {
				echo $single_post['excerpt'];
			}
		}

		echo '</div><!-- .jetpack-display-remote-posts -->';
		echo $args['after_widget'];
	}

	/**
	 * Scan and extract first error from blog data array.
	 *
	 * @param array $blog_data Blog data to scan for errors.
	 *
	 * @return string First error message found
	 */
	public function extract_errors_from_blog_data( $blog_data ) {

		$errors = array(
			'message' => '',
			'debug'   => '',
			'where'   => '',
		);


		/**
		 * When the cache result is an error. Usually when the cache is empty.
		 * This is not an error case for now.
		 */
		if ( is_wp_error( $blog_data ) ) {
			return $errors;
		}

		/**
		 * Loop through `site_info` and `posts` keys of $blog_data.
		 */
		foreach ( array( 'site_info', 'posts' ) as $info_key ) {

			/**
			 * Contains information on which stage the error ocurred.
			 */
			$errors['where'] = $info_key;

			/**
			 * If an error is set, we want to check it for usable messages.
			 */
			if ( isset( $blog_data[ $info_key ]['error'] ) && ! empty( $blog_data[ $info_key ]['error'] ) ) {

				/**
				 * Extract error message from the error, if possible.
				 */
				if ( is_wp_error( $blog_data[ $info_key ]['error'] ) ) {
					/**
					 * In the case of WP_Error we want to have the error message
					 * and the debug information available.
					 */
					$error_messages    = $blog_data[ $info_key ]['error']->get_error_messages();
					$errors['message'] = reset( $error_messages );

					$extra_data = $blog_data[ $info_key ]['error']->get_error_data();
					if ( is_array( $extra_data ) ) {
						$errors['debug'] = implode( ';', $extra_data );
					}
					else {
						$errors['debug'] = $extra_data;
					}

					break;
				}
				elseif ( is_array( $blog_data[ $info_key ]['error'] ) ) {
					/**
					 * In this case we don't have debug information, because
					 * we have no way to know the format. The widget works with
					 * WP_Error objects only.
					 */
					$errors['message'] = reset( $error_messages );
					break;
				}

				/**
				 * We do nothing if no usable error is found.
				 */
			}
		}

		return $errors;
	}

	/**
	 * Display the widget administration form.
	 *
	 * @param array $instance Widget instance configuration.
	 *
	 * @return string|void
	 */
	public function form( $instance ) {

		/**
		 * Initialize widget configuration variables.
		 */
		$title              = ( isset( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Posts', 'jetpack' );
		$url                = ( isset( $instance['url'] ) ) ? $instance['url'] : '';
		$number_of_posts    = ( isset( $instance['number_of_posts'] ) ) ? $instance['number_of_posts'] : 5;
		$open_in_new_window = ( isset( $instance['open_in_new_window'] ) ) ? $instance['open_in_new_window'] : false;
		$featured_image     = ( isset( $instance['featured_image'] ) ) ? $instance['featured_image'] : false;
		$show_excerpts      = ( isset( $instance['show_excerpts'] ) ) ? $instance['show_excerpts'] : false;


		/**
		 * Check if the widget instance has errors available.
		 *
		 * Only do so if a URL is set.
		 */
		$update_errors = array();

		if ( ! empty( $url ) ) {
			$data          = $this->get_blog_data( $url );
			$update_errors = $this->extract_errors_from_blog_data( $data );
		}

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'jetpack' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e( 'Blog URL:', 'jetpack' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'url' ); ?>" type="text" value="<?php echo esc_attr( $url ); ?>" />
			<i>
				<?php _e( "Enter a WordPress.com or Jetpack WordPress site URL.", 'jetpack' ); ?>
			</i>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number_of_posts' ); ?>"><?php _e( 'Number of Posts to Display:', 'jetpack' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'number_of_posts' ); ?>">
				<?php
				for ( $i = 1; $i <= 10; $i ++ ) {
					echo '<option value="' . $i . '" ' . selected( $number_of_posts, $i ) . '>' . $i . '</option>';
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'open_in_new_window' ); ?>"><?php _e( 'Open links in new window/tab:', 'jetpack' ); ?></label>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'open_in_new_window' ); ?>" <?php checked( $open_in_new_window, 1 ); ?> />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'featured_image' ); ?>"><?php _e( 'Show Featured Image:', 'jetpack' ); ?></label>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'featured_image' ); ?>" <?php checked( $featured_image, 1 ); ?> />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_excerpts' ); ?>"><?php _e( 'Show Excerpts:', 'jetpack' ); ?></label>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'show_excerpts' ); ?>" <?php checked( $show_excerpts, 1 ); ?> />
		</p>

		<?php

		/**
		 * Show error messages.
		 */
		if ( ! empty( $update_errors['message'] ) ) {

			/**
			 * Prepare the error messages.
			 */

			$where_message = __( 'An error occurred while downloading ', 'jetpack' );
			switch ( $update_errors['where'] ) {
				case 'site_info':
					$where_message .= __( 'blog information', 'jetpack' );
					break;
				case 'posts':
					$where_message .= __( 'blog posts list', 'jetpack' );
					break;
			}

			?>
			<p class="error-message">
				<?php echo $where_message; ?>:
				<br />
				<i>
					<?php echo esc_html( $update_errors['message'] ); ?>
					<?php
					/**
					 * If there is any debug - show it here.
					 */
					if ( ! empty( $update_errors['debug'] ) ) {
						?>
						<br />
						<br />
						<?php echo __( 'Detailed information', 'jetpack' ); ?>:
						<br />
						<?php echo esc_html( $update_errors['debug'] ); ?>
						<?php
					}
					?>
				</i>
			</p>

			<?php
		}
	}

	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['url']   = ( ! empty( $new_instance['url'] ) ) ? strip_tags( $new_instance['url'] ) : '';
		$instance['url']   = preg_replace( "!^https?://!is", "", $instance['url'] );
		$instance['url']   = untrailingslashit( $instance['url'] );

		// Normalize www.
		$site_info = $this->get_site_info( $instance['url'] );
		if ( ! $site_info && 'www.' === substr( $instance['url'], 0, 4 ) ) {
			$site_info = $this->get_site_info( substr( $instance['url'], 4 ) );
			if ( $site_info ) {
				$instance['url'] = substr( $instance['url'], 4 );
			}
		}

		$instance['number_of_posts']    = ( ! empty( $new_instance['number_of_posts'] ) ) ? intval( $new_instance['number_of_posts'] ) : '';
		$instance['open_in_new_window'] = ( ! empty( $new_instance['open_in_new_window'] ) ) ? true : '';
		$instance['featured_image']     = ( ! empty( $new_instance['featured_image'] ) ) ? true : '';
		$instance['show_excerpts']      = ( ! empty( $new_instance['show_excerpts'] ) ) ? true : '';

		return $instance;
	}


	/**
	 * This is just to make method mocks in the unit tests easier.
	 *
	 * @param string $param Option key to get
	 *
	 * @return mixed
	 *
	 * @codeCoverageIgnore
	 */
	public function wp_get_option( $param ) {
		return get_option( $param );
	}
}