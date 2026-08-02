(function () {
  'use strict';

  var dataLayer = window.dataLayer;
  if (!Array.isArray(dataLayer)) return;

  var baseProperties = function () {
    var query = new URLSearchParams(window.location.search);
    return {
      language: document.documentElement.lang || '',
      landing_page: window.location.pathname,
      referrer: document.referrer || '',
      campaign: query.get('utm_campaign') || ''
    };
  };
  var track = function (event, properties) {
    dataLayer.push(Object.assign({ event: event }, baseProperties(), properties || {}));
  };
  var once = function (key, event, properties) {
    if (window.sessionStorage && sessionStorage.getItem(key)) return;
    if (window.sessionStorage) sessionStorage.setItem(key, '1');
    track(event, properties);
  };
  var reservationForm = document.querySelector('[data-reservation-form]');
  var reservationValues = function () {
    if (!reservationForm) return {};
    var vehicle = reservationForm.querySelector('[data-reservation-vehicle-id]');
    var pickup = reservationForm.elements.namedItem('pickup_location');
    var returnLocation = reservationForm.elements.namedItem('return_location');
    return {
      vehicle: vehicle ? vehicle.value : '',
      pickup_location: pickup ? pickup.value : '',
      return_location: returnLocation ? returnLocation.value : ''
    };
  };

  var tripForm = document.querySelector('[data-trip-form]');
  if (tripForm) {
    tripForm.addEventListener('input', function () { once('rac_availability_search_started', 'availability_search_started'); }, { once: true });
    tripForm.addEventListener('submit', function () { track('availability_search_completed'); });
  }

  if (document.body.classList.contains('single-cars')) {
    once('rac_vehicle_viewed_' + window.location.pathname, 'vehicle_viewed');
  }

  document.querySelectorAll('[data-reservation-trigger]').forEach(function (button) {
    button.addEventListener('click', function () {
      var vehicle = button.getAttribute('data-vehicle-id') || '';
      track('vehicle_selected', { vehicle: vehicle });
      track('reservation_started', { vehicle: vehicle });
    });
  });

  if (reservationForm) {
    var continueButton = reservationForm.querySelector('[data-reservation-continue]');
    if (continueButton) {
      continueButton.addEventListener('click', function () {
        var stepOneFields = reservationForm.querySelectorAll('[data-reservation-step="1"] input, [data-reservation-step="1"] select, [data-reservation-step="1"] textarea');
        var stepOneValid = Array.prototype.every.call(stepOneFields, function (field) { return field.checkValidity(); });
        if (stepOneValid) track('reservation_step_completed', reservationValues());
      });
    }
  }

  document.querySelectorAll('a[href^="tel:"]').forEach(function (link) {
    link.addEventListener('click', function () { track('phone_clicked'); });
  });
  document.querySelectorAll('a[href^="mailto:"]').forEach(function (link) {
    link.addEventListener('click', function () { track('email_clicked'); });
  });
  document.querySelectorAll('a[href*="wa.me"], a[href*="whatsapp"]').forEach(function (link) {
    link.addEventListener('click', function () { track('whatsapp_clicked'); });
  });
  document.querySelectorAll('[data-guide-cta]').forEach(function (link) {
    link.addEventListener('click', function () { track('guide_cta_clicked'); });
  });
  if (document.body.classList.contains('page-template-template-airport-location')) {
    once('rac_airport_page_viewed_' + window.location.pathname, 'airport_page_viewed');
  }

  var success = document.querySelector('[data-reservation-success]');
  if (success) {
    var reportConfirmation = function () {
      if (success.hasAttribute('hidden')) return;
      var reference = success.querySelector('[data-reservation-reference]');
      var leadId = reference && reference.textContent ? reference.textContent.replace(/^.*:\s*/, '') : '';
      var key = leadId || window.location.pathname;
      var properties = Object.assign({}, reservationValues(), leadId ? { lead_id: leadId } : {});
      once('rac_reservation_submitted_' + key, 'reservation_submitted', properties);
      once('rac_reservation_confirmed_' + key, 'reservation_confirmed', properties);
    };
    new MutationObserver(reportConfirmation).observe(success, { attributes: true, childList: true, subtree: true });
    reportConfirmation();
  }
}());
