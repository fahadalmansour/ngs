/**
 * NGS Design System - Animated Counter
 * Number counter animation for statistics
 * @version 1.0.0
 */

'use strict';

class AnimatedCounter {
  constructor(element, options = {}) {
    this.element = element;
    this.target = parseFloat(element.dataset.ngsCounter) || 0;
    this.suffix = element.dataset.ngsSuffix || '';
    this.prefix = element.dataset.ngsPrefix || '';
    this.duration = parseInt(element.dataset.ngsDuration) || 2000;
    this.decimals = parseInt(element.dataset.ngsDecimals) || 0;
    this.separator = element.dataset.ngsSeparator === 'true';
    this.options = options;
    this.observer = null;
    this.hasAnimated = false;
    this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  }

  init() {
    if (!this.element) return;

    if (this.prefersReducedMotion) {
      // Show final value immediately if user prefers reduced motion
      this.setDisplay(this.target);
      return;
    }

    // Observe element and animate when visible
    this.observer = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && !this.hasAnimated) {
            this.animate();
            this.hasAnimated = true;
            this.observer.unobserve(this.element);
          }
        });
      },
      { threshold: 0.5 }
    );

    this.observer.observe(this.element);
  }

  /**
   * Animate counter from 0 to target
   */
  animate() {
    const startTime = performance.now();
    const startValue = 0;

    const updateCounter = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / this.duration, 1);

      // Easing function (ease-out cubic)
      const easeProgress = 1 - Math.pow(1 - progress, 3);

      const currentValue = startValue + (this.target - startValue) * easeProgress;

      this.setDisplay(currentValue);

      if (progress < 1) {
        requestAnimationFrame(updateCounter);
      } else {
        this.setDisplay(this.target);
      }
    };

    requestAnimationFrame(updateCounter);
  }

  /**
   * Format and display number
   */
  setDisplay(value) {
    let displayValue;

    if (this.decimals > 0) {
      displayValue = value.toFixed(this.decimals);
    } else {
      displayValue = Math.floor(value).toString();
    }

    // Add thousand separators if enabled
    if (this.separator) {
      displayValue = this.addThousandSeparators(displayValue);
    }

    // Add prefix and suffix
    const fullText = this.prefix + displayValue + this.suffix;
    this.element.textContent = fullText;
  }

  /**
   * Add thousand separators to number
   */
  addThousandSeparators(numStr) {
    const parts = numStr.split('.');
    const integerPart = parts[0];
    const decimalPart = parts[1] ? '.' + parts[1] : '';

    // Determine separator based on language
    const isRTL = document.documentElement.dir === 'rtl';
    const separator = isRTL ? '،' : ',';

    const formatted = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, separator);
    return formatted + decimalPart;
  }

  /**
   * Reset counter
   */
  reset() {
    this.hasAnimated = false;
    this.setDisplay(0);
  }

  /**
   * Destroy counter
   */
  destroy() {
    if (this.observer) {
      this.observer.disconnect();
    }
  }
}

/**
 * Initialize all counters on page
 */
export function init() {
  const counters = [];
  const counterElements = document.querySelectorAll('[data-ngs-counter]');

  counterElements.forEach(element => {
    const counter = new AnimatedCounter(element);
    counter.init();
    counters.push(counter);
  });

  return counters;
}

export { AnimatedCounter };
export default { init, AnimatedCounter };
