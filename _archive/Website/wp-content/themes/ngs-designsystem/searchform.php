<?php
/**
 * Search Form Template
 *
 * Template for displaying the search form.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$unique_id = wp_unique_id( 'search-form-' );
?>

<form role="search" method="get" class="ngs-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $unique_id ); ?>" class="ngs-sr-only">
		<?php esc_html_e( 'Search for:', 'ngs-designsystem' ); ?>
	</label>

	<div class="ngs-search-form__wrapper">
		<input
			type="search"
			id="<?php echo esc_attr( $unique_id ); ?>"
			class="ngs-search-form__input"
			placeholder="<?php esc_attr_e( 'Search products...', 'ngs-designsystem' ); ?>"
			value="<?php echo get_search_query(); ?>"
			name="s"
			autocomplete="off"
			aria-label="<?php esc_attr_e( 'Search products', 'ngs-designsystem' ); ?>"
		>

		<?php if ( function_exists( 'WC' ) ) : ?>
			<input type="hidden" name="post_type" value="product">
		<?php endif; ?>

		<button type="submit" class="ngs-search-form__submit" aria-label="<?php esc_attr_e( 'Search', 'ngs-designsystem' ); ?>">
			<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<span class="ngs-sr-only"><?php esc_html_e( 'Search', 'ngs-designsystem' ); ?></span>
		</button>
	</div>
</form>
