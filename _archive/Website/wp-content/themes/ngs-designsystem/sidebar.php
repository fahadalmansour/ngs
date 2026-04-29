<?php
/**
 * Sidebar Template
 *
 * Template for displaying the sidebar widget area.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	return;
}
?>

<aside id="secondary" class="ngs-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Sidebar', 'ngs-designsystem' ); ?>">
	<?php dynamic_sidebar( 'sidebar-1' ); ?>
</aside>
