/**
 * NGS Design System - Product Filters
 * AJAX-powered product filtering with URL sync
 * @version 1.0.0
 */

'use strict';

import NGSState from '../state.js';
import { show as showToast } from './toast.js';

class ProductFilters {
  constructor() {
    this.form = document.querySelector('.ngs-filters-form');
    this.grid = document.querySelector('.ngs-product-grid');
    this.resultCount = document.querySelector('.ngs-filters__result-count');
    this.resetBtn = document.querySelector('.ngs-filters__reset');
    this.mobileToggle = document.querySelector('.ngs-filters-mobile-toggle');
    this.filterDrawer = document.querySelector('.ngs-filters-drawer');
    this.drawerClose = document.querySelector('.ngs-filters-drawer__close');
    this.drawerBackdrop = document.querySelector('.ngs-filters-drawer__backdrop');
    this.isLoading = false;
  }

  init() {
    if (!this.form || !this.grid) return;

    // Initialize from URL params
    this.initFromURL();

    // Filter change handlers
    this.form.addEventListener('change', (e) => this.handleFilterChange(e));

    // Reset button
    this.resetBtn?.addEventListener('click', () => this.resetFilters());

    // Mobile drawer
    this.mobileToggle?.addEventListener('click', () => this.openDrawer());
    this.drawerClose?.addEventListener('click', () => this.closeDrawer());
    this.drawerBackdrop?.addEventListener('click', () => this.closeDrawer());

    // Subscribe to state changes
    NGSState.subscribe('filters', () => {
      this.applyFilters();
    });
  }

  /**
   * Initialize filter state from URL parameters
   */
  initFromURL() {
    const params = new URLSearchParams(window.location.search);
    const filters = NGSState.get('filters');

    // Protocol filters
    const protocol = params.get('protocol');
    if (protocol) {
      filters.protocol = protocol.split(',');
      this.setCheckboxes('protocol', filters.protocol);
    }

    // Compatibility filters
    const compatibility = params.get('compatibility');
    if (compatibility) {
      filters.compatibility = compatibility.split(',');
      this.setCheckboxes('compatibility', filters.compatibility);
    }

    // Price range
    const priceMin = params.get('price_min');
    const priceMax = params.get('price_max');
    if (priceMin || priceMax) {
      filters.priceRange = [
        priceMin ? parseInt(priceMin) : 0,
        priceMax ? parseInt(priceMax) : 5000
      ];
      this.setPriceRange(filters.priceRange);
    }

    // In stock
    const inStock = params.get('in_stock');
    if (inStock === '0') {
      filters.inStock = false;
      const checkbox = this.form.querySelector('[name="in_stock"]');
      if (checkbox) checkbox.checked = false;
    }

    // Sort
    const sort = params.get('sort');
    if (sort) {
      filters.sort = sort;
      const select = this.form.querySelector('[name="sort"]');
      if (select) select.value = sort;
    }

    NGSState.update('filters', filters);
  }

  /**
   * Set checkbox values from array
   */
  setCheckboxes(name, values) {
    const checkboxes = this.form.querySelectorAll(`[name="${name}[]"]`);
    checkboxes.forEach(checkbox => {
      checkbox.checked = values.includes(checkbox.value);
    });
  }

  /**
   * Set price range slider values
   */
  setPriceRange(range) {
    const minInput = this.form.querySelector('[name="price_min"]');
    const maxInput = this.form.querySelector('[name="price_max"]');
    if (minInput) minInput.value = range[0];
    if (maxInput) maxInput.value = range[1];
  }

  /**
   * Handle filter change
   */
  handleFilterChange(e) {
    const filters = NGSState.get('filters');
    const input = e.target;
    const name = input.name;

    if (name === 'protocol[]') {
      filters.protocol = this.getCheckedValues('protocol');
    } else if (name === 'compatibility[]') {
      filters.compatibility = this.getCheckedValues('compatibility');
    } else if (name === 'price_min' || name === 'price_max') {
      const min = parseInt(this.form.querySelector('[name="price_min"]').value) || 0;
      const max = parseInt(this.form.querySelector('[name="price_max"]').value) || 5000;
      filters.priceRange = [min, max];
    } else if (name === 'in_stock') {
      filters.inStock = input.checked;
    } else if (name === 'sort') {
      filters.sort = input.value;
    }

    NGSState.update('filters', filters);
  }

  /**
   * Get checked checkbox values
   */
  getCheckedValues(name) {
    const checkboxes = this.form.querySelectorAll(`[name="${name}[]"]:checked`);
    return Array.from(checkboxes).map(cb => cb.value);
  }

  /**
   * Apply filters via AJAX
   */
  async applyFilters() {
    if (this.isLoading) return;

    this.isLoading = true;
    this.showLoadingSkeleton();

    try {
      const filters = NGSState.get('filters');
      const params = new URLSearchParams();

      if (filters.protocol.length) params.set('protocol', filters.protocol.join(','));
      if (filters.compatibility.length) params.set('compatibility', filters.compatibility.join(','));
      if (filters.priceRange[0] > 0) params.set('price_min', filters.priceRange[0]);
      if (filters.priceRange[1] < 5000) params.set('price_max', filters.priceRange[1]);
      if (!filters.inStock) params.set('in_stock', '0');
      if (filters.sort !== 'default') params.set('sort', filters.sort);
      params.set('action', 'ngs_filter_products');
      params.set('nonce', window.ngs_ajax?.nonce || '');

      const response = await fetch(window.ngs_ajax?.ajax_url || '/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString()
      });

      if (!response.ok) throw new Error('Network response was not ok');

      const data = await response.json();

      if (data.success) {
        this.updateGrid(data.data.html);
        this.updateResultCount(data.data.count);
      } else {
        throw new Error(data.data?.message || 'Filter failed');
      }
    } catch (error) {
      console.error('Filter error:', error);
      showToast('فشل تحميل المنتجات. يرجى المحاولة مرة أخرى.', 'error');
    } finally {
      this.isLoading = false;
      this.hideLoadingSkeleton();
    }
  }

  /**
   * Update product grid HTML
   */
  updateGrid(html) {
    this.grid.innerHTML = html;

    // Trigger animation for new items
    const items = this.grid.querySelectorAll('.ngs-product-card');
    items.forEach((item, index) => {
      item.style.animationDelay = `${index * 50}ms`;
      item.classList.add('ngs-product-card--animate-in');
    });
  }

  /**
   * Update result count text
   */
  updateResultCount(count) {
    if (!this.resultCount) return;
    const isRTL = document.documentElement.dir === 'rtl';
    const text = isRTL
      ? `${count} منتج`
      : `${count} products`;
    this.resultCount.textContent = text;
  }

  /**
   * Show loading skeleton
   */
  showLoadingSkeleton() {
    this.grid.classList.add('ngs-product-grid--loading');
    this.grid.setAttribute('aria-busy', 'true');
  }

  /**
   * Hide loading skeleton
   */
  hideLoadingSkeleton() {
    this.grid.classList.remove('ngs-product-grid--loading');
    this.grid.setAttribute('aria-busy', 'false');
  }

  /**
   * Reset all filters
   */
  resetFilters() {
    const filters = {
      protocol: [],
      compatibility: [],
      priceRange: [0, 5000],
      inStock: true,
      sort: 'default'
    };

    // Reset form inputs
    this.form.reset();
    this.setPriceRange(filters.priceRange);

    // Update state
    NGSState.update('filters', filters);
  }

  /**
   * Open mobile filter drawer
   */
  openDrawer() {
    this.filterDrawer?.classList.add('ngs-filters-drawer--open');
    document.body.classList.add('ngs-filters-open');
    NGSState.update('ui', { filterDrawerOpen: true });
  }

  /**
   * Close mobile filter drawer
   */
  closeDrawer() {
    this.filterDrawer?.classList.remove('ngs-filters-drawer--open');
    document.body.classList.remove('ngs-filters-open');
    NGSState.update('ui', { filterDrawerOpen: false });
  }
}

/**
 * Initialize product filters
 */
export function init() {
  const filters = new ProductFilters();
  filters.init();
  return filters;
}

export default { init };
