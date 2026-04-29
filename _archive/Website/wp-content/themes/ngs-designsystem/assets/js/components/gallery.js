/**
 * NGS Design System - Product Gallery
 * Image gallery with 3D model and AR support
 * @version 1.0.0
 */

'use strict';

class ProductGallery {
  constructor() {
    this.container = document.querySelector('.ngs-product-gallery');
    this.tabs = document.querySelectorAll('.ngs-gallery-tab');
    this.panels = document.querySelectorAll('.ngs-gallery-panel');
    this.mainImage = document.querySelector('.ngs-gallery-main__image');
    this.thumbnails = document.querySelectorAll('.ngs-gallery-thumbnail');
    this.modelViewer = document.querySelector('model-viewer');
    this.arButton = document.querySelector('.ngs-gallery-ar-btn');
    this.currentTab = 'image';
    this.currentImageIndex = 0;
  }

  init() {
    if (!this.container) return;

    // Tab switching
    this.tabs.forEach(tab => {
      tab.addEventListener('click', () => this.switchTab(tab.dataset.tab));
      tab.addEventListener('keydown', (e) => this.handleTabKeydown(e, tab));
    });

    // Image gallery
    this.initImageGallery();

    // 3D viewer
    if (this.modelViewer) {
      this.init3DViewer();
    }

    // AR mode
    if (this.arButton) {
      this.initARMode();
    }
  }

  /**
   * Switch between gallery tabs
   */
  switchTab(tabName) {
    if (this.currentTab === tabName) return;

    this.currentTab = tabName;

    // Update tabs
    this.tabs.forEach(tab => {
      const isActive = tab.dataset.tab === tabName;
      tab.setAttribute('aria-selected', isActive);
      tab.classList.toggle('ngs-gallery-tab--active', isActive);
    });

    // Update panels
    this.panels.forEach(panel => {
      const isActive = panel.dataset.panel === tabName;
      panel.hidden = !isActive;
      panel.classList.toggle('ngs-gallery-panel--active', isActive);
    });

    // Start 3D viewer if switching to 3D tab
    if (tabName === '3d' && this.modelViewer) {
      this.modelViewer.dismissPoster();
    }
  }

  /**
   * Handle tab keyboard navigation
   */
  handleTabKeydown(e, tab) {
    const tabs = Array.from(this.tabs);
    const currentIndex = tabs.indexOf(tab);

    switch (e.key) {
      case 'ArrowLeft':
      case 'ArrowRight':
        e.preventDefault();
        const direction = e.key === 'ArrowLeft' ? -1 : 1;
        const nextIndex = (currentIndex + direction + tabs.length) % tabs.length;
        const nextTab = tabs[nextIndex];
        nextTab.focus();
        this.switchTab(nextTab.dataset.tab);
        break;

      case 'Home':
        e.preventDefault();
        tabs[0].focus();
        this.switchTab(tabs[0].dataset.tab);
        break;

      case 'End':
        e.preventDefault();
        tabs[tabs.length - 1].focus();
        this.switchTab(tabs[tabs.length - 1].dataset.tab);
        break;
    }
  }

  /**
   * Initialize image gallery
   */
  initImageGallery() {
    if (!this.thumbnails.length) return;

    this.thumbnails.forEach((thumbnail, index) => {
      thumbnail.addEventListener('click', () => this.showImage(index));
      thumbnail.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          this.showImage(index);
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
          e.preventDefault();
          const direction = e.key === 'ArrowLeft' ? -1 : 1;
          const nextIndex = (index + direction + this.thumbnails.length) % this.thumbnails.length;
          this.thumbnails[nextIndex].focus();
          this.showImage(nextIndex);
        }
      });
    });

    // Image zoom on hover (desktop only)
    if (this.mainImage && window.innerWidth >= 768) {
      this.initImageZoom();
    }
  }

  /**
   * Show image at index
   */
  showImage(index) {
    if (index === this.currentImageIndex) return;

    this.currentImageIndex = index;

    // Update main image
    const thumbnail = this.thumbnails[index];
    const newSrc = thumbnail.dataset.full || thumbnail.src;
    const newAlt = thumbnail.alt;

    if (this.mainImage) {
      this.mainImage.src = newSrc;
      this.mainImage.alt = newAlt;
    }

    // Update thumbnails
    this.thumbnails.forEach((thumb, i) => {
      thumb.classList.toggle('ngs-gallery-thumbnail--active', i === index);
      thumb.setAttribute('aria-selected', i === index);
    });
  }

  /**
   * Initialize image zoom on hover
   */
  initImageZoom() {
    const zoomContainer = this.mainImage.parentElement;

    zoomContainer.addEventListener('mousemove', (e) => {
      const rect = zoomContainer.getBoundingClientRect();
      const x = ((e.clientX - rect.left) / rect.width) * 100;
      const y = ((e.clientY - rect.top) / rect.height) * 100;

      this.mainImage.style.transformOrigin = `${x}% ${y}%`;
      this.mainImage.classList.add('ngs-gallery-main__image--zoom');
    });

    zoomContainer.addEventListener('mouseleave', () => {
      this.mainImage.classList.remove('ngs-gallery-main__image--zoom');
    });
  }

  /**
   * Initialize 3D viewer
   */
  init3DViewer() {
    const progressBar = document.querySelector('.ngs-gallery-3d-progress');

    // Loading progress
    this.modelViewer.addEventListener('progress', (e) => {
      const progress = e.detail.totalProgress * 100;
      if (progressBar) {
        progressBar.style.width = `${progress}%`;
        progressBar.setAttribute('aria-valuenow', progress);
      }
    });

    // Loading complete
    this.modelViewer.addEventListener('load', () => {
      if (progressBar) {
        progressBar.style.opacity = '0';
        setTimeout(() => {
          progressBar.style.display = 'none';
        }, 300);
      }
    });

    // Error handling
    this.modelViewer.addEventListener('error', (e) => {
      console.error('3D model loading error:', e);
      const errorMsg = document.createElement('div');
      errorMsg.className = 'ngs-gallery-3d-error';
      errorMsg.textContent = 'فشل تحميل النموذج ثلاثي الأبعاد';
      this.modelViewer.parentElement.appendChild(errorMsg);
    });
  }

  /**
   * Initialize AR mode
   */
  initARMode() {
    // Check AR support
    if (!this.modelViewer || !this.modelViewer.canActivateAR) {
      this.arButton.disabled = true;
      this.arButton.title = 'الواقع المعزز غير متاح على هذا الجهاز';
      return;
    }

    this.arButton.addEventListener('click', () => {
      // Switch to 3D tab first if not already there
      if (this.currentTab !== '3d') {
        this.switchTab('3d');
        // Wait for panel to be visible
        setTimeout(() => {
          this.activateAR();
        }, 300);
      } else {
        this.activateAR();
      }
    });
  }

  /**
   * Activate AR mode
   */
  activateAR() {
    if (!this.modelViewer) return;

    try {
      this.modelViewer.activateAR();
    } catch (error) {
      console.error('AR activation error:', error);
      const errorMsg = document.createElement('div');
      errorMsg.className = 'ngs-gallery-ar-error';
      errorMsg.textContent = 'فشل تشغيل الواقع المعزز';
      this.arButton.parentElement.appendChild(errorMsg);
      setTimeout(() => errorMsg.remove(), 3000);
    }
  }
}

/**
 * Initialize product gallery
 */
export function init() {
  const gallery = new ProductGallery();
  gallery.init();
  return gallery;
}

export default { init };
