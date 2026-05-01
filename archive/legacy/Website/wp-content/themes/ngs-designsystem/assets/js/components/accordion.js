/**
 * NGS Design System - Accordion Component
 * Accessible FAQ accordion with keyboard navigation
 * @version 1.0.0
 */

'use strict';

class Accordion {
  constructor(element, options = {}) {
    this.element = element;
    this.options = {
      allowMultiple: options.allowMultiple ?? false,
      animationDuration: options.animationDuration ?? 300
    };
    this.items = [];
  }

  init() {
    if (!this.element) return;

    // Get all accordion items
    const itemElements = this.element.querySelectorAll('.ngs-accordion__item');

    itemElements.forEach((itemElement, index) => {
      const trigger = itemElement.querySelector('.ngs-accordion__trigger');
      const panel = itemElement.querySelector('.ngs-accordion__panel');

      if (!trigger || !panel) return;

      // Setup ARIA attributes
      const itemId = `accordion-item-${Date.now()}-${index}`;
      const triggerId = `${itemId}-trigger`;
      const panelId = `${itemId}-panel`;

      trigger.id = triggerId;
      panel.id = panelId;
      trigger.setAttribute('aria-controls', panelId);
      trigger.setAttribute('aria-expanded', 'false');
      panel.setAttribute('role', 'region');
      panel.setAttribute('aria-labelledby', triggerId);

      // Store item data
      this.items.push({
        element: itemElement,
        trigger,
        panel,
        isOpen: false
      });

      // Event listeners
      trigger.addEventListener('click', () => this.toggle(index));
      trigger.addEventListener('keydown', (e) => this.handleKeydown(e, index));
    });
  }

  /**
   * Toggle accordion item
   */
  toggle(index) {
    const item = this.items[index];
    if (!item) return;

    if (item.isOpen) {
      this.close(index);
    } else {
      // Close other items if not allowing multiple
      if (!this.options.allowMultiple) {
        this.items.forEach((otherItem, otherIndex) => {
          if (otherIndex !== index && otherItem.isOpen) {
            this.close(otherIndex);
          }
        });
      }
      this.open(index);
    }
  }

  /**
   * Open accordion item
   */
  open(index) {
    const item = this.items[index];
    if (!item || item.isOpen) return;

    const { element, trigger, panel } = item;

    // Update state
    item.isOpen = true;

    // Update classes
    element.classList.add('ngs-accordion__item--active');

    // Update ARIA
    trigger.setAttribute('aria-expanded', 'true');

    // Animate panel open
    this.animatePanel(panel, true);
  }

  /**
   * Close accordion item
   */
  close(index) {
    const item = this.items[index];
    if (!item || !item.isOpen) return;

    const { element, trigger, panel } = item;

    // Update state
    item.isOpen = false;

    // Update classes
    element.classList.remove('ngs-accordion__item--active');

    // Update ARIA
    trigger.setAttribute('aria-expanded', 'false');

    // Animate panel close
    this.animatePanel(panel, false);
  }

  /**
   * Animate panel open/close
   */
  animatePanel(panel, open) {
    if (open) {
      // Get full height
      panel.style.display = 'block';
      const height = panel.scrollHeight;
      panel.style.maxHeight = '0px';
      panel.style.overflow = 'hidden';

      // Force reflow
      void panel.offsetHeight;

      // Animate to full height
      panel.style.transition = `max-height ${this.options.animationDuration}ms ease-out`;
      panel.style.maxHeight = `${height}px`;

      // Remove max-height after animation
      setTimeout(() => {
        panel.style.maxHeight = 'none';
        panel.style.overflow = '';
      }, this.options.animationDuration);
    } else {
      // Set current height
      const height = panel.scrollHeight;
      panel.style.maxHeight = `${height}px`;
      panel.style.overflow = 'hidden';

      // Force reflow
      void panel.offsetHeight;

      // Animate to zero
      panel.style.transition = `max-height ${this.options.animationDuration}ms ease-in`;
      panel.style.maxHeight = '0px';

      // Hide after animation
      setTimeout(() => {
        panel.style.display = 'none';
        panel.style.maxHeight = '';
        panel.style.overflow = '';
      }, this.options.animationDuration);
    }
  }

  /**
   * Handle keyboard navigation
   */
  handleKeydown(e, index) {
    const item = this.items[index];
    if (!item) return;

    switch (e.key) {
      case 'Enter':
      case ' ':
        e.preventDefault();
        this.toggle(index);
        break;

      case 'ArrowDown':
        e.preventDefault();
        this.focusNext(index);
        break;

      case 'ArrowUp':
        e.preventDefault();
        this.focusPrevious(index);
        break;

      case 'Home':
        e.preventDefault();
        this.focusFirst();
        break;

      case 'End':
        e.preventDefault();
        this.focusLast();
        break;
    }
  }

  /**
   * Focus next item
   */
  focusNext(currentIndex) {
    const nextIndex = (currentIndex + 1) % this.items.length;
    this.items[nextIndex].trigger.focus();
  }

  /**
   * Focus previous item
   */
  focusPrevious(currentIndex) {
    const prevIndex = (currentIndex - 1 + this.items.length) % this.items.length;
    this.items[prevIndex].trigger.focus();
  }

  /**
   * Focus first item
   */
  focusFirst() {
    this.items[0].trigger.focus();
  }

  /**
   * Focus last item
   */
  focusLast() {
    this.items[this.items.length - 1].trigger.focus();
  }

  /**
   * Open all items
   */
  openAll() {
    this.items.forEach((item, index) => this.open(index));
  }

  /**
   * Close all items
   */
  closeAll() {
    this.items.forEach((item, index) => this.close(index));
  }

  /**
   * Destroy accordion
   */
  destroy() {
    this.items.forEach(({ trigger }) => {
      trigger.replaceWith(trigger.cloneNode(true));
    });
    this.items = [];
  }
}

/**
 * Initialize all accordions on page
 */
export function init() {
  const accordions = [];
  const accordionElements = document.querySelectorAll('.ngs-accordion');

  accordionElements.forEach(element => {
    const allowMultiple = element.dataset.allowMultiple === 'true';
    const accordion = new Accordion(element, { allowMultiple });
    accordion.init();
    accordions.push(accordion);
  });

  return accordions;
}

export { Accordion };
export default { init, Accordion };
