/**
 * NGS Design System - Navigation Components
 * Mobile navigation drawer and mega menu system
 * @version 1.0.0
 */

'use strict';

import NGSState from '../state.js';

/**
 * Mobile Navigation Drawer
 */
class MobileNav {
  constructor() {
    this.hamburger = document.querySelector('.ngs-hamburger');
    this.drawer = document.querySelector('.ngs-mobile-nav');
    this.backdrop = document.querySelector('.ngs-mobile-nav__backdrop');
    this.focusableElements = null;
    this.scrollPosition = 0;
  }

  init() {
    if (!this.hamburger || !this.drawer) return;

    this.focusableElements = this.drawer.querySelectorAll(
      'a[href], button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );

    this.hamburger.addEventListener('click', () => this.toggle());
    this.backdrop?.addEventListener('click', () => this.close());
    document.addEventListener('keydown', (e) => this.handleKeydown(e));

    // Subscribe to state changes
    NGSState.subscribe('ui', (ui) => {
      if (ui.mobileMenuOpen !== this.isOpen()) {
        ui.mobileMenuOpen ? this.open() : this.close();
      }
    });
  }

  toggle() {
    this.isOpen() ? this.close() : this.open();
  }

  open() {
    // Save current scroll position
    this.scrollPosition = window.pageYOffset;

    // Lock body scroll
    document.body.style.position = 'fixed';
    document.body.style.top = `-${this.scrollPosition}px`;
    document.body.style.width = '100%';
    document.body.classList.add('ngs-menu-open');

    // Update drawer
    this.drawer.classList.add('ngs-mobile-nav--open');
    this.hamburger.classList.add('ngs-hamburger--active');
    this.hamburger.setAttribute('aria-expanded', 'true');

    // Update state
    NGSState.update('ui', { mobileMenuOpen: true });

    // Focus first element in drawer
    setTimeout(() => {
      const firstFocusable = this.drawer.querySelector('a, button');
      firstFocusable?.focus();
    }, 300);

    // Setup focus trap
    this.setupFocusTrap();
  }

  close() {
    // Restore scroll position
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    document.body.classList.remove('ngs-menu-open');
    window.scrollTo(0, this.scrollPosition);

    // Update drawer
    this.drawer.classList.remove('ngs-mobile-nav--open');
    this.hamburger.classList.remove('ngs-hamburger--active');
    this.hamburger.setAttribute('aria-expanded', 'false');

    // Update state
    NGSState.update('ui', { mobileMenuOpen: false });

    // Return focus to hamburger
    this.hamburger.focus();
  }

  isOpen() {
    return this.drawer.classList.contains('ngs-mobile-nav--open');
  }

  setupFocusTrap() {
    const firstElement = this.focusableElements[0];
    const lastElement = this.focusableElements[this.focusableElements.length - 1];

    this.drawer.addEventListener('keydown', (e) => {
      if (e.key !== 'Tab') return;

      if (e.shiftKey) {
        // Shift + Tab
        if (document.activeElement === firstElement) {
          e.preventDefault();
          lastElement.focus();
        }
      } else {
        // Tab
        if (document.activeElement === lastElement) {
          e.preventDefault();
          firstElement.focus();
        }
      }
    });
  }

  handleKeydown(e) {
    if (e.key === 'Escape' && this.isOpen()) {
      this.close();
    }
  }
}

/**
 * Mega Menu Component
 */
class MegaMenu {
  constructor() {
    this.triggers = document.querySelectorAll('.ngs-nav__item--has-dropdown');
    this.activeDropdown = null;
    this.hoverTimeout = null;
  }

  init() {
    if (!this.triggers.length) return;

    this.triggers.forEach(trigger => {
      const link = trigger.querySelector('.ngs-nav__link');
      const dropdown = trigger.querySelector('.ngs-nav__dropdown');

      if (!link || !dropdown) return;

      // Desktop hover (with delay)
      trigger.addEventListener('mouseenter', () => {
        clearTimeout(this.hoverTimeout);
        this.hoverTimeout = setTimeout(() => {
          this.openDropdown(trigger, link, dropdown);
        }, 150);
      });

      trigger.addEventListener('mouseleave', () => {
        clearTimeout(this.hoverTimeout);
        this.hoverTimeout = setTimeout(() => {
          this.closeDropdown(trigger, link, dropdown);
        }, 300);
      });

      // Mobile/keyboard click
      link.addEventListener('click', (e) => {
        if (window.innerWidth < 1024) {
          e.preventDefault();
          this.toggleDropdown(trigger, link, dropdown);
        }
      });

      // Keyboard navigation
      link.addEventListener('keydown', (e) => {
        this.handleKeydown(e, trigger, link, dropdown);
      });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.ngs-nav__item--has-dropdown')) {
        this.closeAllDropdowns();
      }
    });
  }

  openDropdown(trigger, link, dropdown) {
    // Close other dropdowns
    this.closeAllDropdowns();

    trigger.classList.add('ngs-nav__item--active');
    dropdown.classList.add('ngs-nav__dropdown--open');
    link.setAttribute('aria-expanded', 'true');
    this.activeDropdown = { trigger, link, dropdown };
  }

  closeDropdown(trigger, link, dropdown) {
    trigger.classList.remove('ngs-nav__item--active');
    dropdown.classList.remove('ngs-nav__dropdown--open');
    link.setAttribute('aria-expanded', 'false');
    if (this.activeDropdown?.dropdown === dropdown) {
      this.activeDropdown = null;
    }
  }

  toggleDropdown(trigger, link, dropdown) {
    const isOpen = dropdown.classList.contains('ngs-nav__dropdown--open');
    if (isOpen) {
      this.closeDropdown(trigger, link, dropdown);
    } else {
      this.openDropdown(trigger, link, dropdown);
    }
  }

  closeAllDropdowns() {
    this.triggers.forEach(trigger => {
      const link = trigger.querySelector('.ngs-nav__link');
      const dropdown = trigger.querySelector('.ngs-nav__dropdown');
      this.closeDropdown(trigger, link, dropdown);
    });
  }

  handleKeydown(e, trigger, link, dropdown) {
    const isOpen = dropdown.classList.contains('ngs-nav__dropdown--open');

    switch(e.key) {
      case 'Enter':
      case ' ':
        e.preventDefault();
        this.toggleDropdown(trigger, link, dropdown);
        break;

      case 'Escape':
        if (isOpen) {
          e.preventDefault();
          this.closeDropdown(trigger, link, dropdown);
          link.focus();
        }
        break;

      case 'ArrowDown':
        if (isOpen) {
          e.preventDefault();
          const firstLink = dropdown.querySelector('a');
          firstLink?.focus();
        } else {
          e.preventDefault();
          this.openDropdown(trigger, link, dropdown);
        }
        break;

      case 'ArrowUp':
        if (isOpen) {
          e.preventDefault();
          this.closeDropdown(trigger, link, dropdown);
        }
        break;
    }
  }
}

/**
 * Initialize all navigation components
 */
export function init() {
  const mobileNav = new MobileNav();
  mobileNav.init();

  const megaMenu = new MegaMenu();
  megaMenu.init();

  return { mobileNav, megaMenu };
}

export default { init };
