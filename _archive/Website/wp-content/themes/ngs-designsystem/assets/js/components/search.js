/**
 * NGS Design System - Search Overlay
 * Full-screen search with focus management
 * @version 1.0.0
 */

'use strict';

import NGSState from '../state.js';

class SearchOverlay {
  constructor() {
    this.trigger = document.querySelector('.ngs-search-trigger');
    this.overlay = document.querySelector('.ngs-search-overlay');
    this.closeBtn = document.querySelector('.ngs-search-overlay__close');
    this.backdrop = document.querySelector('.ngs-search-overlay__backdrop');
    this.input = document.querySelector('.ngs-search-overlay__input');
    this.focusableElements = null;
    this.previousFocus = null;
  }

  init() {
    if (!this.trigger || !this.overlay) return;

    this.focusableElements = this.overlay.querySelectorAll(
      'input, button, [tabindex]:not([tabindex="-1"])'
    );

    // Event listeners
    this.trigger.addEventListener('click', () => this.open());
    this.closeBtn?.addEventListener('click', () => this.close());
    this.backdrop?.addEventListener('click', () => this.close());
    document.addEventListener('keydown', (e) => this.handleKeydown(e));

    // Subscribe to state changes
    NGSState.subscribe('ui', (ui) => {
      if (ui.searchOpen !== this.isOpen()) {
        ui.searchOpen ? this.open() : this.close();
      }
    });
  }

  open() {
    if (this.isOpen()) return;

    // Save current focus
    this.previousFocus = document.activeElement;

    // Show overlay
    this.overlay.classList.add('ngs-search-overlay--open');
    document.body.classList.add('ngs-search-open');

    // Update state
    NGSState.update('ui', { searchOpen: true });

    // Focus input after animation
    setTimeout(() => {
      this.input?.focus();
    }, 300);

    // Setup focus trap
    this.setupFocusTrap();
  }

  close() {
    if (!this.isOpen()) return;

    // Hide overlay
    this.overlay.classList.remove('ngs-search-overlay--open');
    document.body.classList.remove('ngs-search-open');

    // Update state
    NGSState.update('ui', { searchOpen: false });

    // Clear input
    if (this.input) {
      this.input.value = '';
    }

    // Return focus to trigger
    if (this.previousFocus) {
      this.previousFocus.focus();
      this.previousFocus = null;
    }
  }

  isOpen() {
    return this.overlay.classList.contains('ngs-search-overlay--open');
  }

  setupFocusTrap() {
    const firstElement = this.focusableElements[0];
    const lastElement = this.focusableElements[this.focusableElements.length - 1];

    this.overlay.addEventListener('keydown', (e) => {
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

    // Keyboard shortcut: Ctrl/Cmd + K
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      this.isOpen() ? this.close() : this.open();
    }
  }
}

/**
 * Initialize search overlay
 */
export function init() {
  const search = new SearchOverlay();
  search.init();
  return search;
}

export default { init };
