<?php
/**
 * Featured image template.
 *
 * @package Fusion-Library
 * @subpackage Templates
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
?>
<?php ob_start(); ?>
<?php $post_thumbnail_id = ( ! isset( $type ) || 'taxonomy' !== $type ) ? get_post_thumbnail_id( $post_id ) : false; ?>
<?php if ( 'related' === $type && 'fixed' === $post_featured_image_size && $post_thumbnail_id ) : ?>
	<?php
	/**
	 * Resize images for use as related posts.
	 */
	$image_args           = apply_filters(
		'fusion_related_posts_image_attr',
		[
			'width'  => '500',
			'height' => '383',
			'url'    => wp_get_attachment_url( $post_thumbnail_id ),
			'path'   => get_attached_file( $post_thumbnail_id ),
			'retina' => false,
			'id'     => $post_thumbnail_id,
		]
	);
	$image_args['retina'] = false;
	$image                = Fusion_Image_Resizer::image_resize( $image_args );

	$image_retina_args           = $image_args;
	$image_retina_args['retina'] = true;
	$image_retina                = Fusion_Image_Resizer::image_resize( $image_retina_args );
	$scrset                      = '';
	if ( isset( $image_retina['url'] ) && $image_retina['url'] ) {
		$scrset = ' srcset="' . esc_attr( $image['url'] . ' 1x, ' . $image_retina['url'] . ' 2x' ) . '"';
	}
	?>
	<img src="<?php echo esc_url_raw( $image['url'] ); ?>"<?php echo $scrset; // phpcs:ignore WordPress.Security.EscapeOutput ?> width="<?php echo absint( $image['width'] ); ?>" height="<?php echo absint( $image['height'] ); ?>" alt="<?php the_title_attribute( 'post=' . $post_id ); ?>" />

<?php else : ?>

	<?php if ( $post_thumbnail_id ) : ?>
		<?php
		/**
		 * Get the featured image if one is set.
		 */
		?>
		<?php echo get_the_post_thumbnail( $post_id, $post_featured_image_size ); ?>

	<?php elseif ( fusion_get_page_option( 'video', $post_id ) ) : ?>

		<?php
		$image_size_class .= ' fusion-video'

		/**
		 * Show the video if one is set.
		 */
		?>
		<div class="full-video">
			<?php echo apply_filters( 'privacy_iframe_embed', fusion_get_page_option( 'video', $post_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

	<?php elseif ( is_tax() ) : ?>
		<?php
			$image_id = fusion_get_term_image( 'id' );
		if ( $image_id ) {
			echo wp_get_attachment_image( $image_id, $post_featured_image_size );
		}
		?>

	<?php elseif ( $display_placeholder_image ) : ?>

		<?php
		/**
		 * The avada_placeholder_image hook.
		 *
		 * @hooked avada_render_placeholder_image - 10 (outputs the HTML for the placeholder image).
		 */
		?>
		<?php do_action( 'avada_placeholder_image', $post_featured_image_size ); ?>

	<?php endif; ?>

<?php endif; ?>

<?php
/**
 * Set the markup generated above as a variable.
 * Depending on the use case we'll be echoing this markup in a wrapper or followed by an action.
 */
$featured_image = ob_get_clean();
?>

<?php

$image_wrapper_attributes = '';

$attributes['class'] = ( isset( $attributes['class'] ) ) ? $attributes['class'] . ' fusion-image-wrapper' . $image_size_class : 'fusion-image-wrapper' . $image_size_class;

foreach ( $attributes as $key => $value ) {
	$image_wrapper_attributes .= ' ' . $key . '="' . esc_attr( $value ) . '"';
}
?>

<div <?php echo $image_wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-haspopup="true">
	<?php $enable_rollover = apply_filters( 'fusion_builder_image_rollover', true ); ?>

	<?php
	global $product;
	$is_product = ! is_null( $product ) && method_exists( $product, 'is_on_sale' ) && method_exists( $product, 'is_in_stock' );
	$woo_badges = '';

	ob_start();
	if ( $is_product && true === $display_woo_outofstock ) {
		get_template_part( 'templates/wc-product-loop-outofstock-flash' );
	}

	if ( $is_product && true === $display_woo_sale && function_exists( 'woocommerce_show_product_sale_flash' ) ) {
		woocommerce_show_product_sale_flash();
	}
	$woo_badges = ob_get_clean();

	if ( '' !== $woo_badges ) {
		echo '<div class="fusion-woo-badges-wrapper">' . $woo_badges . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	$object_title = 'taxonomy' === $type ? single_term_title( '', false ) : get_the_title();
	?>

	<?php if ( ( $enable_rollover && 'yes' === $display_rollover ) || 'force_yes' === $display_rollover ) : ?>

		<?php echo $featured_image; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php do_action( 'avada_rollover', $post_id, $post_permalink, $display_woo_price, $display_woo_buttons, $display_post_categories, $display_post_title, $gallery_id, $display_woo_rating ); ?>

	<?php else : ?>
		<?php
		// Set custom link.
		if ( true === $image_link ) {
			$link_icon_url  = apply_filters( 'fusion_builder_link_icon_url', '', $post_id );
			$post_permalink = '' !== $link_icon_url ? $link_icon_url : $post_permalink;

			$post_links_target = apply_filters( 'fusion_builder_post_links_target', '', $post_id );
			$link_target       = 'yes' === $post_links_target ? ' target="_blank"' : '';
		}
		?>

		<?php if ( true === $image_link ) : ?>
		<a href="<?php echo esc_url_raw( $post_permalink ); ?>"<?php echo $link_target; // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-label="<?php echo esc_attr( $object_title ); ?>">
		<?php endif; ?>

		<?php echo $featured_image; // phpcs:ignore WordPress.Security.EscapeOutput ?>

		<?php if ( true === $image_link ) : ?>
		</a>
		<?php endif; ?>

	<?php endif; ?>

</div>
