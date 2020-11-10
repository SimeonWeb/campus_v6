<?php
/**
 * The template for displaying album playlist block
 *
 * Used for both single and index/archive.
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since Radio Campus Angers V5 1.0
 */

global $content_loop, $content_block_ad_index, $content_adrotate_group;

if( $content_block_ad_index == 0 ) {
    $content_adrotate_group = adrotate_group( get_option( 'content_square_adrotate_group_id' ) );
    if( ! $content_adrotate_group ||
        ( $content_adrotate_group == '<!-- Either there are no banners, they are disabled or none qualified for this location! -->' ||
          $content_adrotate_group == "<!-- Soit il n'y a pas de bannières, ils sont desactivées ou pas qualifiées pour cet endroit! -->" ) ) { // Merci adRotate de nous simplifier la vie !
        return;
    }
}

if( $content_loop > 0 && $content_loop % 13 == 0 ) {

    $content_block_ad_index++;

    // Duplicate block
    printf( '<article class="list-item type-block-ads type-block-ads-%s"><div class="list-item-container"><div class="post-thumbnail"><div class="wp-post-image">%s</div></div></div></article>',
        zeroise( $content_block_ad_index, 2 ),
        $content_adrotate_group
    );
}
