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
      return value && /^\d+$/.test(value) && n >= 1 && n <= 99 ? '' : 'people';
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
      } else if (kind === 'email') {
        text = t('invalidEmail', 'Please enter a valid email address (for example name@example.com).');
      } else {
        text = t('required', '%s is required.').replace('%s', label);
      }
      missing.push(kind === 'people' ? text : label);
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

  // Booking summary on the inquiry page follows the number of people typed.
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
    // Row picked on the price table; shown until a number of people is typed.
    var chosen = data.chosen && typeof data.chosen === 'object' ? data.chosen : null;
    var peopleOut = box.querySelector('[data-summary="people"]');
    var groupOut = box.querySelector('[data-summary="group"]');
    var aOut = box.querySelector('[data-summary="a"]');
    var bOut = box.querySelector('[data-summary="b"]');
    var totalAOut = box.querySelector('[data-summary="total-a"]');
    var totalBOut = box.querySelector('[data-summary="total-b"]');
    function money(v) {
      return '$' + v.toLocaleString('en-US', { maximumFractionDigits: 2 });
    }
    // Display only: the emailed total is recomputed on the server from the stored table.
    function total(r, key, people, priced, onRequest) {
      if (!r) {
        return priced ? onRequest : '\u2014';
      }
      var unit = Number(r[key + 'n']) || 0;
      if (!unit) {
        return onRequest;
      }
      return people ? money(unit * people) : '\u2014';
    }
    function update() {
      var n = parseInt(input.value, 10);
      var valid = n >= 1 && n <= 99;
      if (peopleOut) {
        peopleOut.textContent = valid ? String(n) : '—';
      }
      var row = null;
      if (valid) {
        rows.forEach(function (r) {
          if (!row && n >= r.min && n <= r.max) {
            row = r;
          }
        });
      }
      var onRequest = t('onRequest', 'On request');
      var show = row || (!valid ? chosen : null);
      var priced = valid || !!show;
      if (groupOut) {
        groupOut.textContent = show ? show.label : (valid && n > data.max ? (data.max + 1) + '+' : '—');
      }
      if (aOut) {
        aOut.textContent = show && show.a ? show.a : (priced ? onRequest : '—');
      }
      if (bOut) {
        bOut.textContent = show && show.b ? show.b : (priced ? onRequest : '—');
      }
      var people = valid ? n : (show && show.min && show.min === show.max ? show.min : 0);
      if (totalAOut) {
        totalAOut.textContent = total(show, 'a', people, priced, onRequest);
      }
      if (totalBOut) {
        totalBOut.textContent = total(show, 'b', people, priced, onRequest);
      }
    }
    input.addEventListener('input', update);
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
