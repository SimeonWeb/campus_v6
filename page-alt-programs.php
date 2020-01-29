<?php
/**
 * The template for displaying programs table
 *
 * Template Name: Grille des programmes
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

$name = campus_is_ajax() ? 'ajax' : null;

get_header( $name ); ?>

<div class="wrap">

	<header class="fixed-header">
		<hgroup class="fixed-header-group">
			<h1 class="title-hierarchical-prefix"><?php the_title(); ?></h1>
		</hgroup>
	</header><!-- .fixed-header -->

	<div id="primary" <?php campus_content_classes(); ?>>
		
		<main id="main" class="site-main" role="main">

			<?php
				RCA_CAL()->set_calendar( 'program' );
				RCA_CAL()->set_view( 'front_grid' );
				RCA_CAL()->get_calendar();
			?>

		</main><!-- #main -->

		<?php campus_this_is_the_end(); ?>

	</div><!-- #primary -->
</div><!-- .wrap -->

<?php
campus_the_meta_links();

get_footer( $name );
