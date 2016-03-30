<div class="page-content landing">
	<?php Jetpack::init()->load_view( 'admin/network-activated-notice.php' ); ?>

	<?php
		/**
		 * Fires when a notice is displayed in the Jetpack menu.
		 *
		 * @since 3.0.0
		 */
		do_action( 'jetpack_notices' );
	?>

	<?php if ( $data['is_connected'] ) : ?>

		<?php if ( $data['show_jumpstart'] && 'new_connection' === Jetpack_Options::get_option( 'jumpstart' ) && current_user_can( 'jetpack_manage_modules' ) && ! Jetpack::is_development_mode() ) : ?>

			<div id="jump-start-success"></div>
			<div id="jump-start-area" class="jump-start-area j-row">
				<h1 title="<?php esc_attr_e( 'Jump Start your site by activating these components', 'jetpack' ); ?>" class="jstart"><?php _e( 'Jump Start your site', 'jetpack' ); ?></h1>
				<div class="jumpstart-desc j-col j-sm-12 j-md-12">
					<div class="jumpstart-message">
						<p id="jumpstart-paragraph-before"><?php
							if ( count( $data['jumpstart_list'] ) > 1 ) {
								$last_item = array_pop( $data['jumpstart_list'] );
								/* translators: %1$s is a comma-separated list of module names or a single module name, %2$s is the last item in the module list */
								echo sprintf( __( 'To quickly boost performance, security, and engagement we recommend activating <strong>%1$s and %2$s</strong>. Click <strong>Jump Start</strong> to activate these features or <a class="pointer jp-config-list-btn">learn more</a>', 'jetpack' ), implode( $data['jumpstart_list'], ', ' ), $last_item );

							} else {
								/* translators: %s is a module name */
								echo sprintf( __( 'To quickly boost performance, security, and engagement we recommend activating <strong>%s</strong>. Click <strong>Jump Start</strong> to activate this feature or <a class="pointer jp-config-list-btn">learn more</a>', 'jetpack' ), $data['jumpstart_list'][0] );
							}
						?></p>
					</div><!-- /.jumpstart-message -->
				</div>
				<div class="jumpstart-message hide">
					<h1 title="<?php esc_attr_e( 'Your site has been sucessfully Jump Started.', 'jetpack' ); ?>" class="success"><?php _e( 'Success! You\'ve jump started your site.', 'jetpack' ); ?></h1>
					<p><?php echo sprintf( __( 'Check out other recommended features below, or go to the <a href="%s">settings</a> page to customize your Jetpack experience.', 'jetpack' ), admin_url( 'admin.php?page=jetpack_modules' ) ); ?></p>
				</div><!-- /.jumpstart-message -->
				<div id="jumpstart-cta" class="j-col j-sm-12 j-md-12 j-lrg-4">
					<img class="jumpstart-spinner" style="margin: 50px auto 14px; display: none;" width="17" height="17" src="<?php echo esc_url( includes_url( 'images/spinner-2x.gif' ) ); ?>" alt="Loading ..." />
					<a id="jump-start" class="button-primary" ><?php esc_html_e( 'Jump Start', 'jetpack' ); ?></a>
					<a class="dismiss-jumpstart pointer" ><?php esc_html_e( 'Skip', 'jetpack' ); ?></a>
				</div>
				<div id="jump-start-module-area">
					<div id="jp-config-list" class="clear j-row hide">
						<a class="pointer jp-config-list-btn close" ><span class="dashicons dashicons-no"></span></a>
					</div>
				</div>
			</div>

		<?php endif; ?>

		<?php if ( $data['is_connected'] && ! $data['is_user_connected'] && current_user_can( 'jetpack_connect_user' ) ) : ?>
			<div class="link-button" style="width: 100%; text-align: center; margin-top: 15px;">
				<a href="<?php echo Jetpack::init()->build_connect_url() ?>" class="download-jetpack"><?php esc_html_e( 'Link your account to WordPress.com', 'jetpack' ); ?></a>
			</div>
		<?php endif; ?>

		<div class="nux-intro jp-content" style="display: none;">

		<h1 title="<?php esc_attr_e( 'Improve your site with Jetpack', 'jetpack' ); ?>"><?php _e( 'Improve your site with Jetpack', 'jetpack' ); ?></h1>
		<p><?php _e( 'Jetpack can help secure your site, increase performance &amp; traffic, and simplify how you manage your site.', 'jetpack' ); ?></p>

		<div class="j-row">

		<?php // Performance & Security ?>
			<div class="j-col j-lrg-4 main-col">
				<div class="nux-in">

					<h3 title="<?php esc_attr_e( 'Performance &amp; Security', 'jetpack' ); ?>">
						<?php /* Leave out until better link is available
						<a class="dashicons dashicons-editor-help" href="http://jetpack.com/features/" title="<?php esc_attr_e( 'Learn more about Jetpack\'s Performance &amp; Security tools', 'jetpack' ); ?>" target="_blank"></a>
                        */ ?>
						<?php _e( 'Performance &amp; Security', 'jetpack' ); ?>
					</h3>

					<?php // The template container from landing-page-templates.php ?>
					<div id="nux-performance-security"></div>

				</div> <?php // nux-in ?>
			</div><?php // j-col ?>
		<?php // END Performance & Security ?>

		<?php // Traffic Boosting Tools ?>
			<div class="j-col j-lrg-4 main-col">
				<div class="nux-in">

					<h3 title="<?php esc_attr_e( 'Traffic Growth', 'jetpack' ); ?>">
						<?php /* Leave out until better link is available
						<a class="dashicons dashicons-editor-help" href="http://jetpack.com/features/" title="<?php esc_attr_e( 'Learn more about Jetpack\'s Traffic Boosting tools', 'jetpack' ); ?>" target="_blank"></a>
						*/ ?>
                        <?php _e( 'Traffic Growth', 'jetpack' ); ?>
					</h3>

					<?php // The template container from landing-page-templates.php ?>
					<div id="nux-traffic"></div>

				</div> <?php // nux-in ?>
			</div><?php // j-col ?>
		<?php // END Traffic Tools ?>


		<?php // WordPress.com Tools ?>
			<div class="wpcom j-col j-lrg-4 main-col">
				<div class="nux-in">

					<h3 title="<?php esc_attr_e( 'WordPress.com Tools', 'jetpack' ); ?>"><a class="dashicons dashicons-editor-help" href="http://jetpack.com/support/site-management/" title="<?php esc_attr_e( 'Learn more about WordPress.com\'s free tools', 'jetpack' ); ?>" target="_blank"></a><?php _e( 'WordPress.com Tools', 'jetpack' ); ?></h3>

					<div class="j-row">
						<div class="j-col j-lrg-12 j-md-12 j-sm-12">
							<h4 title="<?php esc_attr_e( 'Manage Multiple Sites', 'jetpack' ); ?>"><?php _e( 'Manage Multiple Sites', 'jetpack' ); ?></h4>
							<p title="<?php esc_attr_e( 'Bulk site management from one dashboard.', 'jetpack' ); ?>"><?php _e( 'Bulk site management from one dashboard.', 'jetpack' ); ?></p>
						</div>
					</div><?php // j-row ?>

					<div class="j-row">
						<div class="j-col j-lrg-12 j-md-12 j-sm-12">
							<h4 title="<?php esc_attr_e( 'Automatic Updates', 'jetpack' ); ?>"><?php _e( 'Automatic Updates', 'jetpack' ); ?></h4>
							<p title="<?php esc_attr_e( 'Keep plugins auto-updated.', 'jetpack' ); ?>"><?php _e( 'Keep plugins auto-updated.', 'jetpack' ); ?></p>
						</div>
					</div><?php // j-row ?>

					<div class="j-row">
						<div class="j-col j-lrg-12 j-md-12 j-sm-12">
							<h4 title="<?php esc_attr_e( 'Centralized Posting', 'jetpack' ); ?>"><?php _e( 'Centralized Posting', 'jetpack' ); ?></h4>
							<p title="<?php esc_attr_e( 'Post to your sites via mobile devices.', 'jetpack' ); ?>"><?php _e( 'Post to your sites via mobile devices.', 'jetpack' ); ?></p>
						</div>
					</div><?php // j-row ?>

					<div class="j-row">
						<div class="j-col j-lrg-12 j-md-12 j-sm-12">
							<h4 title="<?php esc_attr_e( 'Menu Management', 'jetpack' ); ?>"><?php _e( 'Menu Management', 'jetpack' ); ?></h4>
							<p title="<?php esc_attr_e( 'A simpler UI for creating and editing menus.', 'jetpack' ); ?>"><?php _e( 'A simpler UI for creating and editing menus.', 'jetpack' ); ?></p>
						</div>
					</div><?php // j-row ?>

					<div class="j-row">
						<div class="j-col j-lrg-12 j-md-12 j-sm-12">
							<h4 title="<?php esc_attr_e( 'More Statistics', 'jetpack' ); ?>"><?php _e( 'More Statistics', 'jetpack' ); ?></h4>
							<p title="<?php esc_attr_e( 'Enhanced site stats and insights.', 'jetpack' ); ?>"><?php _e( 'Enhanced site stats and insights.', 'jetpack' ); ?></p>
						</div>
					</div><?php // j-row ?>

					<?php
						$normalized_site_url = Jetpack::build_raw_urls( get_home_url() );
						$manage_active = Jetpack::is_module_active( 'manage' );
					?>
					<?php if ( current_user_can( 'jetpack_manage_modules' ) && $data['is_user_connected'] && ! Jetpack::is_development_mode() ) : ?>
					<div id="manage-row" class="j-row goto <?php echo ( $manage_active ) ? 'activated' : ''; ?>">
						<div class="feat j-col j-lrg-7 j-md-8 j-sm-7">
							<a href="<?php echo esc_url( 'https://wordpress.com/plugins/' . $normalized_site_url . '?from=jpnux' ); ?>" class="button button-primary manage-cta-active" target="_blank" style="display: <?php echo ( $manage_active ) ? 'inline-block' : 'none'; ?>;" title="<?php esc_attr_e( 'Go to WordPress.com to try these features', 'jetpack' ); ?>"><?php _e( 'Go to WordPress.com', 'jetpack' ); ?></a>
							<label for="active-manage" class="button button-primary form-toggle manage-cta-inactive" style="display: <?php echo ( $manage_active ) ? 'none' : 'inline-block'; ?>" title="<?php esc_attr_e( 'Activate free WordPress.com features', 'jetpack' ); ?>"><?php _e( 'Activate features', 'jetpack' ); ?></label>
						</div>
						<div class="act j-col j-lrg-5 j-md-4 j-sm-5">
							<div class="module-action">
								<span>
								<?php $manage_active = Jetpack::is_module_active( 'manage' ); ?>
								<input class="is-compact form-toggle" type="checkbox" id="active-manage" <?php echo ( $manage_active ) ? 'checked' : ''; ?> />
									<label class="form-toggle__label" for="active-manage">
										<img class="module-spinner-manage" style="display: none;" width="16" height="16" src="<?php echo esc_url( includes_url( 'images/spinner-2x.gif' ) ); ?>" alt="Loading ..." />
										<label class="plugin-action__label" for="active-manage">
											<?php ( $manage_active ) ? esc_html_e( 'Active', 'jetpack' ) : esc_html_e( 'Inactive', 'jetpack' ); ?>
										</label>
										<span class="form-toggle__switch"></span>
									</label>
								</span>
							</div>
						</div>
					</div><?php // j-row ?>
					<?php endif; ?>

				</div> <?php // nux-in ?>
			</div><?php // j-col ?>
		<?php // END WordPress.com Tools ?>

	</div><?php // j-row ?>

		<?php if ( current_user_can( 'jetpack_manage_modules' ) ) : ?>
			<p><?php _e( 'Jetpack includes many other features that you can use to customize how your site looks and functions. These include Contact Forms, Tiled Photo Galleries, Custom CSS, Image Carousel, and a lot more.', 'jetpack' ); ?></p>
			<p><a href="<?php echo admin_url( 'admin.php?page=jetpack_modules' ); ?>" class="button full-features-btn" ><?php echo sprintf( __( 'See the other %s Jetpack features', 'jetpack' ), count( Jetpack::get_available_modules() ) - count( $data['recommended_list'] ) ); ?></a></p>
		<?php endif; ?>

		<div class="nux-foot j-row">
			<div class="j-col j-lrg-8 j-md-8 j-sm-12">
			<?php
				// Get a list of Jetpack Happiness Engineers.
				$jetpack_hes = array(
					'724cd8eaaa1ef46e4c38c4213ee1d8b7',
					'623f42e878dbd146ddb30ebfafa1375b',
					'561be467af56cefa58e02782b7ac7510',
					'd8ad409290a6ae7b60f128a0b9a0c1c5',
					'790618302648bd80fa8a55497dfd8ac8',
					'6e238edcb0664c975ccb9e8e80abb307',
					'4e6c84eeab0a1338838a9a1e84629c1a',
					'9d4b77080c699629e846d3637b3a661c',
					'4626de7797aada973c1fb22dfe0e5109',
					'190cf13c9cd358521085af13615382d5',
				);

				// Get a fallback profile image.
				$default_he_img = plugins_url( 'images/jetpack-icon.jpg', JETPACK__PLUGIN_FILE );

				printf(
					'<a href="http://jetpack.com/support/" target="_blank"><img src="https://secure.gravatar.com/avatar/%1$s?s=75&d=%2$s" alt="Jetpack Happiness Engineer" /></a>',
					$jetpack_hes[ array_rand( $jetpack_hes ) ],
					urlencode( $default_he_img )
				);
			?>
			<p><?php _e( 'Help and Support', 'jetpack' ); ?></p>
			<p><?php _e( 'We offer free, full support to all Jetpack users. Our support team is always around to help you.', 'jetpack' ); ?></p>
			<ul class="actions">
				<li><a href="http://jetpack.com/support/" target="_blank" class="button"><?php esc_html_e( 'Visit support site', 'jetpack' ); ?></a></li>
				<li><a href="https://wordpress.org/support/plugin/jetpack" target="_blank"><?php esc_html_e( 'Browse forums', 'jetpack' ); ?></a></li>
				<li><a href="http://jetpack.com/contact-support/" target="_blank"><?php esc_html_e( 'Contact us directly', 'jetpack' ); ?></a></li>
			</ul>
			</div>
			<div class="j-col j-lrg-4 j-md-4 j-sm-12">
				<p><?php _e( 'Premium Add-ons', 'jetpack' ); ?></p>
				<p><?php esc_html_e( 'Business site? Safeguard it with real-time backups, security scans, and anti-spam.', 'jetpack' ); ?></p>
				<p>&nbsp;</p>
				<?php $normalized_site_url = Jetpack::build_raw_urls( get_home_url() ); ?>
				<div class="actions jptracks" data-jptracks-name="nudge_click" data-jptracks-prop="nux-addons"><a href="<?php echo esc_url( 'https://wordpress.com/plans/' . $normalized_site_url ); ?>" target="_blank" class="button"><?php esc_html_e( 'Compare Options', 'jetpack' ); ?></a></div>
			</div>
		</div><?php // nux-foot ?>

		</div><?php // nux-intro ?>

</div><!-- .landing -->

	<?php else : ?>

		<div class="connection-landing">

		<div class="connect-card j-row">
			<h1 title="<?php esc_attr_e( 'Please Connect Jetpack', 'jetpack' ); ?>"><?php esc_html_e( 'Please Connect Jetpack', 'jetpack' ); ?></h1>
			<div class="connect-btn j-col j-sm-12 j-md-12">
				<p><?php echo wp_kses( __( 'Connecting Jetpack will show you <strong>stats</strong> about your traffic, <strong>protect</strong> you from brute force attacks, <strong>speed up</strong> your images and photos, and enable other <strong>traffic and security</strong> features.', 'jetpack' ), 'jetpack' ) ?></p>
				<?php if ( ! $data['is_connected'] && current_user_can( 'jetpack_connect' ) ) : ?>
					<a href="<?php echo Jetpack::init()->build_connect_url() ?>" class="download-jetpack"><?php esc_html_e( 'Connect Jetpack', 'jetpack' ); ?></a>
				<?php elseif ( $data['is_connected'] && ! $data['is_user_connected'] && current_user_can( 'jetpack_connect_user' ) ) : ?>
					<a href="<?php echo Jetpack::init()->build_connect_url() ?>" class="download-jetpack"><?php esc_html_e( 'Connect your account', 'jetpack' ); ?></a>
				<?php endif; ?>
			</div>
		</div> <?php // connect-card ?>

		
			<h2 title="<?php esc_attr_e( 'Why Connect Jetpack?', 'jetpack' ); ?>"><?php esc_html_e( 'Why Connect Jetpack?', 'jetpack' ); ?></h2>
		
			<div class="j-traffic feature-container jp-card">
				<header class="first-header j-int">
					<h2 title="<?php esc_attr_e( 'Get more Traffic on your Site', 'jetpack' ); ?>"><?php esc_html_e( 'Get more Traffic on your Site', 'jetpack' ); ?></h2>
					<p><?php esc_html_e( 'Jetpack has many traffic and engagement tools to help you get more viewers 
	to your site and keep them there.', 'jetpack' ); ?></p>
				</header>

				<div class="three-feature j-row">
					<div class="j-col j-sm-12 j-md-12 j-lrg-4">
						<h3 title="<?php esc_attr_e( 'Publicize', 'jetpack' ); ?>"><?php esc_html_e( 'Publicize', 'jetpack' ); ?></h3>
						<div class="feature-img">
							<a href="<?php echo plugins_url( 'images/connection-landing/feature-publicize.jpg', JETPACK__PLUGIN_FILE ); ?>" target="_blank">
								<img src="<?php echo plugins_url( 'images/connection-landing/feature-publicize.jpg', JETPACK__PLUGIN_FILE ); ?>" alt="<?php esc_attr( 'Connect social media accounts and your content will be automatically shared when you publish it.', 'jetpack' ); ?>" />
							</a>
						</div>
						<p><?php esc_html_e( 'Save time with Jetpack.', 'jetpack' ); ?></p>
						<p><?php esc_html_e( 'Connect social media accounts and your content will be automatically shared when you publish it.', 'jetpack' ); ?></p>
					</div>
					<div class="j-col j-sm-12 j-md-12 j-lrg-4">
						<h3 title="<?php esc_attr_e( 'Sharing &amp; Like Buttons', 'jetpack' ); ?>"><?php esc_html_e( 'Sharing &amp; Like Buttons', 'jetpack' ); ?></h3>
						<div class="feature-img">
							<a href="<?php echo plugins_url( 'images/connection-landing/feature-sharing.jpg', JETPACK__PLUGIN_FILE ); ?>" target="_blank">
								<img src="<?php echo plugins_url( 'images/connection-landing/feature-sharing.jpg', JETPACK__PLUGIN_FILE ); ?>" alt="<?php esc_attr( 'Sharing buttons allow users to quickly share your content through popular social media sites and more.', 'jetpack' ); ?>" />
							</a>
						</div>
						<p><?php esc_html_e( 'Let viewers grow your audience.', 'jetpack' ); ?></p>
						<p><?php esc_html_e( 'Sharing buttons allow users to quickly share your content through popular social media sites and more.', 'jetpack' ); ?></p>
					</div>
					<div class="j-col j-sm-12 j-md-12 j-lrg-4">
						<h3 title="<?php esc_attr_e( 'Related Posts', 'jetpack' ); ?>"><?php esc_html_e( 'Related Posts', 'jetpack' ); ?></h3>
						<div class="feature-img">
						<a href="<?php echo plugins_url( 'images/connection-landing/feature-related.jpg', JETPACK__PLUGIN_FILE ); ?>" target="_blank">
								<img src="<?php echo plugins_url( 'images/connection-landing/feature-related.jpg', JETPACK__PLUGIN_FILE ); ?>" alt="<?php esc_attr( 'Related content at the bottom of your content keep users engaged with your content, longer.', 'jetpack' ); ?>" />
							</a>
						</div>
						<p><?php esc_html_e( 'Retain more viewers.', 'jetpack' ); ?></p>
						<p><?php esc_html_e( 'Related content at the bottom of your content keep users engaged with your content, longer.', 'jetpack' ); ?></p>
					</div>
				</div><?php // three-feature ?>

				<header class="secondary-header j-int">
					<h2 title="<?php esc_attr_e( 'Detailed Insights and Analytics', 'jetpack' ); ?>"><?php esc_html_e( 'Detailed Insights and Analytics', 'jetpack' ); ?></h2>
					<p><?php esc_html_e( 'Jetpack harnesses the power of WordPress.com to show you detailed insights about your visitors, what they’re reading, and where they’re coming from.', 'jetpack' ); ?></p>
				</header>

				<div class="j-feature-img">
				<a href="<?php echo plugins_url( 'images/connection-landing/stats-example-lrg.png', JETPACK__PLUGIN_FILE ); ?>" target="_blank" title="Tap or click to open larger stats image">
					<img srcset="<?php echo plugins_url( 'images/connection-landing/stats-example-sm.png', JETPACK__PLUGIN_FILE ); ?> 1x,
					<?php echo plugins_url( 'images/connection-landing/stats-example-med.png', JETPACK__PLUGIN_FILE ); ?> 1.5x,
					<?php echo plugins_url( 'images/connection-landing/stats-example-lrg.png', JETPACK__PLUGIN_FILE ); ?> 2x" src="<?php echo plugins_url( 'images/connection-landing/stats-example-med.png', JETPACK__PLUGIN_FILE ); ?>" alt="<?php esc_attr( 'View detailed insights and analytics about your site with Jetpack', 'jetpack' ); ?>" />
					</a>
				</div>

				<p><em><?php esc_html_e( '(Just a small preview of all the free statistic tools Jetpack offers)', 'jetpack' ); ?></em></p>

			</div><?php // jp-card ?>

		<div class="connect-card j-row">
			<h1 title="<?php esc_attr_e( 'Ready to Connect?', 'jetpack' ); ?>"><?php esc_html_e( 'Ready to Connect?', 'jetpack' ); ?></h1>
			<div class="connect-btn j-col j-sm-12 j-md-12">
				<p><?php echo wp_kses( __( 'Join the millions of users who rely on Jetpack to enhance and secure their sites. We’re passionate about WordPress and here to make your life easier.', 'jetpack' ), 'jetpack' ) ?></p>
				<?php if ( ! $data['is_connected'] && current_user_can( 'jetpack_connect' ) ) : ?>
					<a href="<?php echo Jetpack::init()->build_connect_url() ?>" class="download-jetpack"><?php esc_html_e( 'Connect Jetpack', 'jetpack' ); ?></a>
				<?php elseif ( $data['is_connected'] && ! $data['is_user_connected'] && current_user_can( 'jetpack_connect_user' ) ) : ?>
					<a href="<?php echo Jetpack::init()->build_connect_url() ?>" class="download-jetpack"><?php esc_html_e( 'Connect your account', 'jetpack' ); ?></a>
				<?php endif; ?>
			</div>
		</div> <?php // connect-card ?>

			<div class="j-security feature-container jp-card">
				<header class="first-header j-int">
					<h2 title="<?php esc_attr_e( 'Site Security and Peace of Mind', 'jetpack' ); ?>"><?php esc_html_e( 'Site Security and Peace of Mind', 'jetpack' ); ?></h2>
					<p><?php esc_html_e( 'Jetpack has many traffic and engagement tools to help you get more viewers 
	to your site and keep them there.', 'jetpack' ); ?></p>
				</header>

				<div class="three-feature j-row">
					<div class="j-col j-sm-12 j-md-12 j-lrg-4">
						<h3 title="<?php esc_attr_e( 'Protect', 'jetpack' ); ?>"><?php esc_html_e( 'Protect', 'jetpack' ); ?></h3>
						<div class="feature-img">
							<a href="<?php echo plugins_url( 'images/connection-landing/feature-protect.jpg', JETPACK__PLUGIN_FILE ); ?>" target="_blank">
								<img src="<?php echo plugins_url( 'images/connection-landing/feature-protect.jpg', JETPACK__PLUGIN_FILE ); ?>" alt="<?php esc_attr( 'Botnets routinely scan the internet looking to break into vunerable sites. With Jetpack, you’re protected.', 'jetpack' ); ?>" />
							</a>
						</div>
						<p><?php esc_html_e( 'Keep out the bots.', 'jetpack' ); ?></p>
						<p><?php esc_html_e( 'Botnets routinely scan the internet looking to break into vunerable sites. With Jetpack, you’re protected.', 'jetpack' ); ?></p>
					</div>
					<div class="j-col j-sm-12 j-md-12 j-lrg-4">
						<h3 title="<?php esc_attr_e( 'Downtime Monitoring', 'jetpack' ); ?>"><?php esc_html_e( 'Downtime Monitoring', 'jetpack' ); ?></h3>
						<div class="feature-img">
							<a href="<?php echo plugins_url( 'images/connection-landing/feature-monitor.jpg', JETPACK__PLUGIN_FILE ); ?>" target="_blank">
								<img src="<?php echo plugins_url( 'images/connection-landing/feature-monitor.jpg', JETPACK__PLUGIN_FILE ); ?>" alt="<?php esc_attr( 'Sometimes technology fails and servers go down. We’ll be sure to email you if and when that happens.', 'jetpack' ); ?>" />
							</a>
						</div>
						<p><?php esc_html_e( 'Ensure your site is online.', 'jetpack' ); ?></p>
						<p><?php esc_html_e( 'Sometimes technology fails and servers go down. We’ll be sure to email you if and when that happens.', 'jetpack' ); ?></p>
					</div>
					<div class="j-col j-sm-12 j-md-12 j-lrg-4">
						<h3 title="<?php esc_attr_e( 'Automatic Updates', 'jetpack' ); ?>"><?php esc_html_e( 'Automatic Updates', 'jetpack' ); ?></h3>
						<div class="feature-img">
						<a href="<?php echo plugins_url( 'images/connection-landing/feature-auto-updates.jpg', JETPACK__PLUGIN_FILE ); ?>" target="_blank">
								<img src="<?php echo plugins_url( 'images/connection-landing/feature-auto-updates.jpg', JETPACK__PLUGIN_FILE ); ?>" alt="<?php esc_attr( 'Save hours of time by keeping all your plugins (and more) automatically updated on all your sites.', 'jetpack' ); ?>" />
							</a>
						</div>
						<p><?php esc_html_e( 'Stay up to date.', 'jetpack' ); ?></p>
						<p><?php esc_html_e( 'Save hours of time by keeping all your plugins (and more) automatically updated on all your sites.', 'jetpack' ); ?></p>
					</div>
				</div><?php // three-feature ?>

				<header class="secondary-header j-int">
					<h2 title="<?php esc_attr_e( 'Faster Loading Images', 'jetpack' ); ?>"><?php esc_html_e( 'Faster Loading Images', 'jetpack' ); ?></h2>
					<p><?php esc_html_e( 'Jetpack utilizes the state-of-the-art WordPress.com Content Delivery Network to load your gorgeous images super fast. It’s free and there’s no image limit.', 'jetpack' ); ?></p>
				</header>

				<div class="j-feature-img">
					<img srcset="<?php echo plugins_url( 'images/connection-landing/feature-photon-sm.jpg', JETPACK__PLUGIN_FILE ); ?> 1x,
					<?php echo plugins_url( 'images/connection-landing/feature-photon-med.jpg', JETPACK__PLUGIN_FILE ); ?> 1.5x,
					<?php echo plugins_url( 'images/connection-landing/feature-photon-lrg.jpg', JETPACK__PLUGIN_FILE ); ?> 2x" src="<?php echo plugins_url( 'images/connection-landing/feature-photon-med.jpg', JETPACK__PLUGIN_FILE ); ?>" alt="<?php esc_attr( 'Have faster loading images with Jetpack Photon', 'jetpack' ); ?>" />
				</div>

				<header class="secondary-header j-int">
					<h2 title="<?php esc_attr_e( 'Did We Mention Free Support?', 'jetpack' ); ?>"><?php esc_html_e( 'Did We Mention Free Support?', 'jetpack' ); ?></h2>
					<p><?php esc_html_e( 'Jetpack is supported by some of the most technical and passionate people in the community. Located around the globe and ready to help you.', 'jetpack' ); ?></p>
				</header>

				<div class="j-feature-img">
					<img srcset="<?php echo plugins_url( 'images/connection-landing/aurora-sm.jpg', JETPACK__PLUGIN_FILE ); ?> 1x,
					<?php echo plugins_url( 'images/connection-landing/aurora-med.jpg', JETPACK__PLUGIN_FILE ); ?> 1.5x,
					<?php echo plugins_url( 'images/connection-landing/aurora-lrg.jpg', JETPACK__PLUGIN_FILE ); ?> 2x" src="<?php echo plugins_url( 'images/connection-landing/aurora-med.jpg', JETPACK__PLUGIN_FILE ); ?>" alt="<?php esc_attr( 'Jetpack has an amazing free support team', 'jetpack' ); ?>" />
				</div>

			</div><?php // jp-card ?>

		<div class="connect-card j-row">
			<h1 title="<?php esc_attr_e( 'Ready to Connect?', 'jetpack' ); ?>"><?php esc_html_e( 'Ready to Connect?', 'jetpack' ); ?></h1>
			<div class="connect-btn j-col j-sm-12 j-md-12">
				<p><?php echo wp_kses( __( 'Join the millions of users who rely on Jetpack to enhance and secure their sites. We’re passionate about WordPress and here to make your life easier.', 'jetpack' ), 'jetpack' ) ?></p>
				<?php if ( ! $data['is_connected'] && current_user_can( 'jetpack_connect' ) ) : ?>
					<a href="<?php echo Jetpack::init()->build_connect_url() ?>" class="download-jetpack"><?php esc_html_e( 'Connect Jetpack', 'jetpack' ); ?></a>
				<?php elseif ( $data['is_connected'] && ! $data['is_user_connected'] && current_user_can( 'jetpack_connect_user' ) ) : ?>
					<a href="<?php echo Jetpack::init()->build_connect_url() ?>" class="download-jetpack"><?php esc_html_e( 'Connect your account', 'jetpack' ); ?></a>
				<?php endif; ?>
			</div>
		</div> <?php // connect-card ?>


		</div> <php // connection landing ?>


	<?php endif; ?>
<div id="deactivate-success"></div>
<?php if ( Jetpack::is_development_version() ) { ?>
	<a id="jump-start-deactivate" style="cursor:pointer; display: block; text-align: center; margin-top: 25px;"><?php esc_html_e( 'RESET EVERYTHING (during testing only) - will reset modules to default as well', 'jetpack' ); ?></a>
<?php } // is_development_version ?>
