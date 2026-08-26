(function () {
  'use strict';
  var toggle = document.querySelector('.wpt-nav-toggle');
  var nav = document.querySelector('.wpt-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }
  document.querySelectorAll('.wpt-menu .has-children > a').forEach(function (a) {
    a.addEventListener('click', function (e) {
      if (window.matchMedia('(max-width: 768px)').matches) {
        e.preventDefault();
        a.parentElement.classList.toggle('is-open');
      }
    });
  });
})();
