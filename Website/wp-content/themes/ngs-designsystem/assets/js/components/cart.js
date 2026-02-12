/**
 * NGS Design System - Cart Management
 * AJAX add-to-cart with animations and notifications
 * @version 1.0.0
 */

'use strict';

import NGSState from '../state.js';
import { show as showToast } from './toast.js';

class CartManager {
  constructor() {
    this.addToCartButtons = document.querySelectorAll('.ngs-btn-add-to-cart');
    this.cartBadge = document.querySelector('.ngs-cart-badge');
    this.liveRegion = null;
    this.buttonStates = new Map();
  }

  init() {
    if (!this.addToCartButtons.length) return;

    // Create screen reader live region
    this.createLiveRegion();

    // Setup button handlers
    this.addToCartButtons.forEach(button => {
      button.addEventListener('click', (e) => this.handleAddToCart(e, button));
    });

    // Initialize cart count from WooCommerce
    this.initCartCount();
  }

  /**
   * Create screen reader live region
   */
  createLiveRegion() {
    this.liveRegion = document.createElement('div');
    this.liveRegion.className = 'sr-only';
    this.liveRegion.setAttribute('aria-live', 'polite');
    this.liveRegion.setAttribute('aria-atomic', 'true');
    document.body.appendChild(this.liveRegion);
  }

  /**
   * Initialize cart count from existing badge or WooCommerce
   */
  initCartCount() {
    if (!this.cartBadge) return;

    const currentCount = parseInt(this.cartBadge.textContent) || 0;
    NGSState.update('cart', { count: currentCount });
  }

  /**
   * Handle add to cart click
   */
  async handleAddToCart(e, button) {
    e.preventDefault();

    // Prevent multiple clicks
    if (this.buttonStates.get(button) === 'loading') return;

    const productId = button.dataset.productId;
    const quantity = parseInt(button.dataset.quantity) || 1;

    if (!productId) {
      console.error('Product ID not found');
      return;
    }

    try {
      // Set loading state
      this.setButtonState(button, 'loading');

      // Make AJAX request
      const response = await this.addToCartRequest(productId, quantity);

      if (response.error) {
        throw new Error(response.error);
      }

      // Success
      this.setButtonState(button, 'success');
      this.updateCartCount(response.cart_count || (NGSState.get('cart').count + quantity));
      this.announceToScreenReader(response.message || 'تمت إضافة المنتج إلى السلة');
      showToast(response.message || 'تمت إضافة المنتج إلى السلة بنجاح', 'success');

      // Trigger WooCommerce fragment refresh if jQuery available
      if (window.jQuery) {
        window.jQuery(document.body).trigger('wc_fragment_refresh');
      }

      // Reset button after 2 seconds
      setTimeout(() => {
        this.setButtonState(button, 'default');
      }, 2000);

    } catch (error) {
      console.error('Add to cart error:', error);
      this.setButtonState(button, 'error');
      showToast(error.message || 'فشل إضافة المنتج إلى السلة. يرجى المحاولة مرة أخرى.', 'error');

      // Reset button after 2 seconds
      setTimeout(() => {
        this.setButtonState(button, 'default');
      }, 2000);
    }
  }

  /**
   * Make add to cart AJAX request
   */
  async addToCartRequest(productId, quantity) {
    const formData = new FormData();
    formData.append('action', 'ngs_add_to_cart');
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('nonce', window.ngs_ajax?.nonce || '');

    const response = await fetch(window.ngs_ajax?.ajax_url || '/wp-admin/admin-ajax.php', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      throw new Error('Network response was not ok');
    }

    const data = await response.json();

    if (!data.success) {
      throw new Error(data.data?.message || 'Add to cart failed');
    }

    return data.data;
  }

  /**
   * Set button state
   */
  setButtonState(button, state) {
    this.buttonStates.set(button, state);
    button.setAttribute('data-state', state);

    const isRTL = document.documentElement.dir === 'rtl';
    const texts = {
      default: isRTL ? 'أضف إلى السلة' : 'Add to Cart',
      loading: isRTL ? 'جاري الإضافة...' : 'Adding...',
      success: isRTL ? 'تمت الإضافة ✓' : 'Added ✓',
      error: isRTL ? 'حدث خطأ' : 'Error'
    };

    const textElement = button.querySelector('.ngs-btn__text');
    if (textElement) {
      textElement.textContent = texts[state];
    }

    // Disable/enable button
    button.disabled = (state === 'loading');

    // Update aria-label
    button.setAttribute('aria-label', texts[state]);
  }

  /**
   * Update cart badge count
   */
  updateCartCount(count) {
    if (!this.cartBadge) return;

    const displayCount = count > 9 ? '9+' : count.toString();
    this.cartBadge.textContent = displayCount;
    this.cartBadge.setAttribute('aria-label', `${count} items in cart`);

    // Trigger pop animation
    this.cartBadge.classList.remove('ngs-badge--pop');
    // Force reflow
    void this.cartBadge.offsetWidth;
    this.cartBadge.classList.add('ngs-badge--pop');

    // Remove animation class after animation completes
    setTimeout(() => {
      this.cartBadge.classList.remove('ngs-badge--pop');
    }, 500);

    // Update state
    NGSState.update('cart', { count });

    // Show/hide badge based on count
    if (count > 0) {
      this.cartBadge.classList.add('ngs-badge--visible');
    } else {
      this.cartBadge.classList.remove('ngs-badge--visible');
    }
  }

  /**
   * Announce to screen readers
   */
  announceToScreenReader(message) {
    if (!this.liveRegion) return;

    const cart = NGSState.get('cart');
    const isRTL = document.documentElement.dir === 'rtl';
    const announcement = isRTL
      ? `${message}. السلة الآن تحتوي على ${cart.count} منتج`
      : `${message}. Cart now has ${cart.count} items.`;

    this.liveRegion.textContent = announcement;

    // Clear after announcement
    setTimeout(() => {
      this.liveRegion.textContent = '';
    }, 1000);
  }
}

/**
 * Initialize cart manager
 */
export function init() {
  const cart = new CartManager();
  cart.init();
  return cart;
}

export default { init };
