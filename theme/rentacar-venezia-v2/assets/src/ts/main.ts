document.documentElement.classList.add('js');

type ReservationStrings = {
  menuOpen: string;
  menuClose: string;
  sending: string;
  sendRequest: string;
  reviewForm: string;
  deliveryFailed: string;
  reference: string;
  stepTrip: string;
  stepDetails: string;
  consentSaved: string;
};

type ReservationConfig = {
  reservationUrl: string;
  strings: ReservationStrings;
};

type RentacarWindow = Window & typeof globalThis & { rentacarVenezia?: ReservationConfig };

type AnalyticsWindow = Window & typeof globalThis & { dataLayer?: Array<Record<string, unknown>> };

const track = (event: string): void => {
  const analytics = window as AnalyticsWindow;
  if (Array.isArray(analytics.dataLayer)) analytics.dataLayer.push({ event });
};

const configuration = (window as RentacarWindow).rentacarVenezia;
const strings: ReservationStrings = configuration?.strings || {
  menuOpen: 'Open navigation',
  menuClose: 'Close navigation',
  sending: 'Sending…',
  sendRequest: 'Send request',
  reviewForm: 'Please review the form and try again.',
  deliveryFailed: 'We could not send the request. Please try again.',
  reference: 'Reference: %s',
  stepTrip: '1 of 2 · Trip',
  stepDetails: '2 of 2 · Contact',
  consentSaved: 'Your cookie preferences have been saved.',
};

const consentBanner = document.querySelector<HTMLElement>('[data-cookie-consent]');
const consentDialog = document.querySelector<HTMLDialogElement>('[data-cookie-preferences-dialog]');
const consentAnalytics = consentDialog?.querySelector<HTMLInputElement>('[data-cookie-analytics]');
let consentReturnFocus: HTMLElement | null = null;

const saveCookieConsent = (value: 'necessary' | 'analytics'): void => {
  const expires = new Date(Date.now() + 180 * 24 * 60 * 60 * 1000).toUTCString();
  document.cookie = `rentacar_cookie_consent=${value}; path=/; expires=${expires}; SameSite=Lax${window.location.protocol === 'https:' ? '; Secure' : ''}`;
  window.location.reload();
};

const openCookiePreferences = (source: HTMLElement): void => {
  if (!consentDialog) return;
  consentReturnFocus = source;
  consentDialog.showModal();
  requestAnimationFrame(() => consentAnalytics?.focus());
};

consentBanner?.querySelector<HTMLButtonElement>('[data-cookie-reject]')?.addEventListener('click', () => saveCookieConsent('necessary'));
consentBanner?.querySelector<HTMLButtonElement>('[data-cookie-accept]')?.addEventListener('click', () => saveCookieConsent('analytics'));
consentBanner?.querySelector<HTMLButtonElement>('[data-cookie-preferences]')?.addEventListener('click', (event) => openCookiePreferences(event.currentTarget as HTMLButtonElement));
document.querySelectorAll<HTMLButtonElement>('[data-cookie-settings]').forEach((button) => button.addEventListener('click', () => openCookiePreferences(button)));
consentDialog?.querySelector<HTMLButtonElement>('[data-cookie-close]')?.addEventListener('click', () => consentDialog.close());
consentDialog?.querySelector<HTMLButtonElement>('[data-cookie-save]')?.addEventListener('click', () => saveCookieConsent(consentAnalytics?.checked ? 'analytics' : 'necessary'));
consentDialog?.addEventListener('close', () => consentReturnFocus?.focus());

const toggle = document.querySelector<HTMLButtonElement>('[data-menu-toggle]');
const navigation = document.querySelector<HTMLElement>('[data-primary-navigation]');
let navigationOpen = false;

type LanguageSwitcher = {
  root: HTMLElement;
  trigger: HTMLButtonElement;
  menu: HTMLElement;
};

const languageSwitchers = Array.from(document.querySelectorAll<HTMLElement>('[data-language-switcher]')).flatMap((root) => {
  const trigger = root.querySelector<HTMLButtonElement>('[data-language-trigger]');
  const menu = root.querySelector<HTMLElement>('[data-language-menu]');

  return trigger && menu ? [{ root, trigger, menu }] : [];
});

const setLanguageSwitcher = (switcher: LanguageSwitcher, open: boolean, restoreFocus = false): void => {
  switcher.trigger.setAttribute('aria-expanded', String(open));
  switcher.menu.hidden = !open;

  if (!open && restoreFocus) {
    switcher.trigger.focus();
  }
};

const closeLanguageSwitchers = (restoreFocus = false): void => {
  languageSwitchers.forEach((switcher) => setLanguageSwitcher(switcher, false, restoreFocus));
};

const setNavigation = (open: boolean): void => {
  if (!toggle || !navigation) return;
  if (open) closeLanguageSwitchers();
  navigationOpen = open;
  toggle.setAttribute('aria-expanded', String(open));
  toggle.setAttribute('aria-label', open ? strings.menuClose : strings.menuOpen);
  navigation.classList.toggle('is-open', open);
  document.body.classList.toggle('menu-open', open);
  document.querySelectorAll<HTMLElement>('#main-content, .site-footer, .mobile-action-bar').forEach((target) => {
    target.toggleAttribute('inert', open);
    target.setAttribute('aria-hidden', String(open));
    if (!open) target.removeAttribute('aria-hidden');
  });
  if (open) requestAnimationFrame(() => navigation.querySelector<HTMLElement>('a, button')?.focus());
};

if (toggle && navigation) {
  toggle.setAttribute('aria-label', strings.menuOpen);
  toggle.addEventListener('click', () => setNavigation(!navigationOpen));
}

languageSwitchers.forEach((switcher) => {
  switcher.trigger.addEventListener('click', () => {
    const open = switcher.trigger.getAttribute('aria-expanded') !== 'true';

    if (open) {
      languageSwitchers.forEach((otherSwitcher) => {
        if (otherSwitcher !== switcher) setLanguageSwitcher(otherSwitcher, false);
      });
      setNavigation(false);
    }

    setLanguageSwitcher(switcher, open);
  });
});

document.addEventListener('pointerdown', (event) => {
  const target = event.target;
  if (target instanceof Node && !languageSwitchers.some((switcher) => switcher.root.contains(target))) {
    closeLanguageSwitchers();
  }
});

const tripForm = document.querySelector<HTMLFormElement>('[data-trip-form]');
if (tripForm) {
  const pickup = tripForm.elements.namedItem('pickup_location') as HTMLSelectElement | null;
  const quickLocations = Array.from(tripForm.querySelectorAll<HTMLButtonElement>('[data-trip-location]'));

  const syncTripLocations = (): void => {
    quickLocations.forEach((button) => {
      button.setAttribute('aria-pressed', String(button.dataset.tripLocation === pickup?.value));
    });
  };

  pickup?.addEventListener('change', syncTripLocations);
  quickLocations.forEach((button) => button.addEventListener('click', () => {
    if (pickup && button.dataset.tripLocation) {
      pickup.value = button.dataset.tripLocation;
      syncTripLocations();
    }
  }));
  tripForm.addEventListener('submit', () => track('trip_filter_submit'));
  syncTripLocations();
}

document.querySelectorAll<HTMLAnchorElement>('a[href^="tel:"], a[href*="wa.me"], a[href*="whatsapp"]')
  .forEach((link) => link.addEventListener('click', () => track(link.href.startsWith('tel:') ? 'phone_click' : 'whatsapp_click')));

document.querySelectorAll<HTMLElement>('.arrival-card').forEach((card) => card.addEventListener('click', () => track('airport_page_click')));

document.querySelectorAll<HTMLElement>('[data-guide-cta]').forEach((cta) => cta.addEventListener('click', () => track('guide_cta_click')));

const revealItems = Array.from(document.querySelectorAll<HTMLElement>('.reveal-on-scroll'));
if (revealItems.length) {
  if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    revealItems.forEach((item) => item.classList.add('is-visible'));
  } else {
    const observer = new IntersectionObserver((entries, currentObserver) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        (entry.target as HTMLElement).classList.add('is-visible');
        currentObserver.unobserve(entry.target);
      });
    }, { threshold: 0.14 });
    revealItems.forEach((item) => observer.observe(item));
  }
}

const modal = document.querySelector<HTMLElement>('[data-reservation-modal]');
const form = document.querySelector<HTMLFormElement>('[data-reservation-form]');
const formWrap = document.querySelector<HTMLElement>('[data-reservation-form-wrap]');
const success = document.querySelector<HTMLElement>('[data-reservation-success]');
const errorBox = document.querySelector<HTMLElement>('[data-reservation-errors]');
const reservationPanel = modal?.querySelector<HTMLElement>('.reservation-modal__panel');
const reservationModalTitle = modal?.querySelector<HTMLElement>('[data-reservation-modal-title]');
const inertTargets = Array.from(document.querySelectorAll<HTMLElement>('[data-site-header], #main-content, .site-footer'));
let trigger: HTMLElement | null = null;

const setupReservationDetails = (): void => {
  if (!form) return;
  const pickup = form.querySelector<HTMLSelectElement>('[data-reservation-pickup-location]');
  const returnLocation = form.querySelector<HTMLSelectElement>('select[data-reservation-return-location]');
  const returnDifferent = form.querySelector<HTMLInputElement>('[data-reservation-return-different]');
  const returnWrapper = returnLocation?.closest('label');
  const locationFee = form.querySelector<HTMLElement>('[data-reservation-location-fee]');

  const sync = (): void => {
    const different = Boolean(returnDifferent?.checked);
    if (returnWrapper) returnWrapper.hidden = !different;
    if (locationFee) locationFee.hidden = !different;
    if (!different && pickup && returnLocation) returnLocation.value = pickup.value;
  };

  pickup?.addEventListener('change', () => { sync(); void refreshEstimate(); });
  returnDifferent?.addEventListener('change', () => { sync(); void refreshEstimate(); });
  returnLocation?.addEventListener('change', () => { void refreshEstimate(); });
  sync();
};

const refreshEstimate = async (): Promise<void> => {
  if (!form) return;
  const output = form.querySelector<HTMLElement>('[data-reservation-estimate]');
  const content = form.querySelector<HTMLElement>('[data-reservation-estimate-content]');
  const vehicleId = form.querySelector<HTMLInputElement>('[data-reservation-vehicle-id]')?.value;
  const required = ['pickup_date', 'pickup_time', 'return_date', 'return_time'];
  const values = Object.fromEntries(required.map((name) => [name, (form.elements.namedItem(name) as HTMLInputElement | null)?.value || '']));
  if (!output || !content || !vehicleId || required.some((name) => !values[name])) return;
  const extras = Array.from(form.querySelectorAll<HTMLInputElement>('input[name="extras[]"]:checked')).map((input) => input.value);
  const insurance = form.querySelector<HTMLInputElement>('input[name="insurance"]:checked')?.value || 'base';
  const pickupLocation = (form.elements.namedItem('pickup_location') as HTMLSelectElement | null)?.value || '';
  const returnLocation = (form.elements.namedItem('return_location') as HTMLSelectElement | null)?.value || '';
  const body = new URLSearchParams({ vehicle_id: vehicleId, ...values, insurance, pickup_location: pickupLocation, return_location: returnLocation });
  extras.forEach((extra) => body.append('extras[]', extra));
  try {
    const response = await fetch('/wp-json/rentacar/v1/estimate', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body, credentials: 'same-origin' });
    if (!response.ok) return;
    const estimate = await response.json() as { days: number; line_items?: { label: string; amount: number }[]; estimate_total?: number; included_km?: number; excess_km_rate?: number; deposit?: number };
    if (!Array.isArray(estimate.line_items) || typeof estimate.estimate_total !== 'number') {
      content.textContent = 'An indicative total is not available for these dates. You can still send your request.';
      output.hidden = false;
      return;
    }
    content.replaceChildren(...estimate.line_items.map((item) => { const row = document.createElement('p'); row.textContent = `${item.label}: €${item.amount.toFixed(2)}`; return row; }));
    const total = document.createElement('p'); total.innerHTML = `<strong>Indicative rental total: €${estimate.estimate_total.toFixed(2)}</strong>`;
    const details = document.createElement('p'); details.textContent = `${estimate.days} rental days`;
    content.append(total, details); output.hidden = false;
  } catch { /* The form remains usable when estimates cannot be loaded. */ }
};

const setBackgroundInert = (inert: boolean): void => {
  inertTargets.forEach((target) => {
    if (inert) {
      target.setAttribute('inert', '');
      target.setAttribute('aria-hidden', 'true');
    } else {
      target.removeAttribute('inert');
      target.removeAttribute('aria-hidden');
    }
  });
};

const focusable = (): HTMLElement[] => modal
  ? Array.from(modal.querySelectorAll<HTMLElement>('a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])')).filter((item) => !item.closest('[hidden], .reservation-step:not(.is-active)'))
  : [];

const closeModal = (): void => {
  if (!modal) return;
  modal.hidden = true;
  document.body.classList.remove('reservation-open');
  setBackgroundInert(false);
  trigger?.focus();
};

const setReservationSuccessState = (completed: boolean): void => {
  modal?.classList.toggle('reservation-modal--success', completed);
  reservationPanel?.classList.toggle('reservation-modal__panel--success', completed);
  if (reservationModalTitle) {
    reservationModalTitle.textContent = completed ? reservationModalTitle.dataset.successTitle || '' : reservationModalTitle.dataset.requestTitle || '';
  }
};

const resetVisibleState = (): void => {
  formWrap?.removeAttribute('hidden');
  success?.setAttribute('hidden', '');
  setReservationSuccessState(false);
  if (errorBox) errorBox.textContent = '';
  setReservationStep(1);
};

if (modal) {
  const initiallyOpen = modal.dataset.reservationInitialOpen === '1';
  modal.setAttribute('role', 'dialog');
  modal.setAttribute('aria-modal', 'true');
  modal.hidden = !initiallyOpen;
  modal.classList.remove('reservation-modal--inline');
  setReservationSuccessState(initiallyOpen);
  if (initiallyOpen) {
    document.body.classList.add('reservation-open');
    setBackgroundInert(true);
    requestAnimationFrame(() => modal.querySelector<HTMLElement>('[data-reservation-close]')?.focus());
  }
}

setupReservationDetails();

const openModal = (button: HTMLElement): void => {
  if (!modal || !form) return;
  setNavigation(false);
  trigger = button;
  track('reservation_modal_open');
  resetVisibleState();
  modal.hidden = false;
  document.body.classList.add('reservation-open');
  setBackgroundInert(true);

  const data = button.dataset;
  const vehicleId = form.querySelector<HTMLInputElement>('[data-reservation-vehicle-id]');
  if (vehicleId) vehicleId.value = data.vehicleId || '';

  const title = modal.querySelector<HTMLElement>('[data-reservation-title]');
  if (title) title.textContent = data.vehicleTitle || '';
  const specifications = modal.querySelector<HTMLElement>('[data-reservation-specifications]');
  if (specifications) specifications.textContent = data.vehicleSpecifications || '';
  const prices = modal.querySelector<HTMLElement>('[data-reservation-prices]');
  if (prices) prices.textContent = data.vehiclePriceBands || '';

  const image = modal.querySelector<HTMLElement>('[data-reservation-image]');
  if (image) {
    image.replaceChildren();
    if (data.vehicleImage) {
      const vehicleImage = document.createElement('img');
      vehicleImage.src = data.vehicleImage;
      vehicleImage.alt = '';
      image.append(vehicleImage);
    }
  }

  requestAnimationFrame(() => (modal.querySelector<HTMLElement>('[data-reservation-close]') || form).focus());
  void refreshEstimate();
};

form?.addEventListener('change', () => { void refreshEstimate(); });

document.querySelectorAll<HTMLElement>('[data-reservation-trigger]').forEach((button) => {
  button.addEventListener('click', () => openModal(button));
});

modal?.querySelectorAll<HTMLElement>('[data-reservation-close]').forEach((button) => {
  button.addEventListener('click', closeModal);
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    const openSwitcher = languageSwitchers.find((switcher) => switcher.trigger.getAttribute('aria-expanded') === 'true');
    if (openSwitcher) {
      event.preventDefault();
      setLanguageSwitcher(openSwitcher, false, true);
      return;
    }
  }

  if (event.key === 'Escape' && modal && !modal.hidden) {
    closeModal();
    return;
  }

  if (event.key === 'Escape' && navigationOpen) {
    setNavigation(false);
    toggle?.focus();
    return;
  }

  if (event.key === 'Tab' && navigationOpen && navigation && toggle) {
    const items = [toggle, ...Array.from(navigation.querySelectorAll<HTMLElement>('a, button'))];
    const first = items[0];
    const last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }

  if (event.key === 'Tab' && modal && !modal.hidden) {
    const items = focusable();
    if (!items.length) return;
    const first = items[0];
    const last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  }
});

const displayErrors = (errors: Record<string, string[]>): void => {
  if (!form || !errorBox) return;
  const messages = Object.values(errors).flat().filter(Boolean);
  form.querySelectorAll<HTMLElement>('[aria-invalid="true"]').forEach((field) => {
    field.removeAttribute('aria-invalid');
    field.removeAttribute('aria-describedby');
  });
  Object.keys(errors).forEach((name) => {
    const field = form.elements.namedItem(name) as HTMLElement | null;
    field?.setAttribute('aria-invalid', 'true');
    field?.setAttribute('aria-describedby', 'reservation-errors');
  });
  errorBox.textContent = messages.join(' ');
  errorBox.focus();
};

const nativeErrors = (scope: ParentNode = form || document): Record<string, string[]> => {
  const errors: Record<string, string[]> = {};
  if (!form) return errors;
  Array.from(scope.querySelectorAll<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>('input, textarea, select')).forEach((element) => {
    if (!(element instanceof HTMLInputElement || element instanceof HTMLTextAreaElement || element instanceof HTMLSelectElement)) return;
    if (!element.name || element.type === 'hidden' || element.validity.valid) return;
    errors[element.name] = [element.validationMessage || strings.reviewForm];
  });
  return errors;
};

let reservationStep = 1;
const setReservationStep = (step: number): void => {
  if (!form) return;
  reservationStep = step;
  form.querySelectorAll<HTMLElement>('[data-reservation-step]').forEach((element) => {
    element.classList.toggle('is-active', Number(element.dataset.reservationStep) === step);
  });
  const progress = form.querySelector<HTMLElement>('[data-reservation-progress]');
  if (progress) progress.textContent = step === 1 ? strings.stepTrip : strings.stepDetails;
  if (errorBox) errorBox.textContent = '';
  if (modal && !modal.hidden) {
    reservationPanel?.scrollTo({
      top: 0,
      behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
    });
  }
};

form?.querySelector<HTMLButtonElement>('[data-reservation-continue]')?.addEventListener('click', () => {
  const currentStep = form?.querySelector<HTMLElement>('[data-reservation-step="1"]');
  const errors = nativeErrors(currentStep || form || document);
  if (Object.keys(errors).length) {
    displayErrors(errors);
    return;
  }
  setReservationStep(2);
  form?.querySelector<HTMLElement>('[data-reservation-step="2"] input, [data-reservation-step="2"] textarea, [data-reservation-step="2"] select')?.focus();
});

form?.querySelector<HTMLButtonElement>('[data-reservation-back]')?.addEventListener('click', () => {
  setReservationStep(1);
  requestAnimationFrame(() => form?.querySelector<HTMLElement>('[data-reservation-step="1"] input, [data-reservation-step="1"] select')?.focus());
});

form?.addEventListener('input', (event) => {
  const field = event.target;
  if (field instanceof HTMLElement && field.getAttribute('aria-invalid') === 'true') {
    field.removeAttribute('aria-invalid');
    field.removeAttribute('aria-describedby');
  }
});

if (form) {
  form.addEventListener('submit', async (event) => {
    if (!window.fetch) return;
    event.preventDefault();
    track('reservation_request_submit');

    if (reservationStep !== 2) {
      setReservationStep(1);
      return;
    }

    const clientErrors = nativeErrors(form.querySelector<HTMLElement>('[data-reservation-step="2"]') || form);
    if (Object.keys(clientErrors).length) {
      displayErrors(clientErrors);
      return;
    }

    const submit = form.querySelector<HTMLButtonElement>('[type="submit"]');
    if (submit) {
      submit.disabled = true;
      submit.textContent = strings.sending;
    }

    const data = new FormData(form);
    data.set('rentacar_ajax', '1');

    try {
      const response = await fetch(configuration?.reservationUrl || form.action, {
        method: 'POST',
        body: data,
        credentials: 'same-origin',
      });
      const body = await response.json() as { success: boolean; data: { reference?: string; errors?: Record<string, string[]> } };

      if (!body.success) {
        displayErrors(body.data.errors || { request: [strings.reviewForm] });
        return;
      }

      formWrap?.setAttribute('hidden', '');
      success?.removeAttribute('hidden');
      setReservationSuccessState(true);
      track('reservation_request_success');
      const reference = modal?.querySelector<HTMLElement>('[data-reservation-reference]');
      if (reference && body.data.reference) {
        reference.textContent = strings.reference.replace('%s', body.data.reference);
      }
      reservationModalTitle?.focus();
    } catch {
      displayErrors({ request: [strings.deliveryFailed] });
    } finally {
      if (submit) {
        submit.disabled = false;
        submit.textContent = strings.sendRequest;
      }
    }
  });
}
