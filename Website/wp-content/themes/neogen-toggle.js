/**
 * neogen Theme & Language Toggle
 * Add this via: Appearance → Customize → Additional CSS/JS
 * Or add to theme's functions.php
 */

(function() {
    'use strict';

    // Create toggle buttons on DOM load
    document.addEventListener('DOMContentLoaded', function() {
        createControls();
        initTheme();
        initLanguage();
    });

    // Create the floating control buttons
    function createControls() {
        const controls = document.createElement('div');
        controls.className = 'neogen-controls';
        controls.innerHTML = `
            <button class="neogen-toggle-btn theme-toggle" title="تبديل الوضع / Toggle Theme" aria-label="Toggle dark/light mode"></button>
            <button class="neogen-toggle-btn lang-toggle" title="English / عربي" aria-label="Toggle language">EN</button>
        `;
        document.body.appendChild(controls);

        // Theme toggle click
        controls.querySelector('.theme-toggle').addEventListener('click', toggleTheme);

        // Language toggle click
        controls.querySelector('.lang-toggle').addEventListener('click', toggleLanguage);
    }

    // Initialize theme from localStorage or system preference
    function initTheme() {
        const savedTheme = localStorage.getItem('neogen-theme');

        if (savedTheme) {
            setTheme(savedTheme);
        } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            setTheme('dark');
        } else {
            setTheme('light');
        }

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('neogen-theme')) {
                setTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    // Set theme
    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('neogen-theme', theme);

        // Update meta theme-color for mobile browsers
        const metaTheme = document.querySelector('meta[name="theme-color"]');
        if (metaTheme) {
            metaTheme.content = theme === 'dark' ? '#0a0a0a' : '#0a0a0a'; // Header is always dark
        }
    }

    // Toggle theme
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);

        // Add animation class
        document.body.classList.add('theme-transition');
        setTimeout(() => document.body.classList.remove('theme-transition'), 300);
    }

    // Initialize language from localStorage (default: Arabic)
    function initLanguage() {
        const savedLang = localStorage.getItem('neogen-lang') || 'ar';
        setLanguage(savedLang);
    }

    // Set language
    function setLanguage(lang) {
        const html = document.documentElement;
        const body = document.body;
        const langBtn = document.querySelector('.lang-toggle');

        if (lang === 'en') {
            html.setAttribute('lang', 'en');
            html.setAttribute('dir', 'ltr');
            body.classList.add('lang-en');
            body.classList.remove('lang-ar');
            if (langBtn) langBtn.textContent = 'ع';
        } else {
            html.setAttribute('lang', 'ar');
            html.setAttribute('dir', 'rtl');
            body.classList.add('lang-ar');
            body.classList.remove('lang-en');
            if (langBtn) langBtn.textContent = 'EN';
        }

        localStorage.setItem('neogen-lang', lang);

        // Translate UI elements if translation data exists
        translateUI(lang);
    }

    // Toggle language
    function toggleLanguage() {
        const currentLang = localStorage.getItem('neogen-lang') || 'ar';
        const newLang = currentLang === 'ar' ? 'en' : 'ar';
        setLanguage(newLang);
    }

    // UI Translation (basic elements)
    function translateUI(lang) {
        const translations = {
            ar: {
                'Add to cart': 'أضف للسلة',
                'Add to Cart': 'أضف للسلة',
                'View cart': 'عرض السلة',
                'Checkout': 'إتمام الطلب',
                'Home': 'الرئيسية',
                'Shop': 'المتجر',
                'Products': 'المنتجات',
                'About': 'من نحن',
                'Contact': 'تواصل معنا',
                'My Account': 'حسابي',
                'Cart': 'السلة',
                'Search': 'بحث',
                'Description': 'الوصف',
                'Reviews': 'التقييمات',
                'Related products': 'منتجات ذات صلة',
                'Sale!': 'تخفيض!',
                'Out of stock': 'نفذت الكمية',
                'In stock': 'متوفر',
                'Read more': 'اقرأ المزيد',
                'Select options': 'اختر الخيارات'
            },
            en: {
                'أضف للسلة': 'Add to Cart',
                'عرض السلة': 'View Cart',
                'إتمام الطلب': 'Checkout',
                'الرئيسية': 'Home',
                'المتجر': 'Shop',
                'المنتجات': 'Products',
                'من نحن': 'About',
                'تواصل معنا': 'Contact',
                'حسابي': 'My Account',
                'السلة': 'Cart',
                'بحث': 'Search',
                'الوصف': 'Description',
                'التقييمات': 'Reviews',
                'منتجات ذات صلة': 'Related Products',
                'تخفيض!': 'Sale!',
                'نفذت الكمية': 'Out of Stock',
                'متوفر': 'In Stock',
                'اقرأ المزيد': 'Read More'
            }
        };

        // Only translate if WPML/Polylang not active (basic fallback)
        if (typeof wpml_cookies === 'undefined' && typeof pll_cookies === 'undefined') {
            const currentTranslations = translations[lang];
            if (!currentTranslations) return;

            // Translate buttons and links
            document.querySelectorAll('a, button, .button, span').forEach(el => {
                const text = el.textContent.trim();
                if (currentTranslations[text]) {
                    el.textContent = currentTranslations[text];
                }
            });
        }
    }

    // Expose functions globally for external use
    window.neogenToggle = {
        setTheme: setTheme,
        setLanguage: setLanguage,
        toggleTheme: toggleTheme,
        toggleLanguage: toggleLanguage
    };

})();
