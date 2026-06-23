<?php
/**
 * Template Name: Download PDF
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
    <html>
<head>
    <title><?php the_title();?></title>
</head>
<body onload="window.print()">
      <?php the_content();?>
    </body>
    </html>
  
    
