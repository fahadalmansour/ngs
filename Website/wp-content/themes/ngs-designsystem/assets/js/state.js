/**
 * NGS Design System - State Management
 * Lightweight pub/sub state store with persistence
 * @version 1.0.0
 */

'use strict';

const NGSState = {
  _state: {
    cart: { items: [], total: 0, count: 0 },
    filters: {
      protocol: [],
      compatibility: [],
      priceRange: [0, 5000],
      inStock: true,
      sort: 'default'
    },
    ui: {
      mobileMenuOpen: false,
      searchOpen: false,
      filterDrawerOpen: false,
      activeModal: null
    }
  },
  _subscribers: new Map(),

  /**
   * Get state value by key (returns deep copy)
   */
  get(key) {
    return structuredClone(this._state[key]);
  },

  /**
   * Update state and notify subscribers
   */
  update(key, value) {
    this._state[key] = { ...this._state[key], ...value };
    (this._subscribers.get(key) || []).forEach(cb => cb(this._state[key]));
  },

  /**
   * Subscribe to state changes
   * @returns {Function} Unsubscribe function
   */
  subscribe(key, callback) {
    if (!this._subscribers.has(key)) this._subscribers.set(key, []);
    this._subscribers.get(key).push(callback);
    return () => {
      const subs = this._subscribers.get(key);
      this._subscribers.set(key, subs.filter(cb => cb !== callback));
    };
  }
};

// Persist cart to sessionStorage
NGSState.subscribe('cart', (cart) => {
  try {
    sessionStorage.setItem('ngs_cart_cache', JSON.stringify(cart));
  } catch(e) {
    console.warn('Failed to persist cart:', e);
  }
});

// Persist filters to URL params
NGSState.subscribe('filters', (filters) => {
  const params = new URLSearchParams();
  if (filters.protocol.length) params.set('protocol', filters.protocol.join(','));
  if (filters.compatibility.length) params.set('compatibility', filters.compatibility.join(','));
  if (filters.priceRange[0] > 0) params.set('price_min', filters.priceRange[0]);
  if (filters.priceRange[1] < 5000) params.set('price_max', filters.priceRange[1]);
  if (!filters.inStock) params.set('in_stock', '0');
  if (filters.sort !== 'default') params.set('sort', filters.sort);
  const qs = params.toString();
  const url = qs ? `${location.pathname}?${qs}` : location.pathname;
  history.replaceState({}, '', url);
});

export default NGSState;
