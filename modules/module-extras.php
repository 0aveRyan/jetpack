<?php
/*
 * Load module code that is needed even when a module isn't active.
 * For example, if a module shouldn't be activatable unless certain conditions are met, the code belongs in this file.
 */

// Include extra tools that aren't modules, in a filterable way
$tools = array( 
	'theme-tools/social-links.php',
	'holiday-snow.php', // Happy Holidays!!!
	'theme-tools/random-redirect.php',
);
$jetpack_tools_to_include = apply_filters( 'jetpack-tools-to-include', $tools );

if ( ! empty( $jetpack_tools_to_include ) ) {
	foreach ( $jetpack_tools_to_include as $tool ) {
		if ( file_exists( JETPACK__PLUGIN_DIR . '/modules/' . $tool ) ) {
			require_once( JETPACK__PLUGIN_DIR . '/modules/' . $tool );
		}
	}
}
