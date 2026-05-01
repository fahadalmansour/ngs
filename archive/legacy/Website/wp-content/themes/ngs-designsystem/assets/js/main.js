/**
 * NGS Design System - Main Entry Point
 * Initializes all theme modules based on page context
 * @version 1.0.0
 */

'use strict';

import * as Animations from './animations.js';
import * as Navigation from './components/navigation.js';
import * as Search from './components/search.js';
import * as Toast from './components/toast.js';
import * as Filters from './components/filters.js';
import * as Gallery from './components/gallery.js';
import * as Cart from './components/cart.js';
import * as Forms from './components/forms.js';
import * as Accordion from './components/accordion.js';
import * as Counter from './components/counter.js';

class NGSDesignSystem {
  constructor() {
    this.version = '1.0.0';
    this.modules = new Map();
    this.isDebug = window.location.search.includes('ngs_debug');
  }

  /**
   * Initialize design system
   */
  init() {
    this.log('Initializing NGS Design System...');

    // Core modules (always initialize)
    this.initCoreModules();

    // Page-specific modules (conditional initialization)
    this.initPageModules();

    this.log(`NGS Design System v${this.version} initialized`);
  }

  /**
   * Initialize core modules
   */
  initCoreModules() {
    // Scroll animations
    if (document.querySelectorAll('[data-ngs-animate]').length > 0) {
      this.modules.set('animations', Animations.init());
      this.log('Animations module loaded');
    }

    // Navigation
    if (document.querySelector('.ngs-hamburger') || document.querySelector('.ngs-nav__item--has-dropdown')) {
      this.modules.set('navigation', Navigation.init());
      this.log('Navigation module loaded');
    }

    // Search overlay
    if (document.querySelector('.ngs-search-trigger')) {
      this.modules.set('search', Search.init());
      this.log('Search module loaded');
    }

    // Toast notifications (make globally available)
    window.NGSToast = Toast;
    this.log('Toast module loaded');
  }

  /**
   * Initialize page-specific modules
   */
  initPageModules() {
    const body = document.body;

    // Product single page - Gallery
    if (body.classList.contains('single-product') && document.querySelector('.ngs-product-gallery')) {
      this.modules.set('gallery', Gallery.init());
      this.log('Gallery module loaded');
    }

    // Shop/Archive pages - Filters
    if (
      (body.classList.contains('woocommerce-page') || body.classList.contains('archive')) &&
      document.querySelector('.ngs-filters-form')
    ) {
      this.modules.set('filters', Filters.init());
      this.log('Filters module loaded');
    }

    // Cart functionality (all pages with add-to-cart buttons)
    if (document.querySelectorAll('.ngs-btn-add-to-cart').length > 0) {
      this.modules.set('cart', Cart.init());
      this.log('Cart module loaded');
    }

    // FAQ Accordion
    if (document.querySelector('.ngs-accordion')) {
      this.modules.set('accordion', Accordion.init());
      this.log('Accordion module loaded');
    }

    // Animated counters
    if (document.querySelectorAll('[data-ngs-counter]').length > 0) {
      this.modules.set('counter', Counter.init());
      this.log('Counter module loaded');
    }

    // Form validation
    this.initForms();
  }

  /**
   * Initialize form validation
   */
  initForms() {
    const forms = document.querySelectorAll('.ngs-form[data-validate]');

    if (forms.length === 0) return;

    const validators = [];

    forms.forEach(form => {
      const formType = form.dataset.validate;
      let rules = null;

      // Get validation rules based on form type
      switch(formType) {
        case 'contact':
          rules = Forms.validationPresets.contactForm;
          break;
        case 'checkout':
          rules = Forms.validationPresets.checkoutForm;
          break;
        case 'registration':
          rules = Forms.validationPresets.registrationForm;
          break;
        default:
          this.log(`Unknown form type: ${formType}`);
          return;
      }

      if (rules) {
        const validator = new Forms.FormValidator(form, rules);
        validator.init();
        validators.push(validator);
        this.log(`Form validation loaded for: ${formType}`);
      }
    });

    if (validators.length > 0) {
      this.modules.set('forms', validators);
    }
  }

  /**
   * Log message (only in debug mode)
   */
  log(message) {
    if (this.isDebug) {
      console.log(`[NGS] ${message}`);
    }
  }

  /**
   * Get module instance
   */
  getModule(name) {
    return this.modules.get(name);
  }

  /**
   * Check if module is loaded
   */
  hasModule(name) {
    return this.modules.has(name);
  }

  /**
   * Destroy all modules
   */
  destroy() {
    this.modules.forEach((module, name) => {
      if (module && typeof module.destroy === 'function') {
        module.destroy();
        this.log(`Destroyed module: ${name}`);
      }
    });
    this.modules.clear();
  }
}

/**
 * Initialize when DOM is ready
 */
function initWhenReady() {
  const ngs = new NGSDesignSystem();
  ngs.init();

  // Make globally available for debugging
  window.NGS = ngs;

  // Expose FormValidator for custom forms
  window.NGSFormValidator = Forms.FormValidator;

  return ngs;
}

// Auto-initialize
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initWhenReady);
} else {
  initWhenReady();
}

export default NGSDesignSystem;
