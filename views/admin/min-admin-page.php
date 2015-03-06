<div class="clouds-sm"></div>
<div class="page-content landing">
	<!-- needs to get rendered as SCSS -->
	<style>
		.center { text-align: center; }
		.hide { display: none; }
		.pointer { cursor: pointer; }
		.download-jetpack { margin-top: 1em!important; }
		.wrapper { padding-bottom: 0!important; }
		.landing { max-width: 992px !important; margin: 0 auto; min-height: 400px; padding-bottom: 6em; }
		.jp-content h1 { font: 300 2.57143em/1em "proxima-nova","Open Sans",Helvetica,Arial,sans-serif !important;  position: relative;  text-shadow: 0 1px 1px rgba(0, 0, 0, 0.12);  z-index: 3; }
		#jumpstart-cta { text-align: center; }
		#jumpstart-cta .button, #jumpstart-cta .button-primary { margin: 1em; font-size: 18px; height: 45px!important; padding: 8px 15px 1px!important; }
		.jp-content .footer { padding-top: 2em!important; background-image: none!important; }
		.jp-content .footer:before { height: inherit!important; }
		.jp-content .wrapper { padding-bottom: 6em; }
		.more-info:before { content: none; }
	</style>
	<!-- /needs to get rendered as SCSS -->
	<?php Jetpack::init()->load_view( 'admin/network-activated-notice.php' ); ?>

	<?php do_action( 'jetpack_notices' ) ?>

	<?php if ( $data['is_connected'] ) : ?>
	<div id="deactivate-success"></div>
	<a id="jump-start-deactivate" style="cursor:pointer;"><?php esc_html_e( 'RESET EVERYTHING (during testing only)', 'jetpack' ); ?></a>
	<?php
		if ( true !== $data['hide_jumpstart'] && true != get_option( 'jetpack_dismiss_jumpstart' ) ) : ?>
		<div id="jump-start-success"></div>
			<div id="jump-start-area" class="j-row">
				<div class="j-col j-lrg-8">
					<h1><?php _e( 'Jump Start your site', 'jetpack' ); ?></h1>
					<p id="jumpstart-paragraph-before"><?php echo sprintf( __( 'To immediately boost performance, security, and engagement, we recommend activating <strong>%s</strong> and a few others. Click <strong>Jump Start</strong> to activate these modules.', 'jetpack' ), $data['jumpstart_list'] ); ?>
						<a class="pointer" id="jp-config-list-btn"><?php _e( 'Learn more about Jump Start and what it adds to your site.', 'jetpack' ); ?></a>
					</p>
					<p id="jumpstart-paragraph-success" style="display: none;"><?php echo sprintf( __( 'Your site has been given a Jump-start Checkout other recommended features below, or click <a href="%s">here</a> to go to the settings page to customize your Jetpack experience.', 'jetpack' ), admin_url( 'admin.php?page=jetpack_modules' ) ); ?></p>
				</div>
				<div id="jumpstart-cta" class="j-col j-lrg-4">
					<div id="jumpstart-success">
						<a id="jump-start" class="button-primary" ><?php esc_html_e( 'Jump Start', 'jetpack' ); ?></a><br><a class="pointer dismiss-jumpstart" ><?php esc_html_e( 'Dismiss', 'jetpack' ); ?></a>
					</div>
				</div>
				<div id="jump-start-module-area">
					<div id="jp-config-list" class="clear j-row hide"></div>
				</div>
				<span class="spinner" style="display: none;"></span>
			</div>
		<?php endif; ?>

		<?php if ( Jetpack::is_development_mode() ) : ?>
			<h2 class="center"><?php _e('Jetpack is in local development mode.', 'jetpack' ); ?></h2>
		<?php else : ?>

		<h1 class="center"><?php _e( 'Get the most out of Jetpack with...', 'jetpack' ); ?></h1>

		<?php // Recommended modules on the landing page ?>
		<div class="module-grid">
			<div class="modules"></div>
			<a href="<?php echo admin_url( 'admin.php?page=jetpack_modules' ); ?>" class="button" ><?php esc_html_e( 'See the other 25 Jetpack features', 'jetpack' ); ?></a>
		</div><!-- .module-grid -->
</div><!-- .landing -->
		<?php endif; ?>

	<?php else : ?>
		<h1><?php esc_html_e( 'Boost traffic, enhance security, and improve performance.', 'jetpack' ); ?></h1>

		<p><?php _e('Jetpack connects your site to WordPress.com to give you traffic and customization tools, enhanced security, speed boosts, and more.', 'jetpack' ); ?></p>
		<p><?php _e('To start using Jetpack, connect to your WordPress.com account by clicking the button below <br>(if you don’t have an account you can create one quickly and for free).', 'jetpack' ); ?></p>

		<?php if ( ! $data['is_connected'] && current_user_can( 'jetpack_connect' ) ) : ?>
			<a href="<?php echo Jetpack::init()->build_connect_url() ?>" class="download-jetpack"><?php esc_html_e( 'Connect to WordPress.com', 'jetpack' ); ?></a>
		<?php elseif ( $data['is_connected'] && ! $data['is_user_connected'] && current_user_can( 'jetpack_connect_user' ) ) : ?>
			<a href="<?php echo Jetpack::init()->build_connect_url() ?>" class="download-jetpack"><?php esc_html_e( 'Link to your account to WordPress.com', 'jetpack' ); ?></a>
		<?php endif; ?>
	<?php endif; ?>
	<div id="miguels" class="flyby">
		<svg class="miguel" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="80px" height="87px" viewBox="0 0 80 87" enable-background="new 0 0 80 87" xml:space="preserve">
			<polygon class="eye" fill="#518d2a" points="41.187,17.081 46.769,11.292 50.984,15.306"/>
			<path class="body" fill="#518d2a" d="M38.032,47.3l4.973-5.157l7.597,1.996l0.878-0.91l0.761-0.789l-0.688-2.838l-0.972-0.926l-1.858,1.926 l-2.206-2.1l3.803-3.944l0.09-3.872L80,0L61.201,10.382L60.2,15.976l-5.674,1.145l-8.09-7.702L34.282,22.024l8.828-1.109 l2.068,2.929l-4.996,0.655l-3.467,3.595l0.166-4.469l-4.486,0.355L21.248,35.539l-0.441,4.206l-2.282,2.366l-2.04,6.961 L27.69,37.453l4.693,1.442l-2.223,2.306l-4.912,0.095l-7.39,22.292l-8.06,3.848l-2.408,9.811l-3.343-0.739L0,86.739l30.601-31.733 l8.867,2.507l-7.782,8.07l-1.496-0.616l-0.317-2.623l-7.197,7.463l11.445-2.604l16.413-7.999L38.032,47.3z M42.774,16.143 l3.774-3.914l2.85,2.713L42.774,16.143z"/>
		</svg>
		<svg class="miguel" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="80px" height="87px" viewBox="0 0 80 87" enable-background="new 0 0 80 87" xml:space="preserve">
			<polygon class="eye" fill="#518d2a" points="41.187,17.081 46.769,11.292 50.984,15.306   "/>
			<path class="body" fill="#518d2a" d="M38.032,47.3l4.973-5.157l7.597,1.996l0.878-0.91l0.761-0.789l-0.688-2.838l-0.972-0.926l-1.858,1.926 l-2.206-2.1l3.803-3.944l0.09-3.872L80,0L61.201,10.382L60.2,15.976l-5.674,1.145l-8.09-7.702L34.282,22.024l8.828-1.109 l2.068,2.929l-4.996,0.655l-3.467,3.595l0.166-4.469l-4.486,0.355L21.248,35.539l-0.441,4.206l-2.282,2.366l-2.04,6.961 L27.69,37.453l4.693,1.442l-2.223,2.306l-4.912,0.095l-7.39,22.292l-8.06,3.848l-2.408,9.811l-3.343-0.739L0,86.739l30.601-31.733 l8.867,2.507l-7.782,8.07l-1.496-0.616l-0.317-2.623l-7.197,7.463l11.445-2.604l16.413-7.999L38.032,47.3z M42.774,16.143 l3.774-3.914l2.85,2.713L42.774,16.143z"/>
		</svg>
		<svg class="miguel" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="80px" height="87px" viewBox="0 0 80 87" enable-background="new 0 0 80 87" xml:space="preserve">
			<polygon class="eye" fill="#518d2a" points="41.187,17.081 46.769,11.292 50.984,15.306   "/>
			<path class="body" fill="#518d2a" d="M38.032,47.3l4.973-5.157l7.597,1.996l0.878-0.91l0.761-0.789l-0.688-2.838l-0.972-0.926l-1.858,1.926 l-2.206-2.1l3.803-3.944l0.09-3.872L80,0L61.201,10.382L60.2,15.976l-5.674,1.145l-8.09-7.702L34.282,22.024l8.828-1.109 l2.068,2.929l-4.996,0.655l-3.467,3.595l0.166-4.469l-4.486,0.355L21.248,35.539l-0.441,4.206l-2.282,2.366l-2.04,6.961 L27.69,37.453l4.693,1.442l-2.223,2.306l-4.912,0.095l-7.39,22.292l-8.06,3.848l-2.408,9.811l-3.343-0.739L0,86.739l30.601-31.733 l8.867,2.507l-7.782,8.07l-1.496-0.616l-0.317-2.623l-7.197,7.463l11.445-2.604l16.413-7.999L38.032,47.3z M42.774,16.143 l3.774-3.914l2.85,2.713L42.774,16.143z"/>
		</svg>
	</div>
<style>
	.landing { z-index: 2; position: relative; }
	.miguel {
		display: none;
		position: fixed;
		bottom: -200px;
		left: 0;
		z-index: 1;
		-webkit-animation: miguel 3.4s 0s ease-in-out;
		animation: miguel 3.4s 0s ease-in-out;
	}
	.miguel:nth-child(2) {
		left: 49%;
		width: 120px;
		height: 131px;
		-webkit-animation-duration: 2.4s;
		animation-duration: 2.4s;
		-webkit-animation-delay: 0s;
		animation-delay: 0s;
	}
	.miguel:nth-child(3) {
		left: 23%;
		width: 60px;
		height: 66px;
		-webkit-animation-duration: 4.5s;
		animation-duration: 4.5s;
		-webkit-animation-delay: 0s;
		animation-delay: 0s;
	}
	@-webkit-keyframes "miguel" {
		0% {
			-webkit-transform: translate3d(0px, 0px, 0px);
			transform: translate3d(0px, 0px, 0px);
		}
		100% {
			-webkit-transform: translate3d(900px, -900px, 0px);
			transform: translate3d(900px, -900px, 0px);
		}
	}
	@keyframes "miguel" {
		0% {
			-webkit-transform: translate3d(0px, 0px, 0px);
			transform: translate3d(0px, 0px, 0px);
		}
		100% {
			-webkit-transform: translate3d(900px, -900px, 0px);
			transform: translate3d(900px, -900px, 0px);
		}
	}
</style>
