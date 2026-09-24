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
  initReviewsSlider();

  function initHomeHero() {
    var PLACEHOLDER = 'data:image/gif;base64,R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs=';
    var FADE_MS = 900;
    var root = document.querySelector('.wpt-hero--home .wpt-hero__slides');
    if (!root) {
      return;
    }
    var slides = Array.prototype.slice.call(root.querySelectorAll('.wpt-hero__slide'));
    if (!slides.length) {
      return;
    }

    function rememberSrc(el) {
      if (!el.getAttribute('data-src')) {
        var current = el.getAttribute('src') || '';
        if (current && current.indexOf('data:') !== 0) {
          el.setAttribute('data-src', current);
        }
      }
      if (!el.getAttribute('data-srcset') && el.getAttribute('srcset')) {
        el.setAttribute('data-srcset', el.getAttribute('srcset'));
      }
    }

    function loadSlide(el) {
      if (!el) {
        return;
      }
      rememberSrc(el);
      var src = el.getAttribute('data-src');
      var srcset = el.getAttribute('data-srcset');
      if (src && el.getAttribute('src') !== src) {
        el.setAttribute('src', src);
      }
      if (srcset) {
        el.setAttribute('srcset', srcset);
      }
    }

    function unloadSlide(el) {
      if (!el) {
        return;
      }
      if (el.classList.contains('is-active')) {
        return;
      }
      if (parseFloat(window.getComputedStyle(el).opacity) > 0.02) {
        return;
      }
      rememberSrc(el);
      if (!el.getAttribute('data-src')) {
        return;
      }
      el.removeAttribute('srcset');
      el.setAttribute('src', PLACEHOLDER);
    }

    function keepAround(index) {
      var n = slides.length;
      var keep = {};
      keep[index] = true;
      if (n > 1) {
        keep[(index + 1) % n] = true;
      }
      slides.forEach(function (el, i) {
        if (keep[i]) {
          loadSlide(el);
        } else {
          unloadSlide(el);
        }
      });
    }

    function slideReady(el, cb) {
      var finished = false;
      function done() {
        if (finished) {
          return;
        }
        finished = true;
        el.removeEventListener('load', onLoad);
        el.removeEventListener('error', onLoad);
        cb();
      }
      function onLoad() {
        if (el.decode) {
          el.decode().then(done, done);
        } else {
          done();
        }
      }
      loadSlide(el);
      if (el.complete && el.naturalWidth) {
        onLoad();
        return;
      }
      el.addEventListener('load', onLoad);
      el.addEventListener('error', onLoad);
      window.setTimeout(done, 4000);
    }

    loadSlide(slides[0]);
    if (slides.length < 2 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      return;
    }

    var intervalMs = 2000;
    var mem = navigator.deviceMemory;
    var conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
    if (typeof mem === 'number' && mem > 0 && mem <= 4) {
      intervalMs = 5000;
    }
    if (typeof mem === 'number' && mem > 0 && mem <= 2) {
      intervalMs = 10000;
    }
    if (conn) {
      if (conn.saveData) {
        intervalMs = 10000;
      } else if (conn.effectiveType === 'slow-2g' || conn.effectiveType === '2g') {
        intervalMs = 10000;
      } else if (conn.effectiveType === '3g') {
        intervalMs = Math.max(intervalMs, 5000);
      }
      if (typeof conn.downlink === 'number' && conn.downlink > 0 && conn.downlink < 1.5) {
        intervalMs = Math.max(intervalMs, 5000);
        if (conn.downlink < 0.7) {
          intervalMs = 10000;
        }
      }
    }

    keepAround(0);

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
      } else if (loadMs > 1200) {
        intervalMs = Math.max(intervalMs, 5000);
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

    function startRotate() {
      var idx = 0;
      var timer = null;
      var fading = false;
      function show(n) {
        if (fading || n === idx) {
          return;
        }
        var outgoing = slides[idx];
        var incoming = slides[n];
        fading = true;
        loadSlide(incoming);
        slideReady(incoming, function () {
          if (!incoming.naturalWidth) {
            fading = false;
            return;
          }
          var finished = false;
          function afterFade() {
            if (finished) {
              return;
            }
            finished = true;
            outgoing.removeEventListener('transitionend', onFadeEnd);
            keepAround(idx);
            fading = false;
          }
          function onFadeEnd(event) {
            if (event.propertyName && event.propertyName !== 'opacity') {
              return;
            }
            afterFade();
          }
          outgoing.addEventListener('transitionend', onFadeEnd);
          outgoing.classList.remove('is-active');
          outgoing.setAttribute('aria-hidden', 'true');
          incoming.classList.add('is-active');
          incoming.removeAttribute('aria-hidden');
          idx = n;
          window.setTimeout(afterFade, FADE_MS + 200);
        });
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
      var visible = true;
      if (hero) {
        hero.addEventListener('mouseenter', stop);
        hero.addEventListener('mouseleave', function () {
          if (visible && !document.hidden) {
            play();
          }
        });
        hero.addEventListener('focusin', stop);
        hero.addEventListener('focusout', function () {
          if (visible && !document.hidden) {
            play();
          }
        });
      }
      if (hero && 'IntersectionObserver' in window) {
        var viewer = new IntersectionObserver(function (entries) {
          visible = !!(entries[0] && entries[0].isIntersecting);
          if (!visible) {
            stop();
            slides.forEach(function (el, i) {
              if (i === idx) {
                loadSlide(el);
              } else {
                unloadSlide(el);
              }
            });
          } else if (!document.hidden) {
            keepAround(idx);
            play();
          }
        }, { rootMargin: '120px 0px' });
        viewer.observe(hero);
      }
      document.addEventListener('visibilitychange', function () {
        if (document.hidden || !visible) {
          stop();
        } else {
          keepAround(idx);
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
      var src = fullPictureUrl(img);
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

    function fullPictureUrl(img) {
      var full = img.getAttribute('data-full');
      if (full) {
        return full;
      }
      var link = img.closest('a[href]');
      if (link) {
        var href = link.getAttribute('href') || '';
        if (/\.(jpe?g|png|gif|webp|avif)(\?|$)/i.test(href.split('#')[0].split('?')[0])) {
          return href;
        }
      }
      return img.currentSrc || img.src || '';
    }

    function shouldSkip(img) {
      if (img.closest(skipClosest)) {
        return true;
      }
      if (img.classList.contains('wpt-wa-icon') || img.classList.contains('wpt-footer__logo')) {
        return true;
      }
      if (img.closest('.wpt-review-card')) {
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

  function initReviewsSlider() {
    var root = document.querySelector('[data-wpt-reviews-slider]');
    if (!root) {
      return;
    }
    var viewport = root.querySelector('.wpt-reviews__viewport');
    var track = root.querySelector('.wpt-reviews__track');
    var prev = root.querySelector('.wpt-reviews__nav--prev');
    var next = root.querySelector('.wpt-reviews__nav--next');
    if (!viewport || !track) {
      return;
    }
    var originals = Array.prototype.slice.call(track.children);
    if (originals.length < 2) {
      if (prev) prev.hidden = true;
      if (next) next.hidden = true;
      return;
    }
    originals.forEach(function (slide) {
      track.appendChild(slide.cloneNode(true));
    });
    var index = 0;
    var timer = null;
    var hovering = false;
    var n = originals.length;

    function stepSize() {
      var first = track.children[0];
      if (!first) {
        return 0;
      }
      var styles = window.getComputedStyle(track);
      var gap = parseFloat(styles.columnGap || styles.gap) || 20;
      return first.getBoundingClientRect().width + gap;
    }

    function apply(instant) {
      track.style.transition = instant ? 'none' : 'transform 450ms ease';
      if (!instant) {
        track.style.willChange = 'transform';
        window.setTimeout(function () {
          track.style.willChange = 'auto';
        }, 500);
      }
      track.style.transform = 'translate3d(' + (-index * stepSize()) + 'px,0,0)';
    }

    function go(delta) {
      index += delta;
      apply(false);
      if (index >= n) {
        window.setTimeout(function () {
          index = 0;
          apply(true);
        }, 460);
      } else if (index < 0) {
        index = n - 1;
        apply(false);
      }
    }

    function start() {
      stop();
      if (hovering || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
      }
      timer = window.setInterval(function () {
        go(1);
      }, 5000);
    }

    function stop() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    if (prev) {
      prev.addEventListener('click', function () {
        go(-1);
        start();
      });
    }
    if (next) {
      next.addEventListener('click', function () {
        go(1);
        start();
      });
    }
    root.addEventListener('mouseenter', function () {
      hovering = true;
      stop();
    });
    root.addEventListener('mouseleave', function () {
      hovering = false;
      start();
    });
    var resizeRaf = 0;
    window.addEventListener('resize', function () {
      if (resizeRaf) {
        return;
      }
      resizeRaf = window.requestAnimationFrame(function () {
        resizeRaf = 0;
        apply(true);
      });
    }, { passive: true });
    track.querySelectorAll('.wpt-review-avatar img').forEach(function (img) {
      img.addEventListener('error', function () {
        img.style.display = 'none';
      });
    });
    apply(true);
    start();
  }
})();
