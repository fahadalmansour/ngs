/**
 * NGS Design System - Form Validation
 * Accessible form validation with bilingual error messages
 * @version 1.0.0
 */

'use strict';

/**
 * Validation rules
 */
const validators = {
  required: (value) => {
    return value.trim().length > 0;
  },

  email: (value) => {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(value.trim());
  },

  phone: (value) => {
    // Saudi phone format: +966XXXXXXXXX or 05XXXXXXXX
    const regex = /^(\+966|0)?5\d{8}$/;
    return regex.test(value.trim().replace(/\s/g, ''));
  },

  minLength: (min) => (value) => {
    return value.trim().length >= min;
  },

  maxLength: (max) => (value) => {
    return value.trim().length <= max;
  },

  saudiNationalId: (value) => {
    // 10 digits starting with 1 or 2
    const regex = /^[12]\d{9}$/;
    return regex.test(value.trim());
  },

  postalCode: (value) => {
    // 5 digits
    const regex = /^\d{5}$/;
    return regex.test(value.trim());
  },

  matchField: (fieldName) => (value, formData) => {
    return value === formData[fieldName];
  }
};

/**
 * Error messages (bilingual)
 */
const errorMessages = {
  ar: {
    required: 'هذا الحقل مطلوب',
    email: 'يرجى إدخال بريد إلكتروني صالح',
    phone: 'يرجى إدخال رقم هاتف سعودي صالح',
    minLength: (min) => `يجب أن يكون الحقل ${min} أحرف على الأقل`,
    maxLength: (max) => `يجب أن يكون الحقل ${max} أحرف على الأكثر`,
    saudiNationalId: 'يرجى إدخال رقم هوية وطنية صالح',
    postalCode: 'يرجى إدخال رمز بريدي مكون من 5 أرقام',
    matchField: (fieldName) => `يجب أن يتطابق مع ${fieldName}`
  },
  en: {
    required: 'This field is required',
    email: 'Please enter a valid email address',
    phone: 'Please enter a valid Saudi phone number',
    minLength: (min) => `Must be at least ${min} characters`,
    maxLength: (max) => `Must be at most ${max} characters`,
    saudiNationalId: 'Please enter a valid national ID',
    postalCode: 'Please enter a 5-digit postal code',
    matchField: (fieldName) => `Must match ${fieldName}`
  }
};

/**
 * FormValidator class
 */
export class FormValidator {
  constructor(form, rules, customMessages = {}) {
    this.form = form;
    this.rules = rules;
    this.customMessages = customMessages;
    this.touchedFields = new Set();
    this.errors = new Map();
    this.lang = document.documentElement.lang === 'ar' ? 'ar' : 'en';
    this.isRTL = document.documentElement.dir === 'rtl';
  }

  /**
   * Initialize validation
   */
  init() {
    if (!this.form) return;

    // Validate on blur
    this.form.addEventListener('blur', (e) => {
      if (this.isValidatableField(e.target)) {
        this.touchedFields.add(e.target.name);
        this.validateField(e.target);
      }
    }, true);

    // Validate on input for touched fields
    this.form.addEventListener('input', (e) => {
      if (this.isValidatableField(e.target) && this.touchedFields.has(e.target.name)) {
        this.validateField(e.target);
      }
    }, true);

    // Validate on submit
    this.form.addEventListener('submit', (e) => {
      if (!this.validateForm()) {
        e.preventDefault();
        this.focusFirstError();
      }
    });
  }

  /**
   * Check if field should be validated
   */
  isValidatableField(field) {
    return field.tagName === 'INPUT' || field.tagName === 'SELECT' || field.tagName === 'TEXTAREA';
  }

  /**
   * Validate entire form
   */
  validateForm() {
    let isValid = true;
    this.errors.clear();

    Object.keys(this.rules).forEach(fieldName => {
      const field = this.form.elements[fieldName];
      if (field && !this.validateField(field)) {
        isValid = false;
      }
    });

    return isValid;
  }

  /**
   * Validate single field
   */
  validateField(field) {
    const fieldName = field.name;
    const fieldRules = this.rules[fieldName];

    if (!fieldRules) return true;

    const value = field.value;
    const formData = this.getFormData();
    let isValid = true;
    let errorMessage = '';

    // Run each validation rule
    for (const [ruleName, ruleValue] of Object.entries(fieldRules)) {
      const validator = typeof ruleValue === 'function' ? ruleValue : validators[ruleName];

      if (!validator) {
        console.warn(`Validator "${ruleName}" not found`);
        continue;
      }

      // Get validator function (handle curried validators)
      const validatorFn = typeof ruleValue !== 'boolean' && ruleValue !== true
        ? validator(ruleValue)
        : validator;

      // Run validation
      const result = typeof validatorFn === 'function'
        ? validatorFn(value, formData)
        : validatorFn;

      if (!result) {
        isValid = false;
        errorMessage = this.getErrorMessage(fieldName, ruleName, ruleValue);
        break;
      }
    }

    // Update UI
    if (isValid) {
      this.clearFieldError(field);
      this.errors.delete(fieldName);
    } else {
      this.renderFieldError(field, errorMessage);
      this.errors.set(fieldName, errorMessage);
    }

    return isValid;
  }

  /**
   * Get error message for field and rule
   */
  getErrorMessage(fieldName, ruleName, ruleValue) {
    // Check custom messages first
    if (this.customMessages[fieldName]?.[ruleName]) {
      return this.customMessages[fieldName][ruleName];
    }

    // Use default messages
    const messages = errorMessages[this.lang];
    const message = messages[ruleName];

    if (typeof message === 'function') {
      return message(ruleValue);
    }

    return message || errorMessages.en[ruleName] || 'Invalid value';
  }

  /**
   * Render field error
   */
  renderFieldError(field, message) {
    const fieldContainer = field.closest('.ngs-form-field') || field.parentElement;
    let errorElement = fieldContainer.querySelector('.ngs-form-error');

    // Create error element if it doesn't exist
    if (!errorElement) {
      errorElement = document.createElement('div');
      errorElement.className = 'ngs-form-error';
      errorElement.id = `${field.name}-error`;
      fieldContainer.appendChild(errorElement);
    }

    // Set error message
    errorElement.textContent = message;
    errorElement.style.display = 'block';

    // Add error class to input
    field.classList.add('ngs-input--error');

    // Set ARIA attributes
    field.setAttribute('aria-invalid', 'true');
    field.setAttribute('aria-describedby', errorElement.id);
  }

  /**
   * Clear field error
   */
  clearFieldError(field) {
    const fieldContainer = field.closest('.ngs-form-field') || field.parentElement;
    const errorElement = fieldContainer.querySelector('.ngs-form-error');

    if (errorElement) {
      errorElement.style.display = 'none';
      errorElement.textContent = '';
    }

    // Remove error class
    field.classList.remove('ngs-input--error');

    // Remove ARIA attributes
    field.setAttribute('aria-invalid', 'false');
    field.removeAttribute('aria-describedby');
  }

  /**
   * Focus first error field
   */
  focusFirstError() {
    const firstErrorField = Array.from(this.errors.keys())[0];
    if (firstErrorField) {
      const field = this.form.elements[firstErrorField];
      field?.focus();
      field?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  /**
   * Get form data as object
   */
  getFormData() {
    const formData = new FormData(this.form);
    const data = {};
    formData.forEach((value, key) => {
      data[key] = value;
    });
    return data;
  }

  /**
   * Reset validation
   */
  reset() {
    this.touchedFields.clear();
    this.errors.clear();

    // Clear all error displays
    Object.keys(this.rules).forEach(fieldName => {
      const field = this.form.elements[fieldName];
      if (field) {
        this.clearFieldError(field);
      }
    });
  }
}

/**
 * Common validation rules presets
 */
export const validationPresets = {
  contactForm: {
    name: { required: true, minLength: 2, maxLength: 100 },
    email: { required: true, email: true },
    phone: { required: true, phone: true },
    message: { required: true, minLength: 10, maxLength: 1000 }
  },

  checkoutForm: {
    billing_first_name: { required: true, minLength: 2 },
    billing_last_name: { required: true, minLength: 2 },
    billing_email: { required: true, email: true },
    billing_phone: { required: true, phone: true },
    billing_address_1: { required: true },
    billing_city: { required: true },
    billing_postcode: { required: true, postalCode: true }
  },

  registrationForm: {
    username: { required: true, minLength: 3, maxLength: 20 },
    email: { required: true, email: true },
    password: { required: true, minLength: 8 },
    password_confirm: { required: true, matchField: 'password' }
  }
};

export { validators };
export default FormValidator;
