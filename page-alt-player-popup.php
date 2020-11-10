<?php
/**
 * The template for displaying player popup page
 *
 * Template Name: Player popup
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

$name = campus_is_ajax() ? 'ajax' : null;
$adrotate_group = adrotate_group( get_option( 'popup_adrotate_group_id' ) );

get_header( $name ); ?>

<div class="wrap">

	<?php get_template_part( 'template-parts/page/title', 'page' ); ?>

	<div id="primary" <?php campus_content_classes( 'content-list-detail' ); ?>>
		<main id="main" class="site-main" role="main">

            <script type="text/javascript">
                (function($) {
                    $(document)
                        .on( 'ready ajaxready', function( event ) {
                            $('#main').append( $('.site-player .mejs-container') );
                            $( this ).off( 'ready ajaxready' );
                        } )
                        .on( 'ajaxloading', function( event ) {
                            $('.site-player').append( $('#main .mejs-container') );
                            $( this ).off( event );
                        } );
                })(jQuery);
            </script>

		</main><!-- #main -->

		<?php
		if( $adrotate_group && substr( (string) $adrotate_group, 0, 4 ) !== '<!--' ) :

			echo '<div class="popup-ad">';
				echo $adrotate_group;
			echo '</div>';

		endif;
		?>
	</div><!-- #primary -->

</div><!-- .wrap -->

<?php
campus_the_meta_links();

get_footer( $name );
