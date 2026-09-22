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

  initHomeHero();
  initLightbox();

  function initHomeHero() {
    var root = document.querySelector('.wpt-hero--home .wpt-hero__slides');
    if (!root) {
      return;
    }
    var slides = Array.prototype.slice.call(root.querySelectorAll('.wpt-hero__slide'));
    if (slides.length < 2) {
      return;
    }
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      slides.forEach(loadSlide);
      return;
    }

    var intervalMs = 2000;
    var preloadAll = true;
    var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
    if (conn) {
      if (conn.saveData) {
        intervalMs = 10000;
        preloadAll = false;
      } else if (conn.effectiveType === 'slow-2g' || conn.effectiveType === '2g') {
        intervalMs = 10000;
        preloadAll = false;
      } else if (conn.effectiveType === '3g') {
        intervalMs = 5000;
      }
      if (typeof conn.downlink === 'number' && conn.downlink > 0 && conn.downlink < 1.5) {
        intervalMs = Math.max(intervalMs, 5000);
        if (conn.downlink < 0.7) {
          intervalMs = 10000;
          preloadAll = false;
        }
      }
    }

    loadSlide(slides[0]);
    loadSlide(slides[1]);

    var first = slides[0];
    var started = false;
    var t0 = performance.now();
    function onFirstReady() {
      if (started) {
        return;
      }
      started = true;
      var loadMs = performance.now() - t0;
      if (loadMs > 2500) {
        intervalMs = Math.max(intervalMs, 10000);
        preloadAll = false;
      } else if (loadMs > 1200) {
        intervalMs = Math.max(intervalMs, 5000);
      }
      if (preloadAll) {
        slides.forEach(loadSlide);
      } else {
        var i = 2;
        function nextIdle() {
          if (i >= slides.length) {
            return;
          }
          loadSlide(slides[i]);
          i += 1;
          if ('requestIdleCallback' in window) {
            window.requestIdleCallback(nextIdle, { timeout: 4000 });
          } else {
            setTimeout(nextIdle, 1500);
          }
        }
        nextIdle();
      }
      startRotate();
    }
    if (first.complete && first.naturalWidth) {
      onFirstReady();
    } else {
      first.addEventListener('load', onFirstReady);
      first.addEventListener('error', onFirstReady);
      setTimeout(onFirstReady, 4000);
    }

    function loadSlide(el) {
      if (!el) {
        return;
      }
      var src = el.getAttribute('data-src');
      if (src && el.getAttribute('src') !== src) {
        el.setAttribute('src', src);
      }
    }

    function startRotate() {
      var idx = 0;
      var timer = null;
      function show(n) {
        slides[idx].classList.remove('is-active');
        slides[idx].setAttribute('aria-hidden', 'true');
        idx = n;
        loadSlide(slides[idx]);
        loadSlide(slides[(idx + 1) % slides.length]);
        slides[idx].classList.add('is-active');
        slides[idx].removeAttribute('aria-hidden');
      }
      function tick() {
        show((idx + 1) % slides.length);
      }
      function play() {
        stop();
        timer = setInterval(tick, intervalMs);
      }
      function stop() {
        if (timer) {
          clearInterval(timer);
          timer = null;
        }
      }
      var hero = root.closest('.wpt-hero');
      if (hero) {
        hero.addEventListener('mouseenter', stop);
        hero.addEventListener('mouseleave', play);
        hero.addEventListener('focusin', stop);
        hero.addEventListener('focusout', play);
      }
      document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
          stop();
        } else {
          play();
        }
      });
      play();
    }
  }

  function initLightbox() {
    var i18n = (window.watphouTheme && window.watphouTheme.i18n) || {};
    var skipClosest = [
      'header',
      'footer',
      'nav',
      '.wpt-header',
      '.wpt-footer',
      '.wpt-nav',
      '.wpt-topbar',
      '.wpt-hero',
      '.wpt-logo',
      '.watphou-whatsapp-float',
      '.wpt-dest-tile',
      '.wpt-dest-card'
    ].join(',');

    var items = [];
    document.querySelectorAll('img').forEach(function (img) {
      if (shouldSkip(img)) {
        return;
      }
      var src = img.currentSrc || img.src;
      if (!src) {
        return;
      }
      var trigger = img.closest('a[data-wpt-lightbox], button[data-wpt-lightbox]');
      if (!trigger) {
        trigger = wrapImage(img);
      }
      if (!trigger) {
        return;
      }
      var group = trigger.getAttribute('data-wpt-group') || groupFor(img);
      if (group) {
        trigger.setAttribute('data-wpt-group', group);
      }
      items.push({
        trigger: trigger,
        src: src,
        alt: img.getAttribute('alt') || '',
        group: group
      });
    });

    if (!items.length) {
      return;
    }

    var box = document.createElement('div');
    box.className = 'wpt-lightbox';
    box.hidden = true;
    box.setAttribute('role', 'dialog');
    box.setAttribute('aria-modal', 'true');
    box.setAttribute('aria-label', i18n.viewer || 'Image viewer');
    box.innerHTML =
      '<button type="button" class="wpt-lightbox__backdrop" tabindex="-1" aria-label="' +
      escapeHtml(i18n.close || 'Close') +
      '"></button>' +
      '<div class="wpt-lightbox__stage">' +
      '<img class="wpt-lightbox__img" alt="">' +
      '<p class="wpt-lightbox__caption"></p>' +
      '<button type="button" class="wpt-lightbox__close" aria-label="' +
      escapeHtml(i18n.close || 'Close') +
      '">×</button>' +
      '<button type="button" class="wpt-lightbox__prev" aria-label="' +
      escapeHtml(i18n.prev || 'Previous image') +
      '">‹</button>' +
      '<button type="button" class="wpt-lightbox__next" aria-label="' +
      escapeHtml(i18n.next || 'Next image') +
      '">›</button>' +
      '</div>';
    document.body.appendChild(box);

    var imgEl = box.querySelector('.wpt-lightbox__img');
    var captionEl = box.querySelector('.wpt-lightbox__caption');
    var prevBtn = box.querySelector('.wpt-lightbox__prev');
    var nextBtn = box.querySelector('.wpt-lightbox__next');
    var lastFocus = null;
    var index = 0;
    var set = items;
    var touchX = null;

    items.forEach(function (item, i) {
      item.trigger.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openAt(i);
      });
    });

    box.querySelector('.wpt-lightbox__backdrop').addEventListener('click', close);
    box.querySelector('.wpt-lightbox__close').addEventListener('click', close);
    prevBtn.addEventListener('click', function () {
      step(-1);
    });
    nextBtn.addEventListener('click', function () {
      step(1);
    });

    document.addEventListener('keydown', function (e) {
      if (box.hidden) {
        return;
      }
      if (e.key === 'Escape') {
        close();
      } else if (e.key === 'ArrowLeft') {
        step(-1);
      } else if (e.key === 'ArrowRight') {
        step(1);
      }
    });

    box.addEventListener(
      'touchstart',
      function (e) {
        if (e.changedTouches && e.changedTouches[0]) {
          touchX = e.changedTouches[0].clientX;
        }
      },
      { passive: true }
    );
    box.addEventListener(
      'touchend',
      function (e) {
        if (touchX === null || !e.changedTouches || !e.changedTouches[0]) {
          return;
        }
        var dx = e.changedTouches[0].clientX - touchX;
        touchX = null;
        if (Math.abs(dx) > 50) {
          step(dx > 0 ? -1 : 1);
        }
      },
      { passive: true }
    );

    function openAt(i) {
      var start = items[i];
      set = start.group
        ? items.filter(function (it) {
            return it.group === start.group;
          })
        : [start];
      index = set.indexOf(start);
      if (index < 0) {
        index = 0;
      }
      lastFocus = document.activeElement;
      show();
      box.hidden = false;
      document.body.classList.add('wpt-lightbox-open');
      box.querySelector('.wpt-lightbox__close').focus();
    }

    function show() {
      var current = set[index];
      imgEl.classList.remove('is-ready');
      imgEl.onload = function () {
        imgEl.classList.add('is-ready');
      };
      imgEl.src = current.src;
      imgEl.alt = current.alt;
      captionEl.textContent = current.alt;
      captionEl.hidden = !current.alt;
      var many = set.length > 1;
      prevBtn.hidden = !many;
      nextBtn.hidden = !many;
    }

    function step(dir) {
      if (set.length < 2) {
        return;
      }
      index = (index + dir + set.length) % set.length;
      show();
    }

    function close() {
      box.hidden = true;
      document.body.classList.remove('wpt-lightbox-open');
      imgEl.removeAttribute('src');
      if (lastFocus && typeof lastFocus.focus === 'function') {
        lastFocus.focus();
      }
    }

    function shouldSkip(img) {
      if (img.closest(skipClosest)) {
        return true;
      }
      if (img.classList.contains('wpt-wa-icon') || img.classList.contains('wpt-footer__logo')) {
        return true;
      }
      var src = img.currentSrc || img.src || '';
      if (!src || src.indexOf('data:') === 0) {
        return true;
      }
      var link = img.closest('a[href]');
      if (link && !link.hasAttribute('data-wpt-lightbox')) {
        var href = link.getAttribute('href') || '';
        if (!/\.(jpe?g|png|gif|webp|avif)(\?|$)/i.test(href.split('?')[0])) {
          return true;
        }
      }
      return false;
    }

    function groupFor(img) {
      var tourGallery = img.closest('.wpt-tour-gallery');
      if (tourGallery) {
        return 'tour-gallery';
      }
      var gallery = img.closest('.wpt-about__gallery');
      if (gallery) {
        var galleries = document.querySelectorAll('.wpt-about__gallery');
        return 'about-' + Array.prototype.indexOf.call(galleries, gallery);
      }
      return '';
    }

    function wrapImage(img) {
      if (img.closest('a, button')) {
        return null;
      }
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'wpt-lightbox-trigger';
      btn.setAttribute('data-wpt-lightbox', '');
      btn.setAttribute('aria-label', (img.getAttribute('alt') || i18n.viewer || 'Image viewer'));
      img.parentNode.insertBefore(btn, img);
      btn.appendChild(img);
      return btn;
    }

    function escapeHtml(str) {
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;');
    }
  }
})();
