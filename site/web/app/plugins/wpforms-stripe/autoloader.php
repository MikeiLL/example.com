<?php
/**
 * Setup custom autoloader and allow access to loader.
 *
 * @since 2.0.0
 */

/**
 * Load the class only when it's requested.
 * Inspired by the official example implementations of PSR-4:
 * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * @since 2.0.0
 *
 * @param string $class
 */
spl_autoload_register( function ( $class ) {

	// Our base namespace for all plugin classes.
	$prefix = 'WPFormsStripe\\';

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		// No, move to the next registered autoloader.
		return;
	}

	// Base directory for the namespace prefix.
	$base_dir = __DIR__ . '/src/';

	// Get the relative class name.
	$relative_class = substr( $class, $len );

	// Replace the namespace prefix with the base directory.
	// Replace namespace separators with directory separators in the relative
	// class name. Append with .php.
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	// Finally, require the file.
	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

/**
 * Get the instance of the plugin main class, which actually loads all the code.
 *
 * @since 2.0.0
 *
 * @return \WPFormsStripe\Loader
 */
function wpforms_stripe() {
	return \WPFormsStripe\Loader::get_instance();
}
wpforms_stripe();
