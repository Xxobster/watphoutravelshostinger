(function () {
  var cfg = window.watphouBookings || {};
  var ajaxUrl = cfg.ajaxUrl || '/wp-admin/admin-ajax.php';
  var i18n = cfg.i18n || {};

  function requiredInputs(form) {
    return [
      form.querySelector('[name="customer_name"]'),
      form.querySelector('[name="email"]'),
      form.querySelector('[name="preferred_date"]'),
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
      return input.checked ? '' : (input.validationMessage || 'required');
    }
    var value = (input.value || '').trim();
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
    requiredInputs(form).forEach(function (input) {
      input.removeAttribute('aria-invalid');
      var err = errorEl(input);
      if (err) {
        err.hidden = true;
        err.textContent = '';
      }
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
    var map = {};
    requiredInputs(form).forEach(function (input) {
      var kind = fieldMessage(input);
      if (!kind) {
        return;
      }
      var label = labelOf(input);
      var text =
        kind === 'email'
          ? label + ': please enter a valid email address (for example name@example.com).'
          : label + ' is required.';
      missing.push(label);
      map[input.name] = text;
      showFieldError(input, text);
    });
    return { missing: missing, map: map };
  }

  function showAlert(form, type, html) {
    var alertBox = form.querySelector('.watphou-booking-form__alert');
    if (!alertBox) {
      return;
    }
    alertBox.hidden = false;
    alertBox.classList.remove('watphou-booking-form__alert--error', 'watphou-booking-form__alert--ok');
    alertBox.classList.add('watphou-booking-form__alert--' + type);
    alertBox.innerHTML = html;
    if (typeof alertBox.scrollIntoView === 'function') {
      alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }

  function applyServerErrors(form, errors, message) {
    var list = [];
    Object.keys(errors || {}).forEach(function (name) {
      var input = form.querySelector('[name="' + name + '"]');
      var text = errors[name];
      if (input) {
        showFieldError(input, text);
        list.push(labelOf(input));
      } else {
        list.push(text);
      }
    });
    var title = message || i18n.missingTitle || 'Please complete these required fields:';
    var items = list.map(function (item) {
      return '<li>' + item + '</li>';
    }).join('');
    showAlert(form, 'error', '<p>' + title + '</p><ul>' + items + '</ul>');
  }

  function bind(form) {
    form.setAttribute('novalidate', 'novalidate');
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      clearErrors(form);
      var found = collectErrors(form);
      if (found.missing.length) {
        var title = i18n.missingTitle || 'Please complete these required fields:';
        var items = found.missing
          .map(function (item) {
            return '<li>' + item + '</li>';
          })
          .join('');
        showAlert(form, 'error', '<p>' + title + '</p><ul>' + items + '</ul>');
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
        button.textContent = i18n.sending || 'Sending…';
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
            showAlert(form, 'ok', '<p>' + (data.data.message || 'Thank you.') + '</p>');
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
          showAlert(form, 'error', '<p>' + (i18n.network || 'The request could not be sent.') + '</p>');
          if (button) {
            button.disabled = false;
            button.textContent = original;
          }
        });
    });
  }

  document.querySelectorAll('.watphou-booking-form').forEach(bind);
})();
