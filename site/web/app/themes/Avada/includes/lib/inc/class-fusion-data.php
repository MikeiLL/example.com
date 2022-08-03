<?php
/**
 * This class contains static functions
 * that contain collections of data.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * A collection of data.
 *
 * @since 1.0.0
 */
class Fusion_Data {

	/**
	 * Returns an array of all the social icons included in the core fusion font.
	 *
	 * @static
	 * @access public
	 * @param  bool $custom If we want a custom icon entry.
	 * @param  bool $colors If we want to get the colors.
	 * @return  array
	 */
	public static function fusion_social_icons( $custom = true, $colors = false ) {

		$networks = [
			'blogger'    => [
				'label' => 'Blogger',
				'color' => '#f57d00',
			],
			'deviantart' => [
				'label' => 'Deviantart',
				'color' => '#4dc47d',
			],
			'discord'    => [
				'label' => 'Discord',
				'color' => '#26262B',
			],
			'digg'       => [
				'label' => 'Digg',
				'color' => '#000000',
			],
			'dribbble'   => [
				'label' => 'Dribbble',
				'color' => '#ea4c89',
			],
			'dropbox'    => [
				'label' => 'Dropbox',
				'color' => '#007ee5',
			],
			'facebook'   => [
				'label' => 'Facebook',
				'color' => '#3b5998',
			],
			'flickr'     => [
				'label' => 'Flickr',
				'color' => '#0063dc',
			],
			'forrst'     => [
				'label' => 'Forrst',
				'color' => '#5b9a68',
			],
			'instagram'  => [
				'label' => 'Instagram',
				'color' => '#3f729b',
			],
			'linkedin'   => [
				'label' => 'LinkedIn',
				'color' => '#0077b5',
			],
			'myspace'    => [
				'label' => 'Myspace',
				'color' => '#000000',
			],
			'paypal'     => [
				'label' => 'Paypal',
				'color' => '#003087',
			],
			'pinterest'  => [
				'label' => 'Pinterest',
				'color' => '#bd081c',
			],
			'reddit'     => [
				'label' => 'Reddit',
				'color' => '#ff4500',
			],
			'rss'        => [
				'label' => 'RSS',
				'color' => '#f26522',
			],
			'skype'      => [
				'label' => 'Skype',
				'color' => '#00aff0',
			],
			'soundcloud' => [
				'label' => 'Soundcloud',
				'color' => '#ff8800',
			],
			'spotify'    => [
				'label' => 'Spotify',
				'color' => '#2ebd59',
			],
			'tiktok'     => [
				'label' => 'Tiktok',
				'color' => '#010101',
			],
			'tumblr'     => [
				'label' => 'Tumblr',
				'color' => '#35465c',
			],
			'twitter'    => [
				'label' => 'Twitter',
				'color' => '#55acee',
			],
			'twitch'     => [
				'label' => 'Twitch',
				'color' => '#6441a5',
			],
			'vimeo'      => [
				'label' => 'Vimeo',
				'color' => '#1ab7ea',
			],
			'vk'         => [
				'label' => 'VK',
				'color' => '#45668e',
			],
			'wechat'     => [
				'label' => 'WeChat',
				'color' => '#7bb22e',
			],
			'whatsapp'   => [
				'label' => 'WhatsApp',
				'color' => '#77e878',
			],
			'xing'       => [
				'label' => 'Xing',
				'color' => '#026466',
			],
			'yahoo'      => [
				'label' => 'Yahoo',
				'color' => '#410093',
			],
			'yelp'       => [
				'label' => 'Yelp',
				'color' => '#af0606',
			],
			'youtube'    => [
				'label' => 'Youtube',
				'color' => '#cd201f',
			],
			'email'      => [
				'label' => esc_html__( 'Email Address', 'Avada' ),
				'color' => '#000000',
			],
			'phone'      => [
				'label' => esc_html__( 'Phone', 'Avada' ),
				'color' => '#000000',
			],
		];

		// Add a "custom" entry.
		if ( $custom ) {
			$networks['custom'] = [
				'label' => esc_html__( 'Custom', 'Avada' ),
				'color' => '',
			];
		}

		if ( ! $colors ) {
			$simple_networks = [];
			foreach ( $networks as $network_id => $network_args ) {
				$simple_networks[ $network_id ] = $network_args['label'];
			}
			$networks = $simple_networks;
		}

		return $networks;

	}

	/**
	 * Returns an array of old names for font-awesome icons
	 * and their new destinations on font-awesome.
	 *
	 * @static
	 * @access public
	 */
	public static function old_icons() {

		$icons = [
			'arrow'                  => 'angle-right',
			'asterik'                => 'asterisk',
			'cross'                  => 'times',
			'ban-circle'             => 'ban',
			'bar-chart'              => 'bar-chart-o',
			'beaker'                 => 'flask',
			'bell'                   => 'bell-o',
			'bell-alt'               => 'bell',
			'bitbucket-sign'         => 'bitbucket-square',
			'bookmark-empty'         => 'bookmark-o',
			'building'               => 'building-o',
			'calendar-empty'         => 'calendar-o',
			'check-empty'            => 'square-o',
			'check-minus'            => 'minus-square-o',
			'check-sign'             => 'check-square',
			'check'                  => 'check-square-o',
			'chevron-sign-down'      => 'chevron-circle-down',
			'chevron-sign-left'      => 'chevron-circle-left',
			'chevron-sign-right'     => 'chevron-circle-right',
			'chevron-sign-up'        => 'chevron-circle-up',
			'circle-arrow-down'      => 'arrow-circle-down',
			'circle-arrow-left'      => 'arrow-circle-left',
			'circle-arrow-right'     => 'arrow-circle-right',
			'circle-arrow-up'        => 'arrow-circle-up',
			'circle-blank'           => 'circle-o',
			'cny'                    => 'rub',
			'collapse-alt'           => 'minus-square-o',
			'collapse-top'           => 'caret-square-o-up',
			'collapse'               => 'caret-square-o-down',
			'comment-alt'            => 'comment-o',
			'comments-alt'           => 'comments-o',
			'copy'                   => 'files-o',
			'cut'                    => 'scissors',
			'dashboard'              => 'tachometer',
			'double-angle-down'      => 'angle-double-down',
			'double-angle-left'      => 'angle-double-left',
			'double-angle-right'     => 'angle-double-right',
			'double-angle-up'        => 'angle-double-up',
			'download'               => 'arrow-circle-o-down',
			'download-alt'           => 'download',
			'edit-sign'              => 'pencil-square',
			'edit'                   => 'pencil-square-o',
			'ellipsis-horizontal'    => 'ellipsis-h',
			'ellipsis-vertical'      => 'ellipsis-v',
			'envelope-alt'           => 'envelope-o',
			'exclamation-sign'       => 'exclamation-circle',
			'expand-alt'             => 'plus-square-o',
			'expand'                 => 'caret-square-o-right',
			'external-link-sign'     => 'external-link-square',
			'eye-close'              => 'eye-slash',
			'eye-open'               => 'eye',
			'facebook-sign'          => 'facebook-square',
			'facetime-video'         => 'video-camera',
			'file-alt'               => 'file-o',
			'file-text-alt'          => 'file-text-o',
			'flag-alt'               => 'flag-o',
			'folder-close-alt'       => 'folder-o',
			'folder-close'           => 'folder',
			'folder-open-alt'        => 'folder-open-o',
			'food'                   => 'cutlery',
			'frown'                  => 'frown-o',
			'fullscreen'             => 'arrows-alt',
			'github-sign'            => 'github-square',
			'group'                  => 'users',
			'h-sign'                 => 'h-square',
			'hand-down'              => 'hand-o-down',
			'hand-left'              => 'hand-o-left',
			'hand-right'             => 'hand-o-right',
			'hand-up'                => 'hand-o-up',
			'hdd'                    => 'hdd-o',
			'heart-empty'            => 'heart-o',
			'hospital'               => 'hospital-o',
			'indent-left'            => 'outdent',
			'indent-right'           => 'indent',
			'info-sign'              => 'info-circle',
			'keyboard'               => 'keyboard-o',
			'legal'                  => 'gavel',
			'lemon'                  => 'lemon-o',
			'lightbulb'              => 'lightbulb-o',
			'linkedin-sign'          => 'linkedin-square',
			'meh'                    => 'meh-o',
			'microphone-off'         => 'microphone-slash',
			'minus-sign-alt'         => 'minus-square',
			'minus-sign'             => 'minus-circle',
			'mobile-phone'           => 'mobile',
			'moon'                   => 'moon-o',
			'move'                   => 'arrows',
			'off'                    => 'power-off',
			'ok-circle'              => 'check-circle-o',
			'ok-sign'                => 'check-circle',
			'ok'                     => 'check',
			'paper-clip'             => 'paperclip',
			'paste'                  => 'clipboard',
			'phone-sign'             => 'phone-square',
			'picture'                => 'picture-o',
			'pinterest-sign'         => 'pinterest-square',
			'play-circle'            => 'play-circle-o',
			'play-sign'              => 'play-circle',
			'plus-sign-alt'          => 'plus-square',
			'plus-sign'              => 'plus-circle',
			'pushpin'                => 'thumb-tack',
			'question-sign'          => 'question-circle',
			'remove-circle'          => 'times-circle-o',
			'remove-sign'            => 'times-circle',
			'remove'                 => 'times',
			'reorder'                => 'bars',
			'resize-full'            => 'expand',
			'resize-horizontal'      => 'arrows-h',
			'resize-small'           => 'compress',
			'resize-vertical'        => 'arrows-v',
			'rss-sign'               => 'rss-square',
			'save'                   => 'floppy-o',
			'screenshot'             => 'crosshairs',
			'share-alt'              => 'share',
			'share-sign'             => 'share-square',
			'share'                  => 'share-square-o',
			'sign-blank'             => 'square',
			'signin'                 => 'sign-in',
			'signout'                => 'sign-out',
			'smile'                  => 'smile-o',
			'sort-by-alphabet-alt'   => 'sort-alpha-desc',
			'sort-by-alphabet'       => 'sort-alpha-asc',
			'sort-by-attributes-alt' => 'sort-amount-desc',
			'sort-by-attributes'     => 'sort-amount-asc',
			'sort-by-order-alt'      => 'sort-numeric-desc',
			'sort-by-order'          => 'sort-numeric-asc',
			'sort-down'              => 'sort-asc',
			'sort-up'                => 'sort-desc',
			'stackexchange'          => 'stack-overflow',
			'star-empty'             => 'star-o',
			'star-half-empty'        => 'star-half-o',
			'sun'                    => 'sun-o',
			'thumbs-down-alt'        => 'thumbs-o-down',
			'thumbs-up-alt'          => 'thumbs-o-up',
			'time'                   => 'clock-o',
			'trash'                  => 'trash-o',
			'tumblr-sign'            => 'tumblr-square',
			'twitter-sign'           => 'twitter-square',
			'unlink'                 => 'chain-broken',
			'upload'                 => 'arrow-circle-o-up',
			'upload-alt'             => 'upload',
			'warning-sign'           => 'exclamation-triangle',
			'xing-sign'              => 'xing-square',
			'youtube-sign'           => 'youtube-square',
			'zoom-in'                => 'search-plus',
			'zoom-out'               => 'search-minus',
		];

		return $icons;

	}

	/**
	 * Get an array of all standard fonts.
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function standard_fonts() {

		$standard_fonts = [
			'Arial, Helvetica, sans-serif'          => 'Arial, Helvetica, sans-serif',
			"'Arial Black', Gadget, sans-serif"     => "'Arial Black', Gadget, sans-serif",
			"'Bookman Old Style', serif"            => "'Bookman Old Style', serif",
			"'Comic Sans MS', cursive"              => "'Comic Sans MS', cursive",
			'Courier, monospace'                    => 'Courier, monospace',
			'Garamond, serif'                       => 'Garamond, serif',
			'Georgia, serif'                        => 'Georgia, serif',
			'Impact, Charcoal, sans-serif'          => 'Impact, Charcoal, sans-serif',
			"'Lucida Console', Monaco, monospace"   => "'Lucida Console', Monaco, monospace",
			"'Lucida Sans Unicode', 'Lucida Grande', sans-serif" => "'Lucida Sans Unicode', 'Lucida Grande', sans-serif",
			"'MS Sans Serif', Geneva, sans-serif"   => "'MS Sans Serif', Geneva, sans-serif",
			"'MS Serif', 'New York', sans-serif"    => "'MS Serif', 'New York', sans-serif",
			"'Palatino Linotype', 'Book Antiqua', Palatino, serif" => "'Palatino Linotype', 'Book Antiqua', Palatino, serif",
			'Tahoma, Geneva, sans-serif'            => 'Tahoma, Geneva, sans-serif',
			"'Times New Roman', Times, serif"       => "'Times New Roman', Times, serif",
			"'Trebuchet MS', Helvetica, sans-serif" => "'Trebuchet MS', Helvetica, sans-serif",
			'Verdana, Geneva, sans-serif'           => 'Verdana, Geneva, sans-serif',
		];

		return $standard_fonts;

	}

	/**
	 * Get an array of all font-weights.
	 *
	 * @static
	 * @access public
	 * @return array
	 */
	public static function font_weights() {

		$font_weights = [
			'100' => esc_html__( 'Thin (100)', 'Avada' ),
			'200' => esc_html__( 'Extra Light (200)', 'Avada' ),
			'300' => esc_html__( 'Light (300)', 'Avada' ),
			'400' => esc_html__( 'Normal (400)', 'Avada' ),
			'500' => esc_html__( 'Medium (500)', 'Avada' ),
			'600' => esc_html__( 'Semi Bold (600)', 'Avada' ),
			'700' => esc_html__( 'Bold (700)', 'Avada' ),
			'800' => esc_html__( 'Bolder (800)', 'Avada' ),
			'900' => esc_html__( 'Extra Bold (900)', 'Avada' ),
		];

		return $font_weights;

	}

	/**
	 * Get an array of all available font subsets for the Google Fonts API.
	 *
	 * @static
	 * @access  public
	 * @return  array
	 */
	public static function font_subsets() {
		// Deprecated in Avada 7.0 - The google-fonts API changed
		// and no longer requires subsets.
		return [];
	}

	/**
	 * Returns an array of colors to be used in color presets.
	 *
	 * @static
	 * @access public
	 * @since 1.0.0
	 * @param string $context The preset we want.
	 * @return array
	 */
	public static function color_theme( $context ) {
		$options = get_option( Fusion_Settings::get_option_name(), [] );

		$light                                    = [];
		$light['header_bg_color']                 = '#ffffff';
		$light['header_border_color']             = '#e5e5e5';
		$light['content_bg_color']                = '#ffffff';
		$light['slidingbar_bg_color']             = '#363839';
		$light['header_sticky_bg_color']          = '#ffffff';
		$light['footer_bg_color']                 = '#363839';
		$light['footer_border_color']             = '#e9eaee';
		$light['copyright_border_color']          = '#4B4C4D';
		$light['testimonial_bg_color']            = '#f6f3f3';
		$light['testimonial_text_color']          = '#747474';
		$light['sep_color']                       = '#e0dede';
		$light['slidingbar_divider_color']        = '#505152';
		$light['footer_divider_color']            = '#505152';
		$light['form_bg_color']                   = '#ffffff';
		$light['form_text_color']                 = '#aaa9a9';
		$light['form_border_color']               = '#d2d2d2';
		$light['tagline_font_color']              = '#747474';
		$light['page_title_color']                = '#333333';
		$light['h1_typography']                   = isset( $options['h1_typography'] ) ? $options['h1_typography'] : [];
		$light['h1_typography']['color']          = '#333333';
		$light['h2_typography']                   = isset( $options['h2_typography'] ) ? $options['h2_typography'] : [];
		$light['h2_typography']['color']          = '#333333';
		$light['h3_typography']                   = isset( $options['h3_typography'] ) ? $options['h3_typography'] : [];
		$light['h3_typography']['color']          = '#333333';
		$light['h4_typography']                   = isset( $options['h4_typography'] ) ? $options['h4_typography'] : [];
		$light['h4_typography']['color']          = '#333333';
		$light['h5_typography']                   = isset( $options['h5_typography'] ) ? $options['h5_typography'] : [];
		$light['h5_typography']['color']          = '#333333';
		$light['h6_typography']                   = isset( $options['h6_typography'] ) ? $options['h6_typography'] : [];
		$light['h6_typography']['color']          = '#333333';
		$light['body_typography']                 = isset( $options['body_typography'] ) ? $options['body_typography'] : [];
		$light['body_typography']['color']        = '#747474';
		$light['link_color']                      = '#333333';
		$light['menu_h45_bg_color']               = '#FFFFFF';
		$light['nav_typography']                  = isset( $options['nav_typography'] ) ? $options['nav_typography'] : [];
		$light['nav_typography']['color']         = '#333333';
		$light['menu_sub_bg_color']               = '#f2efef';
		$light['menu_sub_color']                  = '#333333';
		$light['menu_bg_hover_color']             = '#f8f8f8';
		$light['menu_sub_sep_color']              = '#dcdadb';
		$light['snav_color']                      = '#ffffff';
		$light['header_social_links_icon_color']  = '#ffffff';
		$light['header_top_first_border_color']   = '#e5e5e5';
		$light['header_top_sub_bg_color']         = '#ffffff';
		$light['header_top_menu_sub_color']       = '#747474';
		$light['header_top_menu_bg_hover_color']  = '#fafafa';
		$light['header_top_menu_sub_hover_color'] = '#333333';
		$light['header_top_menu_sub_sep_color']   = '#e5e5e5';
		$light['sidebar_bg_color']                = '#ffffff';
		$light['page_title_bg_color']             = '#F6F6F6';
		$light['page_title_border_color']         = '#d2d3d4';
		$light['breadcrumbs_text_color']          = '#333333';
		$light['sidebar_heading_color']           = '#333333';
		$light['accordian_inactive_color']        = '#333333';
		$light['counter_filled_color']            = '#65bc7b';
		$light['counter_unfilled_color']          = '#f6f6f6';
		$light['dates_box_color']                 = '#eef0f2';
		$light['carousel_nav_color']              = '#999999';
		$light['carousel_hover_color']            = '#808080';
		$light['content_box_bg_color']            = 'transparent';
		$light['title_border_color']              = '#e0dede';
		$light['icon_circle_color']               = '#333333';
		$light['icon_border_color']               = '#333333';
		$light['icon_color']                      = '#ffffff';
		$light['imgframe_border_color']           = '#f6f6f6';
		$light['imgframe_style_color']            = '#000000';
		$light['before_after_border_color']       = '#f6f6f6';
		$light['sep_pricing_box_heading_color']   = '#333333';
		$light['full_boxed_pricing_box_heading_color'] = '#333333';
		$light['pricing_bg_color']                     = '#ffffff';
		$light['pricing_border_color']                 = '#f8f8f8';
		$light['pricing_divider_color']                = '#ededed';
		$light['social_bg_color']                      = '#f6f6f6';
		$light['tabs_bg_color']                        = '#ffffff';
		$light['tabs_inactive_color']                  = '#f1f2f2';
		$light['tagline_bg']                           = '#f6f6f6';
		$light['tagline_border_color']                 = '#f6f6f6';
		$light['timeline_bg_color']                    = 'transparent';
		$light['timeline_color']                       = '#ebeaea';
		$light['woo_cart_bg_color']                    = '#fafafa';
		$light['qty_bg_color']                         = '#fbfaf9';
		$light['qty_bg_hover_color']                   = '#ffffff';
		$light['bbp_forum_header_bg']                  = '#ebeaea';
		$light['bbp_forum_border_color']               = '#ebeaea';
		$light['checklist_icons_color']                = '#ffffff';
		$light['flip_boxes_front_bg']                  = '#f6f6f6';
		$light['flip_boxes_front_heading']             = '#333333';
		$light['flip_boxes_front_text']                = '#747474';
		$light['full_width_bg_color']                  = '#ffffff';
		$light['full_width_border_color']              = '#eae9e9';
		$light['modal_bg_color']                       = '#f6f6f6';
		$light['modal_border_color']                   = '#ebebeb';
		$light['person_border_color']                  = '#f6f6f6';
		$light['popover_heading_bg_color']             = '#f6f6f6';
		$light['popover_content_bg_color']             = '#ffffff';
		$light['popover_border_color']                 = '#ebebeb';
		$light['popover_text_color']                   = '#747474';
		$light['progressbar_unfilled_color']           = '#f6f6f6';
		$light['section_sep_bg']                       = '#f6f6f6';
		$light['section_sep_border_color']             = '#f6f6f6';
		$light['sharing_box_tagline_text_color']       = '#333333';
		$light['header_social_links_icon_color']       = '#bebdbd';
		$light['header_social_links_box_color']        = '#e8e8e8';
		$light['bg_color']                             = '#d7d6d6';
		$light['mobile_menu_background_color']         = '#f9f9f9';
		$light['mobile_menu_border_color']             = '#dadada';
		$light['mobile_menu_hover_color']              = '#f6f6f6';
		$light['mobile_menu_typography']               = isset( $options['mobile_menu_typography'] ) ? $options['mobile_menu_typography'] : [];
		$light['mobile_menu_typography']['color']      = '#333333';
		$light['mobile_menu_font_hover_color']         = '#333333';
		$light['social_links_icon_color']              = '#bebdbd';
		$light['social_links_box_color']               = '#e8e8e8';
		$light['sharing_social_links_icon_color']      = '#bebdbd';
		$light['sharing_social_links_box_color']       = '#e8e8e8';
		$light['load_more_posts_button_bg_color']      = '#ebeaea';
		$light['ec_bar_bg_color']                      = '#efeded';
		$light['flyout_menu_icon_color']               = '#333333';
		$light['flyout_menu_background_color']         = 'rgba(255,255,255,0.95)';
		$light['ec_sidebar_bg_color']                  = '#f6f6f6';
		$light['ec_sidebar_link_color']                = '#333333';

		$dark                                    = [];
		$dark['header_bg_color']                 = '#29292a';
		$dark['header_border_color']             = '#3e3e3e';
		$dark['header_top_bg_color']             = '#29292a';
		$dark['content_bg_color']                = '#29292a';
		$dark['slidingbar_bg_color']             = '#363839';
		$dark['header_sticky_bg_color']          = '#29292a';
		$dark['slidingbar_border_color']         = '#484747';
		$dark['footer_bg_color']                 = '#2d2d2d';
		$dark['footer_border_color']             = '#403f3f';
		$dark['copyright_border_color']          = '#4B4C4D';
		$dark['testimonial_bg_color']            = '#3e3e3e';
		$dark['testimonial_text_color']          = '#aaa9a9';
		$dark['sep_color']                       = '#3e3e3e';
		$dark['slidingbar_divider_color']        = '#505152';
		$dark['footer_divider_color']            = '#505152';
		$dark['form_bg_color']                   = '#3e3e3e';
		$dark['form_text_color']                 = '#cccccc';
		$dark['form_border_color']               = '#212122';
		$dark['tagline_font_color']              = '#ffffff';
		$dark['page_title_color']                = '#ffffff';
		$dark['h1_typography']                   = isset( $options['h1_typography'] ) ? $options['h1_typography'] : [];
		$dark['h1_typography']['color']          = '#ffffff';
		$dark['h2_typography']                   = isset( $options['h2_typography'] ) ? $options['h2_typography'] : [];
		$dark['h2_typography']['color']          = '#ffffff';
		$dark['h3_typography']                   = isset( $options['h3_typography'] ) ? $options['h3_typography'] : [];
		$dark['h3_typography']['color']          = '#ffffff';
		$dark['h4_typography']                   = isset( $options['h4_typography'] ) ? $options['h4_typography'] : [];
		$dark['h4_typography']['color']          = '#ffffff';
		$dark['h5_typography']                   = isset( $options['h5_typography'] ) ? $options['h5_typography'] : [];
		$dark['h5_typography']['color']          = '#ffffff';
		$dark['h6_typography']                   = isset( $options['h6_typography'] ) ? $options['h6_typography'] : [];
		$dark['h6_typography']['color']          = '#ffffff';
		$dark['body_typography']                 = isset( $options['body_typography'] ) ? $options['body_typography'] : [];
		$dark['body_typography']['color']        = '#aaa9a9';
		$dark['link_color']                      = '#ffffff';
		$dark['menu_h45_bg_color']               = '#29292A';
		$dark['nav_typography']                  = isset( $options['nav_typography'] ) ? $options['nav_typography'] : [];
		$dark['nav_typography']['color']         = '#ffffff';
		$dark['menu_sub_bg_color']               = '#3e3e3e';
		$dark['menu_sub_color']                  = '#d6d6d6';
		$dark['menu_bg_hover_color']             = '#383838';
		$dark['menu_sub_sep_color']              = '#313030';
		$dark['snav_color']                      = '#747474';
		$dark['header_social_links_icon_color']  = '#747474';
		$dark['header_top_first_border_color']   = '#3e3e3e';
		$dark['header_top_sub_bg_color']         = '#29292a';
		$dark['header_top_menu_sub_color']       = '#d6d6d6';
		$dark['header_top_menu_bg_hover_color']  = '#333333';
		$dark['header_top_menu_sub_hover_color'] = '#d6d6d6';
		$dark['header_top_menu_sub_sep_color']   = '#3e3e3e';
		$dark['sidebar_bg_color']                = '#29292a';
		$dark['page_title_bg_color']             = '#353535';
		$dark['page_title_border_color']         = '#464646';
		$dark['breadcrumbs_text_color']          = '#ffffff';
		$dark['sidebar_heading_color']           = '#ffffff';
		$dark['accordian_inactive_color']        = '#3e3e3e';
		$dark['counter_filled_color']            = '#65bc7b';
		$dark['counter_unfilled_color']          = '#3e3e3e';
		$dark['dates_box_color']                 = '#3e3e3e';
		$dark['carousel_nav_color']              = '#3a3a3a';
		$dark['carousel_hover_color']            = '#333333';
		$dark['content_box_bg_color']            = 'transparent';
		$dark['title_border_color']              = '#3e3e3e';
		$dark['icon_circle_color']               = '#3e3e3e';
		$dark['icon_border_color']               = '#3e3e3e';
		$dark['icon_color']                      = '#ffffff';
		$dark['imgframe_border_color']           = '#494848';
		$dark['before_after_border_color']       = '#494848';
		$dark['imgframe_style_color']            = '#000000';
		$dark['sep_pricing_box_heading_color']   = '#ffffff';
		$dark['full_boxed_pricing_box_heading_color'] = '#AAA9A9';
		$dark['pricing_bg_color']                     = '#3e3e3e';
		$dark['pricing_border_color']                 = '#353535';
		$dark['pricing_divider_color']                = '#29292a';
		$dark['social_bg_color']                      = '#3e3e3e';
		$dark['tabs_bg_color']                        = '#3e3e3e';
		$dark['tabs_inactive_color']                  = '#313132';
		$dark['tagline_bg']                           = '#3e3e3e';
		$dark['tagline_border_color']                 = '#3e3e3e';
		$dark['timeline_bg_color']                    = 'transparent';
		$dark['timeline_color']                       = '#3e3e3e';
		$dark['woo_cart_bg_color']                    = '#333333';
		$dark['qty_bg_color']                         = '#29292a';
		$dark['qty_bg_hover_color']                   = '#383838';
		$dark['bbp_forum_header_bg']                  = '#383838';
		$dark['bbp_forum_border_color']               = '#212121';
		$dark['checklist_icons_color']                = '#ffffff';
		$dark['flip_boxes_front_bg']                  = '#3e3e3e';
		$dark['flip_boxes_front_heading']             = '#ffffff';
		$dark['flip_boxes_front_text']                = '#aaa9a9';
		$dark['full_width_bg_color']                  = '#242424';
		$dark['full_width_border_color']              = '#3e3e3e';
		$dark['modal_bg_color']                       = '#29292a';
		$dark['modal_border_color']                   = '#242424';
		$dark['person_border_color']                  = '#494848';
		$dark['popover_heading_bg_color']             = '#29292a';
		$dark['popover_content_bg_color']             = '#3e3e3e';
		$dark['popover_border_color']                 = '#242424';
		$dark['popover_text_color']                   = '#ffffff';
		$dark['progressbar_unfilled_color']           = '#3e3e3e';
		$dark['section_sep_bg']                       = '#3e3e3e';
		$dark['section_sep_border_color']             = '#3e3e3e';
		$dark['sharing_box_tagline_text_color']       = '#ffffff';
		$dark['header_social_links_icon_color']       = '#545455';
		$dark['header_social_links_box_color']        = '#383838';
		$dark['bg_color']                             = '#1e1e1e';
		$dark['mobile_menu_background_color']         = '#3e3e3e';
		$dark['mobile_menu_border_color']             = '#212122';
		$dark['mobile_menu_hover_color']              = '#383737';
		$dark['mobile_menu_typography']               = isset( $options['mobile_menu_typography'] ) ? $options['mobile_menu_typography'] : [];
		$dark['mobile_menu_typography']['color']      = '#ffffff';
		$dark['mobile_menu_font_hover_color']         = '#ffffff';
		$dark['social_links_icon_color']              = '#3e3e3e';
		$dark['social_links_box_color']               = '#383838';
		$dark['sharing_social_links_icon_color']      = '#919191';
		$dark['sharing_social_links_box_color']       = '#4b4e4f';
		$dark['load_more_posts_button_bg_color']      = '#3e3e3e';
		$dark['ec_bar_bg_color']                      = '#353535';
		$dark['flyout_menu_icon_color']               = '#ffffff';
		$dark['flyout_menu_background_color']         = 'rgba(0,0,0,0.85)';
		$dark['ec_sidebar_bg_color']                  = '#f6f6f6';
		$dark['ec_sidebar_link_color']                = '#ffffff';

		$colors = [
			'green'     => [
				'#92C563',
				'#D1E990',
				'#AAD75B',
				'#D1E990',
				'#AAD75B',
				'#AAD75B',
				'#D1E990',
				'#6e9a1f',
				'#638e1a',
				'#6e9a1f',
				'#638e1a',
				'#54770f',
				'#65bc7b',
			],

			'darkgreen' => [
				'#9db668',
				'#a5c462',
				'#cce890',
				'#afd65a',
				'#cce890',
				'#AAD75B',
				'#AAD75B',
				'#cce890',
				'#577810',
				'#cce890',
				'#577810',
				'#577810',
				'#577810',
			],

			'orange'    => [
				'#c4a362',
				'#e8cb90',
				'#d6ad5a',
				'#e8cb90',
				'#d6ad5a',
				'#d6ad5a',
				'#e8cb90',
				'#785510',
				'#785510',
				'#785510',
				'#785510',
				'#785510',
				'#e9a825',
			],


			'lightblue' => [
				'#62a2c4',
				'#90c9e8',
				'#5aabd6',
				'#90c9e8',
				'#5aabd6',
				'#5aabd6',
				'#90c9e8',
				'#105378',
				'#105378',
				'#105378',
				'#105378',
				'#105378',
				'#67b7e1',
			],

			'lightred'  => [
				'#c46262',
				'#e89090',
				'#d65a5a',
				'#e89090',
				'#d65a5a',
				'#d65a5a',
				'#e89090',
				'#781010',
				'#781010',
				'#781010',
				'#781010',
				'#781010',
				'#f05858',
			],

			'pink'      => [
				'#c46299',
				'#e890c2',
				'#d65aa0',
				'#e890c2',
				'#d65aa0',
				'#d65aa0',
				'#e890c2',
				'#78104b',
				'#78104b',
				'#78104b',
				'#78104b',
				'#78104b',
				'#e67fb9',
			],

			'lightgrey' => [
				'#c4c4c4',
				'#e8e8e8',
				'#d6d6d6',
				'#e8e8e8',
				'#d6d6d6',
				'#d6d6d6',
				'#e8e8e8',
				'#787878',
				'#787878',
				'#787878',
				'#787878',
				'#787878',
				'#9e9e9e',
			],

			'brown'     => [
				'#e8c090',
				'#d69e5a',
				'#e8c090',
				'#d69e5a',
				'#d69e5a',
				'#e8c090',
				'#784910',
				'#784910',
				'#784910',
				'#784910',
				'#784910',
				'#ab8b65',
			],

			'red'       => [
				'#c40606',
				'#e80707',
				'#d60707',
				'#e80707',
				'#d60707',
				'#d60707',
				'#e80707',
				'#780404',
				'#780404',
				'#780404',
				'#780404',
				'#780404',
				'#e10707',
			],

			'blue'      => [
				'#62a2c4',
				'#90c9e8',
				'#5aabd6',
				'#90c9e8',
				'#5aabd6',
				'#5aabd6',
				'#90c9e8',
				'#105378',
				'#105378',
				'#105378',
				'#105378',
				'#105378',
				'#1a80b6',
			],
		];

		$options = [
			'pricing_box_color',
			'image_gradient_top_color',
			'image_gradient_bottom_color',
			'button_gradient_top_color',
			'button_gradient_bottom_color',
			'button_gradient_top_color_hover',
			'button_gradient_bottom_color_hover',
			'button_accent_color',
			'button_accent_hover_color',
			'button_border_color',
			'button_border_hover_color',
			'button_bevel_color',
			'primary_color',
			'checklist_circle_color',
			'counter_box_color',
			'countdown_background_color',
			'dropcap_color',
			'flip_boxes_back_bg',
			'progressbar_filled_color',
			'counter_filled_color',
			'ec_sidebar_widget_bg_color',
			'menu_hover_first_color',
			'header_top_bg_color',
			'content_box_hover_animation_accent_color',
			'map_overlay_color',
			'flyout_menu_icon_hover_color',
			'menu_highlight_background',
			'menu_icon_hover_color',
			'logo_background_color',
			'slidingbar_link_color_hover',
			'footer_link_color_hover',
			'copyright_link_color_hover',
			'privacy_bar_link_hover_color',
			'faq_accordian_active_color',
			'accordian_active_color',
			'header_top_menu_sub_hover_color',
			'breadcrumbs_text_hover_color',
			'form_focus_border_color',
			'icon_circle_color_hover',
			'icon_border_color_hover',
		];

		foreach ( $colors as $color => $values ) {
			$$color = [];
			foreach ( $options as $key => $option ) {
				if ( isset( $values[ $key ] ) ) {
					${$color}[ $option ] = $values[ $key ];
				} else {
					// If $key is not set, this value needs to fallback to the primary color which is the last item in the array.
					${$color}[ $option ] = $values[ count( $values ) - 1 ];
				}
			}
		}

		if ( isset( $$context ) ) {
			return $$context;
		}
		return [];
	}
}

/* Omit closing PHP tag to avoid 'Headers already sent' issues. */
