<?php
/**
 * Breadcrumbs Component
 *
 * Displays breadcrumb navigation using ngs_breadcrumbs() function.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'ngs_breadcrumbs' ) ) {
	ngs_breadcrumbs();
}
