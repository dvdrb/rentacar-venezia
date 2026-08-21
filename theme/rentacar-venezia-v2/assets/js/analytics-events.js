(function () {
  'use strict';

  var dataLayer = Array.isArray(window.dataLayer) ? window.dataLayer : null;

  var baseProperties = function () {
    var query = new URLSearchParams(window.location.search);
    return {
      language: document.documentElement.lang || '',
      landing_page: window.location.pathname,
      referrer: safeReferrer(document.referrer),
      utm_source: query.get('utm_source') || '',
      utm_medium: query.get('utm_medium') || '',
      utm_campaign: query.get('utm_campaign') || ''
    };
  };
  var track = function (event, properties) {
    if (dataLayer) dataLayer.push(Object.assign({ event: event }, baseProperties(), properties || {}));
  };
  var once = function (key, event, properties) {
    if (window.sessionStorage && sessionStorage.getItem(key)) return;
    if (window.sessionStorage) sessionStorage.setItem(key, '1');
    track(event, properties);
  };
  var reservationForm = document.querySelector('[data-reservation-form]');
  function safeReferrer(value) { try { var parsed = new URL(value); return parsed.origin + parsed.pathname; } catch (error) { return ''; } }
  function acquisitionContext() {
    var query = new URLSearchParams(window.location.search);
    var firstLanding = window.location.pathname;
    var referrer = safeReferrer(document.referrer);
    try {
      firstLanding = sessionStorage.getItem('rac_first_landing_page') || firstLanding;
      sessionStorage.setItem('rac_first_landing_page', firstLanding);
      if (!sessionStorage.getItem('rac_first_referrer') && referrer) sessionStorage.setItem('rac_first_referrer', referrer);
      referrer = sessionStorage.getItem('rac_first_referrer') || referrer;
    } catch (error) {}
    return { acquisition_first_landing_page: firstLanding, acquisition_last_landing_page: window.location.pathname, acquisition_referrer: referrer, acquisition_utm_source: query.get('utm_source') || '', acquisition_utm_medium: query.get('utm_medium') || '', acquisition_utm_campaign: query.get('utm_campaign') || '' };
  }
  if (reservationForm) { var acquisition = acquisitionContext(); Object.keys(acquisition).forEach(function (key) { var field = reservationForm.elements.namedItem(key); if (field) field.value = acquisition[key]; }); }
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
    };
    new MutationObserver(reportConfirmation).observe(success, { attributes: true, childList: true, subtree: true });
    reportConfirmation();
  }
}());
