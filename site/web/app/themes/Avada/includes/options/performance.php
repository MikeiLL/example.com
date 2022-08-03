<?php
/**
 * Avada Options.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.8
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Advanced settings
 *
 * @param array $sections An array of our sections.
 * @return array
 */
function avada_options_section_performance( $sections ) {
	$settings = get_option( Avada::get_option_name() );

	$all_filetypes = Avada()->pwa->get_filetypes();
	$filetypes     = [];
	foreach ( $all_filetypes as $key => $details ) {
		$filetypes[ $key ] = $details['label'];
	}

	$cache_first_defaults   = [ 'images', 'scripts', 'styles', 'fonts' ];
	$network_first_defaults = [];

	/**
	 * WIP
	$all_pages = get_pages();
	$pages     = [];
	foreach ( $all_pages as $page ) {
		$pages [ $page->ID ] = $page->post_title;
	}
	*/

	$pwa_enabled = ( function_exists( 'wp_register_service_worker_caching_route' ) && class_exists( 'WP_Service_Worker_Caching_Routes' ) );
	$is_https    = ( false !== strpos( home_url(), 'https' ) );

	// Is the JS compiler enabled?
	$is_http2 = Fusion_Dynamic_JS::is_http2();
	if ( $is_http2 ) {
		$js_compiler_enabled = ( isset( $settings['js_compiler'] ) && ( '1' === $settings['js_compiler'] || 1 === $settings['js_compiler'] || true === $settings['js_compiler'] ) );
	} else {
		$js_compiler_enabled = ( ! isset( $settings['js_compiler'] ) || ( '1' === $settings['js_compiler'] || 1 === $settings['js_compiler'] || true === $settings['js_compiler'] ) );
	}

	$sections['performance'] = [
		'label'    => esc_html__( 'Performance', 'Avada' ),
		'id'       => 'heading_performance',
		'is_panel' => true,
		'priority' => 25,
		'icon'     => 'el-icon-time-alt',
		'alt_icon' => 'fusiona-check',
		'fields'   => [
			'pw_jpeg_quality'                      => [
				'label'       => esc_html__( 'WordPress jpeg Quality', 'Avada' ),
				/* translators: "Regenerate Thumbnails" plugin link. */
				'description' => sprintf( esc_html__( 'Controls the quality of the generated image sizes for every uploaded image. Ranges between 0 and 100 percent. Higher values lead to better image qualities but also higher file sizes. NOTE: After changing this value, please install and run the %s plugin once.', 'Avada' ), '<a target="_blank" href="' . admin_url( 'plugin-install.php?s=Regenerate+Thumbnails&tab=search&type=term' ) . '" title="' . esc_html__( 'Regenerate Thumbnails', 'Avada' ) . '">' . esc_html__( 'Regenerate Thumbnails', 'Avada' ) . '</a>' ),
				'id'          => 'pw_jpeg_quality',
				'default'     => '82',
				'type'        => 'slider',
				'choices'     => [
					'min'  => '1',
					'max'  => '100',
					'step' => '1',
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'wp_big_image_size_threshold'          => [
				'label'       => esc_html__( 'WordPress Big Image Size Threshold', 'Avada' ),
				'description' => esc_html__( 'Sets the threshold for image height and width, above which WordPress will scale down newly uploaded images to this values as max-width or max-height. Set to "0" to disable the threshold completely.', 'Avada' ),
				'id'          => 'wp_big_image_size_threshold',
				'default'     => '2560',
				'type'        => 'slider',
				'choices'     => [
					'min'  => '0',
					'max'  => '5000',
					'step' => '1',
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'lazy_load'                            => [
				'label'       => esc_html__( 'Image Lazy Loading', 'Avada' ),
				'description' => __( 'Choose your preferred lazy loading method for your website\'s images to improve performance. <strong>IMPORTANT:</strong> The WordPress native method can cause issues with dynamically loaded elements like image carousels.', 'Avada' ),
				'id'          => 'lazy_load',
				'default'     => 'none',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'avada'     => esc_html__( 'Avada', 'Avada' ),
					'wordpress' => esc_html__( 'WordPress', 'Avada' ),
					'none'      => esc_html__( 'None', 'Avada' ),
				],
				'transport'   => 'postMessage', // No need to refresh the page.
			],
			'font_face_display'                    => [
				'label'       => esc_html__( 'Font Face Rendering', 'Avada' ),
				'description' => esc_html__( 'Choose "Swap All" for faster rendering with possible flash of unstyled text (FOUT) or "Block" for clean rendering but longer wait time until first paint. "Swap Non-Icon Fonts" will use a mix of the first 2 methods ("swap" for text fonts and "block" for icon-fonts).', 'Avada' ),
				'id'          => 'font_face_display',
				'default'     => 'block',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'block'    => esc_html__( 'Block', 'Avada' ),
					'swap'     => esc_html__( 'Swap Non-Icon Fonts', 'Avada' ),
					'swap-all' => esc_html__( 'Swap All', 'Avada' ),
				],
				'transport'   => 'postMessage', // No need to refresh the page.
			],
			'preload_fonts'                        => [
				'label'       => esc_html__( 'Preload Key Fonts', 'Avada' ),
				'description' => esc_html__( 'Make a selection to prioritize fetching resources that will be requested later in page load. This improves page load time as the browser caches preloaded resources so they are available immediately when needed.', 'Avada' ),
				'id'          => 'preload_fonts',
				'default'     => 'icon_fonts',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'all'          => esc_html__( 'All', 'Avada' ),
					'google_fonts' => esc_html__( 'Google Fonts', 'Avada' ),
					'icon_fonts'   => esc_html__( 'Icon Fonts', 'Avada' ),
					'none'         => esc_html__( 'None', 'Avada' ),
				],
				'transport'   => 'postMessage', // No need to refresh the page.
			],
			'preload_fonts_variants'               => [
				'label'       => esc_html__( 'Preload Google Font Variants', 'Avada' ),
				'description' => esc_html__( 'Select the variants of Google fonts that should get preloaded. Leave empty to preload all variants.', 'Avada' ),
				'id'          => 'preload_fonts_variants',
				'default'     => '400',
				'type'        => 'select',
				'multi'       => true,
				'choices'     => [
					'100'        => esc_html__( 'Ultra-Light 100', 'Avada' ),
					'100-italic' => esc_html__( 'Ultra-Light 100 Italic', 'Avada' ),
					'200'        => esc_html__( 'Light 200', 'Avada' ),
					'200-italic' => esc_html__( 'Light 200 Italic', 'Avada' ),
					'300'        => esc_html__( 'Book 300', 'Avada' ),
					'300-italic' => esc_html__( 'Book 300 Italic', 'Avada' ),
					'400'        => esc_html__( 'Normal 400', 'Avada' ),
					'400-italic' => esc_html__( 'Normal 400 Italic', 'Avada' ),
					'500'        => esc_html__( 'Medium 500', 'Avada' ),
					'500-italic' => esc_html__( 'Medium 500 Italic', 'Avada' ),
					'600'        => esc_html__( 'Semi-Bold 600', 'Avada' ),
					'600-italic' => esc_html__( 'Semi-Bold 600 Italic', 'Avada' ),
					'700'        => esc_html__( 'Bold 700', 'Avada' ),
					'700-italic' => esc_html__( 'Bold 700 Italic', 'Avada' ),
					'800'        => esc_html__( 'Extra-Bold 800', 'Avada' ),
					'900-italic' => esc_html__( 'Extra-Bold 800 Italic', 'Avada' ),
					'900'        => esc_html__( 'Ultra-Bold 900', 'Avada' ),
					'900-italic' => esc_html__( 'Ultra-Bold 900 Italic', 'Avada' ),                                                                                                                                                                                               
				],
				'required'    => [
					[
						'setting'  => 'preload_fonts',
						'operator' => '!=',
						'value'    => 'none',
					],
					[
						'setting'  => 'preload_fonts',
						'operator' => '!=',
						'value'    => 'icon_fonts',
					],                  
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'preload_fonts_subsets'                => [
				'label'       => esc_html__( 'Preload Google Font Subsets', 'Avada' ),
				'description' => esc_html__( 'Select the subsets of Google fonts that should get preloaded. Leave empty to preload all subsets.', 'Avada' ),
				'id'          => 'preload_fonts_subsets',
				'default'     => 'latin',
				'type'        => 'select',
				'multi'       => true,
				'choices'     => [
					'arabic'              => esc_html__( 'Latin', 'Avada' ),
					'bengali'             => esc_html__( 'Bengali', 'Avada' ),
					'chinese-hongkong'    => esc_html__( 'Chinese Hongkong', 'Avada' ),
					'chinese-simplified'  => esc_html__( 'Chinese Simplified', 'Avada' ),
					'chinese-traditional' => esc_html__( 'Chinese Traditional', 'Avada' ),
					'cyrillic'            => esc_html__( 'Cyrillic', 'Avada' ),
					'cyrillic-ext'        => esc_html__( 'Cyrillic Extended', 'Avada' ),
					'devanagari'          => esc_html__( 'Devanagari', 'Avada' ),
					'greek'               => esc_html__( 'Greek', 'Avada' ),
					'greek-ext'           => esc_html__( 'Greek Extended', 'Avada' ),
					'gujarati'            => esc_html__( 'Gujarati', 'Avada' ),
					'gurmukhi'            => esc_html__( 'Gurmukhi', 'Avada' ),
					'hebrew'              => esc_html__( 'Hebrew', 'Avada' ),
					'japanese'            => esc_html__( 'Japanese', 'Avada' ),
					'kannada'             => esc_html__( 'Kannada', 'Avada' ),
					'khmer'               => esc_html__( 'Khmer', 'Avada' ),
					'korean'              => esc_html__( 'Korean', 'Avada' ),
					'latin'               => esc_html__( 'Latin', 'Avada' ),
					'latin-ext'           => esc_html__( 'Latin Extended', 'Avada' ),
					'malayalam'           => esc_html__( 'Malayalam', 'Avada' ),
					'myanmar'             => esc_html__( 'Myanmar', 'Avada' ),
					'oriya'               => esc_html__( 'Oriya', 'Avada' ),
					'sinhala'             => esc_html__( 'Sinhala', 'Avada' ),
					'tamil'               => esc_html__( 'Tamil', 'Avada' ),
					'telugu'              => esc_html__( 'Telugu', 'Avada' ),
					'thai'                => esc_html__( 'Thai', 'Avada' ),
					'tibetan'             => esc_html__( 'Tibetan', 'Avada' ),
					'vietnamese'          => esc_html__( 'Vietnamese', 'Avada' ),
				],
				'required'    => [
					[
						'setting'  => 'preload_fonts',
						'operator' => '!=',
						'value'    => 'none',
					],
					[
						'setting'  => 'preload_fonts',
						'operator' => '!=',
						'value'    => 'icon_fonts',
					],                  
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'emojis_disabled'                      => [
				'label'       => esc_html__( 'Emojis Script', 'Avada' ),
				'description' => esc_html__( 'If you don\'t use emojis you can improve performance by removing WordPress\' emojis script.', 'Avada' ),
				'id'          => 'emojis_disabled',
				'default'     => 'enabled',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'enabled'  => esc_html__( 'Enable', 'Avada' ),
					'disabled' => esc_html__( 'Disable', 'Avada' ),
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'jquery_migrate_disabled'              => [
				'label'       => esc_html__( 'jQuery Migrate Script', 'Avada' ),
				'description' => esc_html__( 'If you are not using any deprecated jQuery code, you can disable this script to improve performance.', 'Avada' ),
				'id'          => 'jquery_migrate_disabled',
				'default'     => 'enabled',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'enabled'  => esc_html__( 'Enable', 'Avada' ),
					'disabled' => esc_html__( 'Disable', 'Avada' ),
				],
				// No need to refresh the page.
				'transport'   => 'postMessage',
			],
			'defer_styles'                         => [
				'label'       => esc_html__( 'Load Stylesheets In Footer', 'Avada' ),
				'description' => esc_html__( 'Set to \'on\' to defer loading of the stylesheets to the footer of the page. This improves page load time by making the styles non-render-blocking. Depending on the connection speed, a flash of unstyled content (FOUC) might occur.', 'Avada' ),
				'id'          => 'defer_styles',
				'default'     => '0',
				'type'        => 'switch',
			],
			'dynamic_compiler_section'             => [
				'label' => esc_html__( 'Dynamic CSS & JS', 'Avada' ),
				'id'    => 'dynamic_compiler_section',
				'icon'  => true,
				'type'  => 'info',
			],
			'css_cache_method'                     => [
				'label'       => esc_html__( 'CSS Compiling Method', 'Avada' ),
				'description' => esc_html__( 'Select "File" mode to compile the dynamic CSS to files (a separate file will be created for each of your pages & posts inside of the uploads/fusion-styles folder), "Database" mode to cache the CSS in your database, or select "Disabled" to disable.', 'Avada' ),
				'id'          => 'css_cache_method',
				'default'     => 'file',
				'type'        => 'radio-buttonset',
				'choices'     => [
					'file' => esc_html__( 'File', 'Avada' ),
					'db'   => esc_html__( 'Database', 'Avada' ),
					'off'  => esc_html__( 'Disabled', 'Avada' ),
				],
				'transport'   => 'postMessage', // No need to refresh the page.
			],
			'media_queries_async'                  => [
				'label'       => esc_html__( 'Load Media-Queries Files Asynchronously', 'Avada' ),
				'description' => esc_html__( 'When enabled, the CSS media-queries will be enqueued separately and then loaded asynchronously, improving performance on mobile and desktop. Please note that this option is only partly compatible with older IE versions and will force mobile viewport on those browsers.', 'Avada' ),
				'id'          => 'media_queries_async',
				'default'     => '0',
				'type'        => 'switch',
				'transport'   => 'postMessage', // No need to refresh the page.
			],
			'css_vars'                             => [
				'label'       => esc_html__( 'Enable CSS Variables', 'Avada' ),
				'description' => sprintf(
					/* translators: Link properties. */
					__( 'Enable this option to use CSS Variables (Custom Properties). Makes compilations faster and lighter, but is <a %s>not compatible with older IE browsers</a>.', 'Avada' ),
					'href="https://caniuse.com/#feat=css-variables" target="_blank" rel="external nofollow noopener noreferrer"'
				),
				'id'          => 'css_vars',
				'default'     => '0',
				'type'        => 'switch',
				'transport'   => 'postMessage', // No need to refresh the page.
			],
			'cache_server_ip'                      => [
				'label'       => esc_html__( 'Cache Server IP', 'Avada' ),
				'description' => esc_html__( 'For unique cases where you are using cloud flare and a cache server, ex: varnish cache. Enter your cache server IP to clear the Global Options dynamic CSS cache. Consult with your server admin for help.', 'Avada' ),
				'id'          => 'cache_server_ip',
				'default'     => '',
				'type'        => 'text',
				'transport'   => 'postMessage', // No need to refresh the page.
			],
			'js_compiler_note'                     => ( apply_filters( 'fusion_compiler_js_file_is_readable', ( get_transient( 'fusion_dynamic_js_readable' ) || ! $js_compiler_enabled ) ) ) ? [] : [
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> JS Compiler is disabled. File does not exist or access is restricted.', 'Avada' ) . '</div>',
				'id'          => 'js_compiler_note',
				'type'        => 'custom',
			],
			'js_compiler'                          => [
				'label'       => esc_html__( 'Enable JS Compiler', 'Avada' ),
				'description' => ( $is_http2 ) ? esc_html__( 'We have detected that your server supports HTTP/2. We recommend you leave the compiler disabled as that will improve performance of your site by allowing multiple JS files to be downloaded simultaneously.', 'Avada' ) : esc_html__( 'By default all the javascript files are combined. Disabling the JS compiler will load non-combined javascript files. This will have an impact on the performance of your site.', 'Avada' ),
				'id'          => 'js_compiler',
				'default'     => ( $is_http2 ) ? '0' : '1',
				'type'        => 'switch',
				'transport'   => 'postMessage', // No need to refresh the page.
			],
			'reset_caches_button'                  => [
				'label'         => esc_html__( 'Reset Avada Caches', 'Avada' ),
				/* translators: %1$s: <code>uploads/fusion-styles</code>. %2$s: <code>uploads/fusion-scripts</code>. */
				'description'   => ( is_multisite() && is_main_site() ) ? sprintf( esc_html__( 'Resets all Dynamic CSS & Dynamic JS, cleans-up the database and deletes the %1$s and %2$s folders. When resetting the caches on the main site of a multisite installation, caches for all sub-sites will be reset. IMPORTANT NOTE: On large multisite installations with a low PHP timeout setting, bulk-resetting the caches may timeout.', 'Avada' ), '<code>uploads/fusion-styles</code>', '<code>uploads/fusion-scripts</code>' ) : sprintf( esc_html__( 'Resets all Dynamic CSS & Dynamic JS, cleans-up the database and deletes the %1$s and %2$s folders.', 'Avada' ), '<code>uploads/fusion-styles</code>', '<code>uploads/fusion-scripts</code>' ),
				'id'            => 'reset_caches_button',
				'default'       => '',
				'type'          => 'raw',
				'content'       => '<a class="button button-secondary" href="#" onclick="fusionResetCaches(event);" target="_self" >' . esc_html__( 'Reset Avada Caches', 'Avada' ) . '</a><span class="spinner fusion-spinner"></span>',
				'full_width'    => false,
				'transport'     => 'postMessage', // No need to refresh the page.
				'hide_on_front' => true,
			],
			'pwa_section'                          => [
				'label' => esc_html__( 'Progressive Web App', 'Avada' ),
				'id'    => 'pwa_section',
				'icon'  => true,
				'type'  => 'info',
			],
			'pwa_required_notice'                  => ! $pwa_enabled ? [
				'label'       => '',
				'description' => sprintf(
					/* translators: URL for the plugins page. */
					'<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> To use the Avada PWA feature you need to install and activate the latest version of the PWA plugin. Please <a href="%s">visit the Avada Plugins page</a> to install and activate the plugin and then refresh Global Options to edit the options.', 'Avada' ) . '</div>',
					admin_url( 'admin.php?page=avada-plugins' )
				),
				'id'          => 'pwa_required_notice',
				'type'        => 'custom',
			] : [],
			'pwa_required_https_notice'            => $pwa_enabled && ! $is_https ? [
				'label'       => '',
				'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> To use the Avada PWA feature your site must use SSL (HTTPS). For more information about the options in this section, please see our <a href="https://theme-fusion.com/documentation/avada/options/avada-progressive-web-app/" target="_blank">PWA documentation</a> page. To learn more about caching strategies and their use in general you can <a href="https://developers.google.com/web/tools/workbox/modules/workbox-strategies" target="_blank" rel="nofollow">read this article</a>.', 'Avada' ) . '</div>',
				'id'          => 'pwa_required_https_notice',
				'type'        => 'custom',
			] : [],
			'pwa_enable'                           => $pwa_enabled ? [
				'label'       => esc_html__( 'Enable Progressive Web App', 'Avada' ),
				'description' => esc_html__( 'Enable this option if you want to enable the Progressive Web App feature and options on your website.', 'Avada' ),
				'id'          => 'pwa_enable',
				'default'     => '0',
				'type'        => 'switch',
				'transport'   => 'postMessage', // No need to refresh the page.
			] : [],
			'pwa_filetypes_cache_first'            => $pwa_enabled ? [
				'label'       => esc_html__( 'Cache-First strategy file types', 'Avada' ),
				'description' => esc_html__( 'File types added in this list will be cached in the browser. Subsequent page requests will use the cached assets. Use this for static assets that don\'t change like images and fonts.', 'Avada' ),
				'id'          => 'pwa_filetypes_cache_first',
				'default'     => $cache_first_defaults,
				'type'        => 'select',
				'multi'       => true,
				'choices'     => $filetypes,
				'required'    => [
					[
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'transport'   => 'postMessage', // No need to refresh the page.
			] : [],
			'pwa_filetypes_network_first'          => $pwa_enabled ? [
				'label'       => esc_html__( 'Network-First strategy file types', 'Avada' ),
				'description' => esc_html__( 'File types added in this list will be cached in the browser. Subsequent page requests will first try to get a more recent version of these files from the network, and fallback to the cached files in case the network is unreachable. If your site\'s content gets updated often we recommend you can use this for your content.', 'Avada' ),
				'id'          => 'pwa_filetypes_network_first',
				'default'     => $network_first_defaults,
				'type'        => 'select',
				'multi'       => true,
				'choices'     => $filetypes,
				'required'    => [
					[
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'transport'   => 'postMessage', // No need to refresh the page.
			] : [],
			'pwa_filetypes_stale_while_revalidate' => $pwa_enabled ? [
				'label'       => esc_html__( 'Stale-While-Revalidating strategy file types', 'Avada' ),
				'description' => esc_html__( 'Any file types added here will be served with a cache-first strategy, and after the page has been loaded the caches will be updated with more recent versions of the selected file types from the network. Use this for assets that may get updated but having their latest version is not critical.', 'Avada' ),
				'id'          => 'pwa_filetypes_stale_while_revalidate',
				'default'     => [],
				'type'        => 'select',
				'multi'       => true,
				'choices'     => $filetypes,
				'required'    => [
					[
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'transport'   => 'postMessage', // No need to refresh the page.
			] : [],
			'pwa_manifest_logo'                    => $pwa_enabled ? [
				'label'       => esc_html__( 'App Splash Screen Logo', 'Avada' ),
				'description' => esc_html__( 'Logo displayed for your website at 512px x 512px when installing as an app. Logo image must be in PNG format.', 'Avada' ),
				'id'          => 'pwa_manifest_logo',
				'default'     => '',
				'type'        => 'media',
				'mode'        => false,
				'required'    => [
					[
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'transport'   => 'postMessage', // No need to refresh the page.
			] : [],
			'pwa_manifest_display'                 => $pwa_enabled ? [
				'label'       => esc_html__( 'App Display Mode', 'Avada' ),
				'description' => __( 'If the user installs your site as an app, select how the app will behave. For more information about these options please refer to <a href="https://developers.google.com/web/fundamentals/web-app-manifest/#display" target="_blank">this document.</a>', 'Avada' ),
				'id'          => 'pwa_manifest_display',
				'default'     => 'minimal-ui',
				'type'        => 'select',
				'choices'     => [
					'fullscreen' => esc_html__( 'Fullscreen', 'Avada' ),
					'standalone' => esc_html__( 'Standalone', 'Avada' ),
					'minimal-ui' => esc_html__( 'Minimal UI', 'Avada' ),
					'browser'    => esc_html__( 'Browser', 'Avada' ),
				],
				'required'    => [
					[
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'transport'   => 'postMessage', // No need to refresh the page.
			] : [],
			/**
			 * This is still a work in progress in the PWA plugin
			 *
			'pwa_precache_pages'                   => $pwa_enabled ? [
				'label'       => esc_html__( 'Precache Pages', 'Avada' ),
				'description' => esc_html__( 'Pages added to this list will be precached and become available faster. You can use this option to precache your homepage or any other pages that are frequently visited. Use with caution and restraint.', 'Avada' ),
				'id'          => 'pwa_precache_pages',
				'default'     => [],
				'type'        => 'select',
				'multi'       => true,
				'choices'     => $pages,
				'required'    => [
					[
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'transport'   => 'postMessage', // No need to refresh the page.
			] : []
			*/
			'pwa_theme_color'                      => $pwa_enabled ? [
				'label'       => esc_html__( 'App Theme Color', 'Avada' ),
				'description' => __( 'Select a color that will be used for the header of your app, as well as the browser toolbar-color on mobile devices.', 'Avada' ),
				'id'          => 'pwa_theme_color',
				'default'     => 'minimal-ui',
				'type'        => 'color',
				'default'     => isset( $settings['mobile_header_bg_color'] ) ? $settings['mobile_header_bg_color'] : '#ffffff',
				'required'    => [
					[
						'setting'  => 'pwa_enable',
						'operator' => '==',
						'value'    => '1',
					],
				],
				'transport'   => 'postMessage', // No need to refresh the page.
			] : [],
		],
	];

	return $sections;

}
