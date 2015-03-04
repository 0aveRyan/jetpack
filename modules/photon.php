<?php
/**
 * Module Name: Photon
 * Module Description: Accelerate your site by loading images from the WordPress.com CDN.
 * Jumpstart Description: Your photos will be mirrored and served from our free and fast image CDN boosting your site performance.
 * Sort Order: 25
 * First Introduced: 2.0
 * Requires Connection: Yes
 * Auto Activate: No
 * Module Tags: Photos and Videos, Appearance, Recommended, Jumpstart
 */

Jetpack::dns_prefetch( array(
	'//i0.wp.com',
	'//i1.wp.com',
	'//i2.wp.com',
) );

Jetpack_Photon::instance();