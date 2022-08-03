<?php
/**
 * Social Icons class.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      4.0.0
 */

/**
 * Social sharing handler.
 *
 * @since 4.0.0
 */
class Fusion_Social_Sharing extends Fusion_Social_Icon {

	/**
	 * Renders all social icons not belonging to shortcodes.
	 *
	 * @since 3.5.0
	 * @access public
	 * @param  array $args Holding all necessarry data for social icons.
	 * @return string  The HTML mark up for social icons, incl. wrapping container.
	 */
	public function render_social_icons( $args ) {

		parent::$args = $args;

		// Get a list of all the available social networks.
		$social_networks_full_array = Fusion_Data::fusion_social_icons( true, true );

		if ( isset( parent::$args['authorpage'] ) && 'yes' === parent::$args['authorpage'] ) {
			$social_networks = $this->get_authorpage_social_links_array( parent::$args );
		} else {
			$social_networks = $this->get_sharingbox_social_links_array( parent::$args );
		}

		$html  = '';
		$icons = '';

		$i = 0;

		if ( isset( parent::$args['authorpage'] ) && 'yes' === parent::$args['authorpage'] && isset( parent::$args['color_type'] ) ) {
			$per_icon_colors = ( 'brand' === parent::$args['color_type'] ) ? true : false;
		} else {
			$per_icon_colors = ( 'brand' === fusion_library()->get_option( 'sharing_social_links_color_type' ) ) ? true : false;
		}
		$number_of_social_networks = count( $social_networks );

		if ( ! empty( $social_networks ) ) {
			foreach ( $social_networks as $network => $icon_args ) {

				$icon_options = [
					'social_network' => $network,
					'social_link'    => $icon_args['url'],
				];

				if ( $per_icon_colors ) {
					$network_for_colors = str_replace( 'sharing_', '', $network );
					if ( parent::$args['icon_boxed'] ) {
						$icon_options['icon_color'] = '#ffffff';
						$icon_options['box_color']  = $social_networks_full_array[ $network_for_colors ]['color'];
					} else {
						$icon_options['icon_color'] = $social_networks_full_array[ $network_for_colors ]['color'];
						$icon_options['box_color']  = '#ffffff';
					}
				} else {
					$icon_options['icon_color'] = 'var(--sharing_social_links_icon_color)';
					$icon_options['box_color']  = 'var(--sharing_social_links_box_color)';
				}

				// Check if are on the last social icon;
				// $i needs to be incremented first to make it match the count() value.
				$i++;
				$icon_options['last'] = ( $i === $number_of_social_networks );

				$icons .= parent::get_markup( $icon_options );
			}
		}

		if ( ! empty( $icons ) ) {
			$attr = [
				'class' => 'fusion-social-networks',
			];
			if ( parent::$args['icon_boxed'] ) {
				$attr['class'] .= ' boxed-icons';
			}
			$html = '<div ' . fusion_attr( 'social-icons-class-social-networks', $attr ) . '><div ' . fusion_attr( 'fusion-social-networks-wrapper' ) . '>' . $icons;
			if ( isset( parent::$args['position'] ) && ( 'header' === parent::$args['position'] || 'footer' === parent::$args['position'] ) ) {
				$html .= '</div></div>';
			} else {
				$html .= '<div class="fusion-clearfix"></div></div></div>';
			}
		}

		return apply_filters( 'fusion_social_sharing_html', $html, $args );
	}

	/**
	 * Set up the array for sharing box social networks.
	 *
	 * @access public
	 * @since 3.5.0
	 * @param  array $args Holding all necessarry data for social icons.
	 * @return array  The social links array containing the social media and links to them.
	 */
	public function get_sharingbox_social_links_array( $args ) {
		$social_links_output = [];
		$social_networks     = fusion_library()->get_option( 'social_sharing' );
		$social_links_array  = [
			'facebook'  => 'https://www.facebook.com/sharer.php?u=' . rawurlencode( $args['link'] ) . '&t=' . rawurlencode( $args['title'] ),
			'twitter'   => 'https://twitter.com/share?text=' . rawurlencode( html_entity_decode( $args['title'], ENT_COMPAT, 'UTF-8' ) ) . '&url=' . rawurlencode( $args['link'] ),
			'linkedin'  => 'https://www.linkedin.com/shareArticle?mini=true&url=' . rawurlencode( $args['link'] ) . '&title=' . rawurlencode( $args['title'] ) . '&summary=' . rawurlencode( mb_substr( html_entity_decode( $args['description'], ENT_QUOTES, 'UTF-8' ), 0, 256 ) ),
			'reddit'    => 'http://reddit.com/submit?url=' . $args['link'] . '&amp;title=' . rawurlencode( $args['title'] ),
			'whatsapp'  => 'https://api.whatsapp.com/send?text=' . rawurlencode( $args['link'] ),
			'tumblr'    => 'http://www.tumblr.com/share/link?url=' . rawurlencode( $args['link'] ) . '&amp;name=' . rawurlencode( $args['title'] ) . '&amp;description=' . rawurlencode( $args['description'] ),
			'pinterest' => 'http://pinterest.com/pin/create/button/?url=' . rawurlencode( $args['link'] ) . '&amp;description=' . rawurlencode( $args['description'] ) . '&amp;media=' . rawurlencode( $args['pinterest_image'] ),
			'vk'        => 'http://vkontakte.ru/share.php?url=' . rawurlencode( $args['link'] ) . '&amp;title=' . rawurlencode( $args['title'] ) . '&amp;description=' . rawurlencode( $args['description'] ),
			'xing'      => 'https://www.xing.com/social_plugins/share/new?sc_p=xing-share&amp;h=1&amp;url=' . rawurlencode( $args['link'] ),
			'email'     => 'mailto:?subject=' . rawurlencode( $args['title'] ) . '&body=' . $args['link'],
		];      

		if ( is_array( $social_networks ) ) {
			foreach ( $social_networks as $index => $network ) {
				$social_links_output[ $network ] = [ 'url' => $social_links_array[ $network ] ];
			}
		}

		return $social_links_output;

	}

	/**
	 * Set up the array for author page social networks.
	 *
	 * @since 3.5.0
	 * @access public
	 * @param  array $args Holding all necessarry data for social icons.
	 * @return array  The social links array containing the social media and links to them.
	 */
	public function get_authorpage_social_links_array( $args ) {

		$social_links_array = [];

		if ( get_the_author_meta( 'author_facebook', $args['author_id'] ) ) {
			$social_links_array['facebook'] = [
				'url' => get_the_author_meta( 'author_facebook', $args['author_id'] ),
			];
		}

		if ( get_the_author_meta( 'author_twitter', $args['author_id'] ) ) {
			$social_links_array['twitter'] = [
				'url' => get_the_author_meta( 'author_twitter', $args['author_id'] ),
			];
		}

		if ( get_the_author_meta( 'author_linkedin', $args['author_id'] ) ) {
			$social_links_array['linkedin'] = [
				'url' => get_the_author_meta( 'author_linkedin', $args['author_id'] ),
			];
		}

		if ( get_the_author_meta( 'author_dribble', $args['author_id'] ) ) {
			$social_links_array['dribbble'] = [
				'url' => get_the_author_meta( 'author_dribble', $args['author_id'] ),
			];
		}

		if ( get_the_author_meta( 'author_whatsapp', $args['author_id'] ) ) {
			$social_links_array['whatsapp'] = [
				'url' => get_the_author_meta( 'author_whatsapp', $args['author_id'] ),
			];
		}

		if ( get_the_author_meta( 'author_email', $args['author_id'] ) ) {
			$social_links_array['email'] = [
				'url' => get_the_author_meta( 'author_email', $args['author_id'] ),
			];
		}

		return $social_links_array;
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
