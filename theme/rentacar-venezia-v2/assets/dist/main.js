(function () {
  document.documentElement.classList.add('js');
  var toggle = document.querySelector('[data-menu-toggle]');
  var navigation = document.querySelector('[data-primary-navigation]');
  if (toggle && navigation) {
    var closeMenu = function () { toggle.setAttribute('aria-expanded', 'false'); navigation.classList.remove('is-open'); };
    toggle.addEventListener('click', function () { var open = toggle.getAttribute('aria-expanded') === 'true'; toggle.setAttribute('aria-expanded', String(!open)); navigation.classList.toggle('is-open', !open); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && navigation.classList.contains('is-open')) { closeMenu(); toggle.focus(); } });
  }
  var card = document.querySelector('[data-request-card]');
  var form = document.querySelector('[data-request-form]');
  var review = document.querySelector('[data-request-review]');
  var result = document.querySelector('[data-estimate-result]');
  var whatsapp = document.querySelector('[data-whatsapp-link]');
  var unavailable = document.querySelector('[data-whatsapp-unconfigured]');
  var config = window.rentacarVenezia;
  function value(name) { var element = form.elements.namedItem(name); return element ? element.value.trim() : ''; }
  function isChecked(name) { var element = form.elements.namedItem(name); return !!(element && element.checked); }
  function euro(amount) { return new Intl.NumberFormat(document.documentElement.lang || 'en', { style: 'currency', currency: 'EUR', maximumFractionDigits: 2 }).format(amount || 0); }
  function renderEstimate(estimate) {
    result.replaceChildren();
    var label = document.createElement('p'); label.className = 'estimate-result__label'; label.textContent = estimate && estimate.available ? 'Indicative estimate · ' + estimate.days + ' day' + (estimate.days === 1 ? '' : 's') : 'Estimate to be confirmed'; result.append(label);
    if (estimate && estimate.available) { var total = document.createElement('strong'); total.className = 'estimate-result__total'; total.textContent = euro(estimate.base_total); result.append(total); var detail = document.createElement('span'); detail.textContent = euro(estimate.daily_price) + ' per day · base vehicle rate only'; result.append(detail); }
    var disclaimer = document.createElement('p'); disclaimer.className = 'estimate-result__disclaimer'; disclaimer.textContent = estimate ? estimate.disclaimer : config.estimateUnavailable; result.append(disclaimer);
  }
  function message(estimate) { return ['Hello, I would like to request a rental car.', '', 'Preferred vehicle: ' + (card.dataset.vehicleTitle || document.title), 'Similar vehicle accepted: ' + (isChecked('similar_vehicle') ? 'Yes' : 'No'), '', 'Pickup:', value('pickup_location') + ' — ' + value('pickup_date') + ' ' + value('pickup_time'), 'Return:', value('dropoff_location') + ' — ' + value('return_date') + ' ' + value('return_time'), '', 'Indicative website estimate: ' + (estimate && estimate.available ? euro(estimate.base_total) : 'To be confirmed'), '', 'Name: ' + value('full_name'), 'Phone / WhatsApp: ' + value('phone'), 'Email: ' + value('email'), value('message') ? 'Message: ' + value('message') : ''].filter(Boolean).join('\n'); }
  if (card && form && review && result && config) {
    card.dataset.vehicleTitle = (document.querySelector('h1') || {}).textContent || '';
    form.addEventListener('submit', function (event) { event.preventDefault(); if (!form.reportValidity()) return; fetch(config.estimateUrl, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ vehicle_id: Number(card.dataset.vehicleId), pickup_date: value('pickup_date'), pickup_time: value('pickup_time'), return_date: value('return_date'), return_time: value('return_time') }) }).then(function (response) { return response.ok ? response.json() : null; }).then(function (estimate) { renderEstimate(estimate); review.hidden = false; review.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); var destination = config.whatsappDestination.replace(/\D/g, ''); if (destination && whatsapp) { whatsapp.href = 'https://wa.me/' + destination + '?text=' + encodeURIComponent(message(estimate)); whatsapp.hidden = false; if (unavailable) unavailable.hidden = true; } }).catch(function () { renderEstimate(null); review.hidden = false; }); });
  }
}());
