<?php
/**
 * Template part for displaying term list item
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

$term = get_queried_object();
$term_social_links = campus_get_term_social_links( $term->term_id, $term->taxonomy );
?>

<article id="term-<?php echo $term->term_id; ?>" <?php campus_taxonomy_classes( 'list-item' ); ?>>

	<div class="list-item-container">

    <header class="taxonomy-header">

        <div class="taxonomy-thumbnail">
            <a href="<?php echo get_term_link( $term ); ?>">
                <?php echo campus_get_category_thumbnail( array( 'term_id' => $term->term_id, 'taxonomy' => $term->taxonomy, 'size' => array(600,600) ) ); ?>
            </a>
        </div><!-- .post-thumbnail -->

        <div class="taxonomy-content">

            <div class="taxonomy-title" title="<?php echo esc_attr( $term->name ); ?>">
                <?php echo $term->name; ?>
            </div>
            <?php campus_the_term_meta( 'secondary_description', '<p class="taxonomy-secondary-description">', '</p>' ); ?>
            <?php campus_the_term_meta( 'type', '<p class="taxonomy-secondary-description">', '</p>' ); ?>

            <div class="taxonomy-schedules">
                <?php campus_the_term_meta( 'day', '<p class="taxonomy-schedules-day">', '</p>' ); ?>
                <?php campus_the_term_meta( 'hours', '<p class="taxonomy-schedules-hours">', '</p>', ' > ' ); ?>
            </div>

            <?php if( $term->description ) : ?>
                <div class="taxonomy-description">
                    <?php echo wpautop( $term->description ); ?>
                </div>
            <?php endif; ?>

        </div>

        <?php if( $term_social_links ) : ?>
            <aside class="taxonomy-aside content-meta-links meta-links">
                <?php echo $term_social_links; ?>
            </aside>
            <a href="#" class="taxonomy-open-aside"><?php echo campus_get_svg( array( 'icon' => 'arrow-left', 'class' => 'icon-small' ) ); ?></a>
        <?php endif; ?>

		</header>
		
	</div>

</article><!-- #post-## -->
