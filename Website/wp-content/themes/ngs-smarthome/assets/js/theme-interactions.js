document.addEventListener('DOMContentLoaded', function () {
  var searchToggle = document.getElementById('search-toggle');
  var searchOverlay = document.getElementById('search-overlay');
  var searchClose = document.getElementById('search-close');
  var searchInput = searchOverlay ? searchOverlay.querySelector('input[type="search"]') : null;

  function openSearch() {
    if (!searchOverlay) return;
    searchOverlay.classList.add('is-open');
    searchOverlay.removeAttribute('hidden');
    document.body.classList.add('ngs-no-scroll');
    if (searchInput) searchInput.focus();
  }

  function closeSearch() {
    if (!searchOverlay) return;
    searchOverlay.classList.remove('is-open');
    searchOverlay.setAttribute('hidden', 'hidden');
    document.body.classList.remove('ngs-no-scroll');
  }

  if (searchToggle) {
    searchToggle.addEventListener('click', openSearch);
  }

  if (searchClose) {
    searchClose.addEventListener('click', closeSearch);
  }

  if (searchOverlay) {
    searchOverlay.addEventListener('click', function (event) {
      if (event.target === searchOverlay) {
        closeSearch();
      }
    });
  }

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && searchOverlay && searchOverlay.classList.contains('is-open')) {
      closeSearch();
    }
  });

  document.querySelectorAll('.faq-question').forEach(function (question) {
    question.addEventListener('click', function () {
      var item = this.parentElement;
      if (item) {
        item.classList.toggle('active');
      }
    });
  });
});
