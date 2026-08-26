(function () {
  'use strict';

  var toggle = document.querySelector('.watphou-nav-toggle');
  var menu = document.querySelector('.watphou-nav-menu');
  if (toggle && menu) {
    toggle.addEventListener('click', function () {
      var open = menu.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  var wa = document.querySelector('.watphou-whatsapp-float');
  if (wa && window.watphouTheme && window.watphouTheme.whatsapp) {
    wa.setAttribute('href', window.watphouTheme.whatsapp);
  }
})();
