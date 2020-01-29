<?php
/**
 * The template for displaying testimonials
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

/**
 * Core walker class used to create an HTML list of comments.
 *
 */
class Campus_Walker_Testimonials extends Walker_Comment {

    /**
     * Ends the element output, if needed.
     *
     * @since 2.7.0
     *
     * @see Walker::end_el()
     * @see wp_list_comments()
     *
     * @param string     $output  Used to append additional content. Passed by reference.
     * @param WP_Comment $comment The current comment object. Default current comment.
     * @param int        $depth   Optional. Depth of the current comment. Default 0.
     * @param array      $args    Optional. An array of arguments. Default empty array.
     */
    public function end_el( &$output, $comment, $depth = 0, $args = array() ) {
        $output .= '';
    }

	/**
	 * Outputs a comment in the HTML5 format.
	 *
	 * @since 3.6.0
	 *
	 * @see wp_list_comments()
	 *
	 * @param WP_Comment $comment Comment to display.
	 * @param int        $depth   Depth of the current comment.
	 * @param array      $args    An array of arguments.
	 */
	protected function html5_comment( $comment, $depth, $args ) {
		$tag = ( 'div' === $args['style'] ) ? 'div' : 'li';
		?>
	   	<article id="div-comment-<?php comment_ID(); ?>" class="list-item testimonial-body">

	       	<blockquote class="comment-content">
	           	<?php comment_text(); ?>
	       	</blockquote><!-- .comment-content -->

	       	<footer class="comment-meta">
	           	<div class="comment-author vcard">
					<span class="byline">
			            <?php
						   	printf( __( 'by %s', 'campus' ),
						   		sprintf( '<b class="fn">%s</b>', get_comment_author_link( $comment ) )
						   	);
			            ?>
					</span>
	           	</div><!-- .comment-author -->

	           	<div class="comment-metadata">
	               	<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
	                   	<time datetime="<?php comment_time( 'c' ); ?>">
	                       	<?php echo get_comment_date( '', $comment ); ?>
	                   	</time>
	               	</a>
	               	<?php edit_comment_link( __( 'Edit' ), '<span class="edit-link">', '</span>' ); ?>
	           	</div><!-- .comment-metadata -->

	           	<?php if ( '0' == $comment->comment_approved ) : ?>
	           	<p class="comment-awaiting-moderation"><?php _e( 'Your testimonial is awaiting moderation.', 'campus' ); ?></p>
	           	<?php endif; ?>
	       	</footer><!-- .comment-meta -->

	   	</article><!-- .comment-body -->
		<?php
	}
}
?>

<div id="testimonials" class="testimonials-area comments-area">

	<?php
	// You can start editing here -- including this comment!
	if ( have_comments() ) : ?>
		<!--<h2 class="comments-title">
			<?php
			$comments_number = get_comments_number();
			if ( '1' === $comments_number ) {
				/* translators: %s: post title */
				printf( _x( 'One Reply to &ldquo;%s&rdquo;', 'comments title', 'campus' ), get_the_title() );
			} else {
				printf(
					/* translators: 1: number of comments, 2: post title */
					_nx(
						'%1$s Reply to &ldquo;%2$s&rdquo;',
						'%1$s Replies to &ldquo;%2$s&rdquo;',
						$comments_number,
						'comments title',
						'campus'
					),
					number_format_i18n( $comments_number ),
					get_the_title()
				);
			}
			?>
		</h2>-->

		<section class="term-list testimonial-list">
			<?php
				wp_list_comments( array(
					'walker'	  		=> new Campus_Walker_Testimonials(),
					'reverse_top_level' => true,
				) );
			?>
		</section>

		<?php the_comments_pagination( array(
			'prev_text' => campus_get_svg( array( 'icon' => 'arrow-left' ) ) . '<span class="screen-reader-text">' . __( 'Previous', 'campus' ) . '</span>',
			'next_text' => '<span class="screen-reader-text">' . __( 'Next', 'campus' ) . '</span>' . campus_get_svg( array( 'icon' => 'arrow-right' ) ),
		) );

	endif; // Check for have_comments().

	// If comments are closed and there are comments, let's leave a little note, shall we?
	if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>

		<p class="no-comments"><?php _e( 'Testimonials are closed.', 'campus' ); ?></p>
	<?php
	endif;
	?>

</div><!-- #comments -->
