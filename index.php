<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Sparkle_WEDL_Sample_Theme_Updater
 */
?>

<html>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<title>Sparkle WEDL Sample Theme Updater</title>
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<article>
		<h1>Sparkle WEDL Sample Theme Updater</h1>
		<p>Licensing solution for your WordPress Themes</p>
	</article>
<?php wp_footer(); ?>
</body>
</html>