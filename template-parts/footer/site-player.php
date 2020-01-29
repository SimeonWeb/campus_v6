<?php
/**
 * Displays footer site player
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 6.0
 * @version 1.0
 */

?>
<footer class="site-player">
	<!--[if lt IE 9]><script>document.createElement('audio');</script><![endif]-->
	<audio id="campus-player" preload="true" title="<?php bloginfo( 'name' ) ?>" type="audio/mpeg" src="<?php echo get_option( 'live_url' ); ?>" <?php echo ! empty( $_GET['autoplay'] ) ? ' autoplay="true"' : ''; ?>></audio>
</footer>
