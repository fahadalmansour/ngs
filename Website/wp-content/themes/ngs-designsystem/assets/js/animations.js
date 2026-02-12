/**
 * NGS Design System - Scroll Animations
 * IntersectionObserver-based animation system with accessibility support
 * @version 1.0.0
 */

'use strict';

class NGSAnimations {
  constructor() {
    this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    this.observer = null;
  }

  /**
   * Initialize scroll animations
   */
  init() {
    if (this.prefersReducedMotion) {
      // Show all elements immediately if user prefers reduced motion
      document.querySelectorAll('[data-ngs-animate]').forEach(el => {
        el.classList.add('ngs-animated');
      });
      return;
    }

    this.observer = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            this.animateElement(entry.target);
            this.observer.unobserve(entry.target); // One-shot animation
          }
        });
      },
      {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
      }
    );

    // Observe all animation targets
    document.querySelectorAll('[data-ngs-animate]').forEach(el => {
      this.observer.observe(el);
    });

    // Initialize counters
    this.initCounters();
  }

  /**
   * Animate element with optional delay
   */
  animateElement(element) {
    const delay = parseInt(element.dataset.ngsDelay) || 0;

    setTimeout(() => {
      element.classList.add('ngs-animated');

      // Trigger counter animation if present
      if (element.dataset.ngsCounter) {
        this.animateCounter(element);
      }
    }, delay);
  }

  /**
   * Initialize counter animations
   */
  initCounters() {
    if (this.prefersReducedMotion) {
      // Show final values immediately
      document.querySelectorAll('[data-ngs-counter]').forEach(el => {
        el.textContent = el.dataset.ngsCounter + (el.dataset.ngsSuffix || '');
      });
      return;
    }

    const counterObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            this.animateCounter(entry.target);
            counterObserver.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.5 }
    );

    document.querySelectorAll('[data-ngs-counter]').forEach(el => {
      counterObserver.observe(el);
    });
  }

  /**
   * Animate counter from 0 to target value
   */
  animateCounter(element) {
    const target = parseFloat(element.dataset.ngsCounter);
    const suffix = element.dataset.ngsSuffix || '';
    const duration = 2000; // 2 seconds
    const fps = 60;
    const frames = duration / (1000 / fps);
    const increment = target / frames;
    let current = 0;
    let frame = 0;

    const updateCounter = () => {
      frame++;
      current += increment;

      if (frame < frames) {
        element.textContent = Math.floor(current) + suffix;
        requestAnimationFrame(updateCounter);
      } else {
        element.textContent = target + suffix;
      }
    };

    requestAnimationFrame(updateCounter);
  }

  /**
   * Cleanup
   */
  destroy() {
    if (this.observer) {
      this.observer.disconnect();
    }
  }
}

// Export init function
export function init() {
  const animations = new NGSAnimations();
  animations.init();
  return animations;
}

export default { init };
