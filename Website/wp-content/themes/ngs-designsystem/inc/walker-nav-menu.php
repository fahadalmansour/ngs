<?php
/**
 * Custom Navigation Walker
 *
 * Custom walker for navigation menus with ARIA support and mega menu functionality.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Navigation Walker Class
 *
 * @since 1.0.0
 */
class NGS_Walker_Nav_Menu extends Walker_Nav_Menu {

	/**
	 * Start level element
	 *
	 * @since 1.0.0
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}

		$indent = str_repeat( $t, $depth );

		// Add custom classes
		$classes = array( 'ngs-submenu' );
		if ( 0 === $depth ) {
			$classes[] = 'ngs-submenu--dropdown';
		}

		$class_names = implode( ' ', $classes );

		$output .= "{$n}{$indent}<ul class=\"$class_names\">{$n}";
	}

	/**
	 * Start element
	 *
	 * @since 1.0.0
	 * @param string   $output            Used to append additional content (passed by reference).
	 * @param WP_Post  $item              Menu item data object.
	 * @param int      $depth             Depth of menu item. Used for padding.
	 * @param stdClass $args              An object of wp_nav_menu() arguments.
	 * @param int      $current_object_id ID of the current menu item.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $current_object_id = 0 ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}

		$indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

		// Build list item classes
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'ngs-menu-item';
		$classes[] = 'menu-item-' . $item->ID;

		if ( in_array( 'current-menu-item', $classes ) || in_array( 'current-menu-parent', $classes ) ) {
			$classes[] = 'ngs-menu-item--active';
		}

		if ( $args->walker->has_children ) {
			$classes[] = 'ngs-menu-item--has-children';
		}

		if ( 0 === $depth ) {
			$classes[] = 'ngs-menu-item--top-level';
		}

		$class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		// Build list item ID
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		// Start output
		$output .= $indent . '<li' . $id . $class_names . '>';

		// Build link attributes
		$atts = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target ) ? $item->target : '';
		$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
		$atts['href']   = ! empty( $item->url ) ? $item->url : '';
		$atts['class']  = 'ngs-menu-link';

		// Add ARIA attributes for items with children
		if ( $args->walker->has_children && 0 === $depth ) {
			$atts['aria-haspopup'] = 'true';
			$atts['aria-expanded'] = 'false';
		}

		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		// Build link content
		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$item_output  = $args->before;
		$item_output .= '<a' . $attributes . '>';
		$item_output .= '<span class="ngs-menu-link__text">';
		$item_output .= $args->link_before . $title . $args->link_after;
		$item_output .= '</span>';

		// Add chevron icon for items with children
		if ( $args->walker->has_children && 0 === $depth ) {
			$item_output .= '<span class="ngs-menu-link__icon" aria-hidden="true">';
			$item_output .= '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">';
			$item_output .= '<path d="M4 6L8 10L12 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
			$item_output .= '</svg>';
			$item_output .= '</span>';
		}

		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	/**
	 * Check if menu item has children
	 *
	 * @since 1.0.0
	 * @param array $elements Menu items
	 * @param int   $parent_id Parent menu item ID
	 * @return bool True if has children
	 */
	public function has_children_check( $elements, $parent_id = 0 ) {
		foreach ( $elements as $element ) {
			if ( isset( $element->menu_item_parent ) && $element->menu_item_parent == $parent_id ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Display array of elements hierarchically
	 *
	 * @since 1.0.0
	 * @param array $elements  An array of elements.
	 * @param int   $max_depth The maximum hierarchical depth.
	 * @return string The hierarchical item output.
	 */
	public function walk( $elements, $max_depth, ...$args ) {
		$output = '';

		// Store reference to elements for has_children check
		$this->db_fields = array(
			'parent' => 'menu_item_parent',
			'id'     => 'db_id',
		);

		// Check for children
		foreach ( $elements as $e ) {
			if ( $this->has_children_check( $elements, $e->ID ) ) {
				$this->has_children = true;
			}
		}

		return parent::walk( $elements, $max_depth, ...$args );
	}
}
