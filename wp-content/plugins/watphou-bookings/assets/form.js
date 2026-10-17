(function () {
  var cfg = window.watphouBookings || {};
  var ajaxUrl = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
  var i18n = cfg.i18n || {};

  function t(key, fallback) {
    return i18n[key] || fallback;
  }

  function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = String(text == null ? '' : text);
    return div.innerHTML;
  }

  function requiredInputs(form) {
    return [
      form.querySelector('[name="customer_name"]'),
      form.querySelector('[name="email"]'),
      form.querySelector('[name="preferred_date"]'),
      form.querySelector('[name="people"]'),
      form.querySelector('[name="privacy_consent"]'),
    ].filter(Boolean);
  }

  function errorEl(input) {
    var wrap = input.closest('label') || input.parentElement;
    return wrap ? wrap.querySelector('.watphou-booking-form__field-error') : null;
  }

  function labelOf(input) {
    return input.getAttribute('data-label') || input.name;
  }

  function isEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  function fieldMessage(input) {
    if (input.type === 'checkbox') {
      return input.checked ? '' : 'required';
    }
    var value = (input.value || '').trim();
    if (input.name === 'people') {
      var n = Number(value);
      if (!(value && /^\d+$/.test(value) && n >= 1 && n <= 99)) {
        return 'people';
      }
      return n < (Number(input.getAttribute('data-min')) || 1) ? 'min' : '';
    }
    if (!value) {
      return 'required';
    }
    if (input.type === 'email' && !isEmail(value)) {
      return 'email';
    }
    return '';
  }

  function clearErrors(form) {
    var alertBox = form.querySelector('.watphou-booking-form__alert');
    if (alertBox) {
      alertBox.hidden = true;
      alertBox.classList.remove('watphou-booking-form__alert--error', 'watphou-booking-form__alert--ok');
      alertBox.textContent = '';
    }
    form.querySelectorAll('[aria-invalid="true"]').forEach(function (input) {
      input.removeAttribute('aria-invalid');
    });
    form.querySelectorAll('.watphou-booking-form__field-error').forEach(function (err) {
      err.hidden = true;
      err.textContent = '';
    });
  }

  function showFieldError(input, text) {
    input.setAttribute('aria-invalid', 'true');
    var err = errorEl(input);
    if (err) {
      err.hidden = false;
      err.textContent = text;
    }
  }

  function collectErrors(form) {
    var missing = [];
    requiredInputs(form).forEach(function (input) {
      var kind = fieldMessage(input);
      if (!kind) {
        return;
      }
      var label = labelOf(input);
      var text;
      if (kind === 'people') {
        text = t('verifyPeople', 'Please verify the number of people.');
      } else if (kind === 'min') {
        text = t('minPeople', 'Please enter %d people or more.').replace('%d', input.getAttribute('data-min'));
      } else if (kind === 'email') {
        text = t('invalidEmail', 'Please enter a valid email address (for example name@example.com).');
      } else {
        text = t('required', '%s is required.').replace('%s', label);
      }
      missing.push(kind === 'people' || kind === 'min' ? text : label);
      showFieldError(input, text);
    });
    return missing;
  }

  // Server text is inserted as text, never as HTML.
  function showAlert(form, type, title, items) {
    var alertBox = form.querySelector('.watphou-booking-form__alert');
    if (!alertBox) {
      return;
    }
    alertBox.hidden = false;
    alertBox.classList.remove('watphou-booking-form__alert--error', 'watphou-booking-form__alert--ok');
    alertBox.classList.add('watphou-booking-form__alert--' + type);
    var html = '<p>' + escapeHtml(title) + '</p>';
    if (items && items.length) {
      html += '<ul>' + items.map(function (item) {
        return '<li>' + escapeHtml(item) + '</li>';
      }).join('') + '</ul>';
    }
    alertBox.innerHTML = html;
    if (typeof alertBox.scrollIntoView === 'function') {
      alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  function applyServerErrors(form, errors, message) {
    var list = [];
    Object.keys(errors || {}).forEach(function (name) {
      var input = form.querySelector('[name="' + name.replace(/[^a-z_]/gi, '') + '"]');
      var text = String(errors[name]);
      if (input) {
        showFieldError(input, text);
        list.push(input.name === 'people' ? text : labelOf(input));
      } else {
        list.push(text);
      }
    });
    showAlert(form, 'error', message || t('missingTitle', 'Please complete these required fields:'), list);
  }

  function toCount(input) {
    var v = (input && input.value || '').trim();
    return /^\d{1,2}$/.test(v) ? Number(v) : 0;
  }

  // Tours with nights: people per room type (Sharing Twin/Double, Private Single) with - and +.
  // The read-only Number of people is their sum. The server recomputes it on its own.
  function bindRooms(form, onChange) {
    var twin = form.querySelector('[name="people_twin"]');
    var single = form.querySelector('[name="people_single"]');
    var people = form.querySelector('[name="people"]');
    if (!twin || !single || !people) {
      return;
    }
    function sync() {
      [twin, single].forEach(function (input) {
        var n = toCount(input);
        if (String(n) !== input.value) {
          input.value = String(n);
        }
      });
      var sum = toCount(twin) + toCount(single);
      people.value = sum ? String(Math.min(sum, 99)) : '';
      onChange();
    }
    form.querySelectorAll('.wpt-stepper__btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var input = btn.parentElement.querySelector('input');
        var other = input === twin ? single : twin;
        var n = toCount(input) + Number(btn.getAttribute('data-step'));
        if (n < 0 || n + toCount(other) > 99) {
          return;
        }
        input.value = String(n);
        sync();
      });
    });
    [twin, single].forEach(function (input) {
      input.addEventListener('input', sync);
      input.addEventListener('blur', sync);
    });
    sync();
  }

  // Booking summary on the inquiry page. Display only: prices and totals in the
  // stored request are recomputed on the server from the price table.
  function bindSummary(form) {
    var box = document.querySelector('.wpt-inquiry-summary[data-prices]');
    var input = form.querySelector('[name="people"]');
    if (!box || !input) {
      return;
    }
    var data;
    try {
      data = JSON.parse(box.getAttribute('data-prices') || '{}');
    } catch (e) {
      return;
    }
    var rows = Array.isArray(data.rows) ? data.rows : [];
    var words = data.i18n || {};
    var twin = form.querySelector('[name="people_twin"]');
    var single = form.querySelector('[name="people_single"]');
    function out(name) {
      return box.querySelector('[data-summary="' + name + '"]');
    }
    function set(el, text) {
      if (el) {
        el.textContent = text;
      }
    }
    function money(v) {
      return '$' + v.toLocaleString('en-US', { maximumFractionDigits: 2 });
    }
    function count(n) {
      return n === 1 ? (words.person || '1 person') : (words.people || '%d people').replace('%d', String(n));
    }
    var onRequest = words.onRequest || t('onRequest', 'On request');
    function update() {
      var n = toCount(input);
      var valid = n >= 1 && n <= 99;
      var row = null;
      if (valid) {
        rows.forEach(function (r) {
          if (!row && n >= r.min && n <= r.max) {
            row = r;
          }
        });
      }
      set(out('people'), valid ? String(n) : '—');
      var parts = data.day ? [['a', n]] : [['a', toCount(twin)], ['b', toCount(single)]];
      var sum = 0;
      var priced = !!row;
      parts.forEach(function (p) {
        var key = p[0];
        var people = valid ? p[1] : 0;
        var unit = row ? Number(row[key + 'n']) || 0 : 0;
        if (people > 0) {
          if (unit) {
            sum += unit * people;
          } else {
            priced = false;
          }
        }
        if (data.day) {
          set(out('unit'), !valid ? '—' : (unit ? money(unit) : onRequest));
          return;
        }
        // Only room types with people in them are shown.
        var wrap = out('room-' + key);
        if (wrap) {
          wrap.hidden = people < 1;
        }
        set(out('room-' + key + '-sub'), people ? count(people) + (unit ? ' × ' + money(unit) : '') : '');
        set(out('room-' + key + '-amount'), unit ? money(unit * people) : onRequest);
      });
      var total = '—';
      if (valid) {
        total = !row ? (words.quote || onRequest) : (priced ? money(sum) : onRequest);
      }
      set(out('total'), total);
    }
    input.addEventListener('input', update);
    bindRooms(form, update);
    update();
  }

  function bind(form) {
    form.setAttribute('novalidate', 'novalidate');
    bindSummary(form);
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      clearErrors(form);
      var missing = collectErrors(form);
      if (missing.length) {
        showAlert(form, 'error', t('missingTitle', 'Please complete these required fields:'), missing);
        var first = form.querySelector('[aria-invalid="true"]');
        if (first && typeof first.focus === 'function') {
          first.focus({ preventScroll: true });
        }
        return;
      }
      var button = form.querySelector('button[type="submit"]');
      var original = button ? button.textContent : '';
      if (button) {
        button.disabled = true;
        button.textContent = t('sending', 'Sending…');
      }
      fetch(ajaxUrl, {
        method: 'POST',
        body: new FormData(form),
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      })
        .then(function (res) {
          return res.json();
        })
        .then(function (data) {
          if (data && data.success && data.data) {
            showAlert(form, 'ok', data.data.message || 'Thank you.');
            // Lets the Google Analytics snippet (seo.php) count a lead.
            try {
              document.dispatchEvent(new CustomEvent('watphou:booking-sent', {
                detail: { tour: String(data.data.tour || ''), group: String(data.data.group || '') },
              }));
            } catch (e) {}
            form.querySelectorAll('fieldset, button[type="submit"], .watphou-booking-form__hint').forEach(function (el) {
              el.hidden = true;
            });
            return;
          }
          var payload = (data && data.data) || {};
          applyServerErrors(form, payload.errors || {}, payload.message || '');
          if (button) {
            button.disabled = false;
            button.textContent = original;
          }
        })
        .catch(function () {
          showAlert(form, 'error', t('network', 'The request could not be sent. Check your connection and try again.'));
          if (button) {
            button.disabled = false;
            button.textContent = original;
          }
        });
    });
  }

  document.querySelectorAll('.watphou-booking-form').forEach(bind);
})();
