/**
 * NGS Design System - Toast Notifications
 * Accessible notification system with auto-dismiss
 * @version 1.0.0
 */

'use strict';

class NGSToast {
  constructor() {
    this.container = null;
    this.toasts = [];
    this.maxToasts = 3;
    this.idCounter = 0;
    this.init();
  }

  init() {
    // Create container if it doesn't exist
    if (!document.querySelector('.ngs-toast-container')) {
      this.container = document.createElement('div');
      this.container.className = 'ngs-toast-container';
      this.container.setAttribute('aria-live', 'assertive');
      this.container.setAttribute('aria-atomic', 'false');
      this.container.setAttribute('role', 'status');
      document.body.appendChild(this.container);
    } else {
      this.container = document.querySelector('.ngs-toast-container');
    }
  }

  /**
   * Show toast notification
   * @param {string} message - Toast message
   * @param {string} type - Toast type (success, error, warning, info)
   * @param {number} duration - Auto-dismiss duration in ms (0 = no auto-dismiss)
   * @returns {number} Toast ID
   */
  show(message, type = 'info', duration = 5000) {
    // Dismiss oldest if at max
    if (this.toasts.length >= this.maxToasts) {
      this.dismiss(this.toasts[0].id);
    }

    const id = ++this.idCounter;
    const toast = this.createToast(id, message, type);

    this.container.appendChild(toast);
    this.toasts.push({ id, element: toast });

    // Trigger animation
    requestAnimationFrame(() => {
      toast.classList.add('ngs-toast--show');
    });

    // Auto-dismiss
    if (duration > 0) {
      setTimeout(() => this.dismiss(id), duration);
    }

    return id;
  }

  /**
   * Create toast element
   */
  createToast(id, message, type) {
    const toast = document.createElement('div');
    toast.className = `ngs-toast ngs-toast--${type}`;
    toast.setAttribute('data-toast-id', id);
    toast.setAttribute('role', 'alert');

    const icon = this.getIcon(type);

    toast.innerHTML = `
      <div class="ngs-toast__content">
        <span class="ngs-toast__icon" aria-hidden="true">${icon}</span>
        <span class="ngs-toast__message">${this.escapeHtml(message)}</span>
      </div>
      <button class="ngs-toast__close" aria-label="Close notification">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
          <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </button>
    `;

    // Close button handler
    const closeBtn = toast.querySelector('.ngs-toast__close');
    closeBtn.addEventListener('click', () => this.dismiss(id));

    return toast;
  }

  /**
   * Dismiss toast by ID
   */
  dismiss(id) {
    const toastData = this.toasts.find(t => t.id === id);
    if (!toastData) return;

    const toast = toastData.element;

    // Slide out animation
    toast.classList.remove('ngs-toast--show');
    toast.classList.add('ngs-toast--hide');

    // Remove from DOM after animation
    setTimeout(() => {
      toast.remove();
      this.toasts = this.toasts.filter(t => t.id !== id);
    }, 300);
  }

  /**
   * Get icon for toast type
   */
  getIcon(type) {
    const icons = {
      success: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
        <path d="M16.667 5L7.5 14.167L3.333 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>`,
      error: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
        <path d="M10 18.333C14.602 18.333 18.333 14.602 18.333 10C18.333 5.398 14.602 1.667 10 1.667C5.398 1.667 1.667 5.398 1.667 10C1.667 14.602 5.398 18.333 10 18.333Z" stroke="currentColor" stroke-width="2"/>
        <path d="M10 6.667V10M10 13.333H10.008" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>`,
      warning: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
        <path d="M8.575 3.217L1.525 15.833C1.183 16.466 1.666 17.25 2.383 17.25H16.617C17.334 17.25 17.817 16.466 17.475 15.833L10.425 3.217C10.083 2.583 9.117 2.583 8.575 3.217Z" stroke="currentColor" stroke-width="2"/>
        <path d="M9.5 8.333V11.667M9.5 14.167H9.508" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>`,
      info: `<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
        <path d="M10 18.333C14.602 18.333 18.333 14.602 18.333 10C18.333 5.398 14.602 1.667 10 1.667C5.398 1.667 1.667 5.398 1.667 10C1.667 14.602 5.398 18.333 10 18.333Z" stroke="currentColor" stroke-width="2"/>
        <path d="M10 13.333V10M10 6.667H10.008" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>`
    };
    return icons[type] || icons.info;
  }

  /**
   * Escape HTML to prevent XSS
   */
  escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  /**
   * Dismiss all toasts
   */
  dismissAll() {
    [...this.toasts].forEach(toast => this.dismiss(toast.id));
  }
}

// Singleton instance
const toastInstance = new NGSToast();

/**
 * Show toast notification
 */
export function show(message, type = 'info', duration = 5000) {
  return toastInstance.show(message, type, duration);
}

/**
 * Dismiss toast by ID
 */
export function dismiss(id) {
  return toastInstance.dismiss(id);
}

/**
 * Dismiss all toasts
 */
export function dismissAll() {
  return toastInstance.dismissAll();
}

export default { show, dismiss, dismissAll };
