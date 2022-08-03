<?php
/**
 * The template for displaying single Form Page
 *
 * @since 1.0.0
 */

?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<div id="wpforms-form-page-page">
	<div class="wpforms-form-page-wrap">
		<main class="wpforms-form-page-main" role="main">

			<?php do_action( 'wpforms_form_pages_content_before' ); ?>

			<?php wpforms_display( get_the_ID() ); ?>

			<?php do_action( 'wpforms_form_pages_content_after' ); ?>

		</main>
	</div>
	<div class="wpforms-form-page-footer">
		<?php do_action( 'wpforms_form_pages_footer' ); ?>
	</div>
</div>

<?php wp_footer(); ?>

</body>

</html>
