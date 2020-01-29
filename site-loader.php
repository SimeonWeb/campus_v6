<?php
/**
 * The template for displaying the site loader
 *
 * @package WordPress
 * @subpackage Radio_Campus_Angers
 * @since 5.3
 * @version 1.2
 */

?>
<div id="site-loader" class="site-loader">
	<div class="wrap">
	<?php
		$icon = campus_get_script_var( 'loader' );
		$message = 'Chargement...';
		$welcome_message = 'Bienvenue sur ' . get_bloginfo( 'name' );

		printf(
			'<div class="loader-content">
				<div class="loader-icon">%s</div>
				<header class="loader-title"><div class="message">%s</div><div class="welcome-message">%s</div></header>
			</div>',
			$icon,
			$message,
			$welcome_message
		);
	?>
	</div>
</div>
