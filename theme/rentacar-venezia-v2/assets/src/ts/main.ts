import { parsePhoneNumberFromString } from 'libphonenumber-js/core';
import phoneMetadata from '../../../../../plugin/rentacar-core/data/phone-metadata.json';

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
  minimumRental: string;
  invalidPeriod: string;
  loadingEstimate: string;
  estimateUnavailable: string;
  estimateError: string;
  retryEstimate: string;
  rentalDays: string;
  dailyRate: string;
  vehicleSubtotal: string;
  insurance: string;
  extras: string;
  afterHoursFee: string;
  airportTransfer: string;
  indicativeTotal: string;
  includedKm: string;
  excessKm: string;
};

type ReservationConfig = {
  reservationUrl: string;
  estimateUrl: string;
  minimumRentalDays: number;
  strings: ReservationStrings;
};

type RentacarWindow = Window & typeof globalThis & { rentacarVenezia?: ReservationConfig };

type AnalyticsWindow = Window & typeof globalThis & { dataLayer?: Array<Record<string, unknown>> };

const track = (event: string): void => {
  const analytics = window as AnalyticsWindow;
  if (Array.isArray(analytics.dataLayer)) analytics.dataLayer.push({ event });
};

const configuration = (window as RentacarWindow).rentacarVenezia;
if (!configuration) throw new Error('Reservation interface configuration is unavailable.');
const strings: ReservationStrings = configuration.strings;
const minimumRentalDays = Number(configuration.minimumRentalDays);
if (!Number.isInteger(minimumRentalDays) || minimumRentalDays < 1) throw new Error('Reservation minimum duration is unavailable.');

type PhoneField = {
  root: HTMLElement;
  native: HTMLSelectElement;
  enhanced: HTMLElement;
  trigger: HTMLButtonElement;
  dialog: HTMLElement;
  search: HTMLInputElement;
  options: HTMLButtonElement[];
  empty: HTMLElement;
  flag: HTMLElement;
  name: HTMLElement;
  code: HTMLElement;
  helpCode: HTMLElement;
  callingCode: HTMLInputElement;
  number: HTMLInputElement;
};

const phoneFields = Array.from(document.querySelectorAll<HTMLElement>('[data-phone-field]')).flatMap((root) => {
  const native = root.querySelector<HTMLSelectElement>('[data-phone-country]');
  const enhanced = root.querySelector<HTMLElement>('[data-phone-enhanced]');
  const trigger = root.querySelector<HTMLButtonElement>('[data-phone-trigger]');
  const dialog = root.querySelector<HTMLElement>('[data-phone-dialog]');
  const search = root.querySelector<HTMLInputElement>('[data-phone-search]');
  const empty = root.querySelector<HTMLElement>('[data-phone-empty]');
  const flag = root.querySelector<HTMLElement>('[data-phone-flag]');
  const name = root.querySelector<HTMLElement>('[data-phone-country-name]');
  const code = root.querySelector<HTMLElement>('[data-phone-code]');
  const helpCode = root.querySelector<HTMLElement>('[data-phone-help-code]');
  const callingCode = root.querySelector<HTMLInputElement>('[data-phone-calling-code]');
  const number = root.querySelector<HTMLInputElement>('[data-phone-number]');
  const options = Array.from(root.querySelectorAll<HTMLButtonElement>('[data-phone-option]'));
  return native && enhanced && trigger && dialog && search && empty && flag && name && code && helpCode && callingCode && number
    ? [{ root, native, enhanced, trigger, dialog, search, options, empty, flag, name, code, helpCode, callingCode, number }]
    : [];
});

const visiblePhoneOptions = (field: PhoneField): HTMLButtonElement[] => field.options.filter((option) => !option.hidden);

const setPhoneDialog = (field: PhoneField, open: boolean, focusSearch = false): void => {
  field.dialog.hidden = !open;
  field.trigger.setAttribute('aria-expanded', String(open));
  if (open && focusSearch) requestAnimationFrame(() => field.search.focus());
};

const setPhoneCountry = (field: PhoneField, country: string): void => {
  const option = field.native.querySelector<HTMLOptionElement>(`option[value="${CSS.escape(country)}"]`);
  const countryButton = field.options.find((item) => item.dataset.country === country);
  if (!option || !countryButton) return;
  const sourceFlag = countryButton.querySelector('span');
  field.native.value = country;
  field.callingCode.value = option.dataset.callingCode || countryButton.dataset.callingCode || '';
  // WordPress replaces flag emoji with an image on supported browsers. Copy
  // the rendered node rather than its text so the selected flag survives that
  // transformation as well as browsers that retain the emoji character.
  field.flag.replaceChildren(...Array.from(sourceFlag?.childNodes || []).map((node) => node.cloneNode(true)));
  field.name.textContent = countryButton.querySelectorAll('span')[1]?.textContent || country;
  field.code.textContent = field.callingCode.value;
  field.helpCode.textContent = field.callingCode.value;
  field.options.forEach((item) => item.setAttribute('aria-selected', String(item === countryButton)));
  setPhoneDialog(field, false);
  field.trigger.focus();
};

const filterPhoneCountries = (field: PhoneField): void => {
  const query = field.search.value.trim().toLocaleLowerCase();
  let matches = 0;
  field.options.forEach((option) => {
    const match = !query || (option.dataset.search || '').toLocaleLowerCase().includes(query);
    option.hidden = !match;
    if (match) matches += 1;
  });
  field.empty.hidden = matches > 0;
};

phoneFields.forEach((field) => {
  field.enhanced.hidden = false;
  const nativeWrapper = field.root.querySelector<HTMLElement>('[data-phone-native]');
  nativeWrapper?.setAttribute('hidden', '');

  field.trigger.addEventListener('click', () => {
    const open = field.trigger.getAttribute('aria-expanded') !== 'true';
    setPhoneDialog(field, open, open);
  });
  field.search.addEventListener('input', () => filterPhoneCountries(field));
  field.search.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowDown') {
      event.preventDefault();
      visiblePhoneOptions(field)[0]?.focus();
    } else if (event.key === 'Escape') {
      setPhoneDialog(field, false);
      field.trigger.focus();
    }
  });
  field.options.forEach((option) => {
    option.addEventListener('click', () => setPhoneCountry(field, option.dataset.country || ''));
    option.addEventListener('keydown', (event) => {
      const options = visiblePhoneOptions(field);
      const index = options.indexOf(option);
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        options[(index + (event.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length]?.focus();
      } else if (event.key === 'Escape') {
        setPhoneDialog(field, false);
        field.trigger.focus();
      }
    });
  });
  field.number.addEventListener('blur', () => {
    if (!field.native.value || !field.number.value) return;
    const parsed = parsePhoneNumberFromString(field.number.value, field.native.value as never, phoneMetadata as never);
    if (parsed?.isValid() && parsed.country === field.native.value) field.number.value = parsed.formatNational();
  });
});

document.addEventListener('pointerdown', (event) => {
  const target = event.target;
  if (!(target instanceof Node)) return;
  phoneFields.forEach((field) => {
    if (!field.root.contains(target)) setPhoneDialog(field, false);
  });
});

document.addEventListener('keydown', (event) => {
  if (event.key !== 'Escape') return;
  phoneFields.forEach((field) => {
    if (field.trigger.getAttribute('aria-expanded') === 'true') {
      setPhoneDialog(field, false);
      field.trigger.focus();
    }
  });
});

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
  document.querySelectorAll<HTMLElement>('#main-content, .site-footer').forEach((target) => {
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
const localDateValue = (date: Date): string => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
if (tripForm) {
  const pickup = tripForm.elements.namedItem('pickup_location') as HTMLSelectElement | null;
  const pickupDate = tripForm.elements.namedItem('pickup_date') as HTMLInputElement | null;
  const returnDate = tripForm.elements.namedItem('return_date') as HTMLInputElement | null;
  const quickLocations = Array.from(tripForm.querySelectorAll<HTMLButtonElement>('[data-trip-location]'));

  const syncTripLocations = (): void => {
    quickLocations.forEach((button) => {
      button.setAttribute('aria-pressed', String(button.dataset.tripLocation === pickup?.value));
    });
  };

  pickup?.addEventListener('change', syncTripLocations);
  const syncTripDates = (): void => {
    if (!pickupDate?.value || !returnDate) return;
    const date = new Date(`${pickupDate.value}T00:00:00`);
    date.setDate(date.getDate() + minimumRentalDays);
    const minimum = localDateValue(date);
    returnDate.min = minimum;
    if (returnDate.value && returnDate.value < minimum) returnDate.value = '';
  };
  pickupDate?.addEventListener('change', syncTripDates);
  quickLocations.forEach((button) => button.addEventListener('click', () => {
    if (pickup && button.dataset.tripLocation) {
      pickup.value = button.dataset.tripLocation;
      syncTripLocations();
    }
  }));
  tripForm.addEventListener('submit', () => track('trip_filter_submit'));
  syncTripLocations();
  syncTripDates();
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
  const hotelDetails = form.querySelector<HTMLElement>('[data-reservation-hotel-details]');
  let hotelLocations: string[] = [];
  try {
    const locations = JSON.parse(form.dataset.hotelLocations || '[]');
    hotelLocations = Array.isArray(locations) ? locations.filter((location): location is string => typeof location === 'string') : [];
  } catch {
    hotelLocations = [];
  }
  const pickupDate = form.elements.namedItem('pickup_date') as HTMLInputElement | null;
  const returnDate = form.elements.namedItem('return_date') as HTMLInputElement | null;

  const sync = (): void => {
    const different = Boolean(returnDifferent?.checked);
    if (returnWrapper) returnWrapper.hidden = !different;
    if (locationFee) locationFee.hidden = !different;
    if (!different && pickup && returnLocation) returnLocation.value = pickup.value;
    if (hotelDetails) {
      const selectedLocations = [pickup?.value, different ? returnLocation?.value : pickup?.value];
      hotelDetails.hidden = !selectedLocations.some((location) => Boolean(location) && hotelLocations.includes(location as string));
    }
  };

  const syncRentalDates = (): void => {
    if (!pickupDate?.value || !returnDate) return;
    const minimumReturn = new Date(`${pickupDate.value}T00:00:00`);
    minimumReturn.setDate(minimumReturn.getDate() + minimumRentalDays);
    const minimum = localDateValue(minimumReturn);
    returnDate.min = minimum;
    if (returnDate.value && returnDate.value < minimum) returnDate.value = '';
  };

  pickup?.addEventListener('change', () => { sync(); void refreshEstimate(); });
  returnDifferent?.addEventListener('change', () => { sync(); void refreshEstimate(); });
  returnLocation?.addEventListener('change', () => { void refreshEstimate(); });
  pickupDate?.addEventListener('change', () => { syncRentalDates(); void refreshEstimate(); });
  returnDate?.addEventListener('change', () => { void refreshEstimate(); });
  sync();
  syncRentalDates();
};

let estimateTimer: number | undefined;
let estimateRequest: AbortController | undefined;
let estimateSequence = 0;

const refreshEstimate = async (): Promise<void> => {
  if (!form) return;
  const output = form.querySelector<HTMLElement>('[data-reservation-estimate]');
  const content = form.querySelector<HTMLElement>('[data-reservation-estimate-content]');
  const vehicleId = form.querySelector<HTMLInputElement>('[data-reservation-vehicle-id]')?.value;
  const required = ['pickup_date', 'pickup_time', 'return_date', 'return_time'];
  const values = Object.fromEntries(required.map((name) => [name, (form.elements.namedItem(name) as HTMLInputElement | null)?.value || '']));
  if (!output || !content || !vehicleId || required.some((name) => !values[name])) return;
  window.clearTimeout(estimateTimer);
  estimateTimer = window.setTimeout(() => { void requestEstimate(output, content, vehicleId, values); }, 250);
};

const requestEstimate = async (output: HTMLElement, content: HTMLElement, vehicleId: string, values: Record<string, string>): Promise<void> => {
  if (!form) return;
  const requestId = ++estimateSequence;
  estimateRequest?.abort();
  estimateRequest = new AbortController();
  output.hidden = false;
  content.textContent = strings.loadingEstimate;
  const pickupDate = new Date(`${values.pickup_date}T${values.pickup_time}`);
  const returnDate = new Date(`${values.return_date}T${values.return_time}`);
  const calendarDays = Math.floor((returnDate.getTime() - pickupDate.getTime()) / 86400000);
  const billableDays = calendarDays + (values.return_time > values.pickup_time ? 1 : 0);
  if (!Number.isFinite(billableDays) || returnDate <= pickupDate) {
    content.textContent = strings.invalidPeriod;
    return;
  }
  if (billableDays < minimumRentalDays) {
    content.textContent = strings.minimumRental;
    return;
  }
  const extras = Array.from(form.querySelectorAll<HTMLInputElement>('input[name="extras[]"]:checked')).map((input) => input.value);
  const insurance = form.querySelector<HTMLInputElement>('input[name="insurance"]:checked')?.value || 'base';
  const pickupLocation = (form.elements.namedItem('pickup_location') as HTMLSelectElement | null)?.value || '';
  const returnLocation = (form.elements.namedItem('return_location') as HTMLSelectElement | null)?.value || '';
  const body = new URLSearchParams({ vehicle_id: vehicleId, ...values, insurance, pickup_location: pickupLocation, return_location: returnLocation });
  extras.forEach((extra) => body.append('extras[]', extra));
  try {
    const response = await fetch(configuration.estimateUrl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body, credentials: 'same-origin', signal: estimateRequest.signal });
    if (requestId !== estimateSequence) return;
    if (!response.ok) throw new Error('estimate request failed');
    const estimate = await response.json() as { days: number; daily_price?: number; base_total?: number; line_items?: { key: string; label: string; amount: number }[]; estimate_total?: number; included_km?: number; excess_km_rate?: number };
    if (!Array.isArray(estimate.line_items) || typeof estimate.estimate_total !== 'number') {
      content.textContent = strings.estimateUnavailable;
      output.hidden = false;
      return;
    }
    const money = (amount: number): string => new Intl.NumberFormat(document.documentElement.lang || undefined, { style: 'currency', currency: 'EUR' }).format(amount);
    const labelFor = (item: { key: string; label: string }): string => ({ vehicle_base_rate: strings.vehicleSubtotal, insurance: strings.insurance, after_hours_pickup: strings.afterHoursFee, inter_airport_transfer: strings.airportTransfer }[item.key] || item.label);
    content.replaceChildren(...estimate.line_items.map((item) => { const row = document.createElement('p'); row.textContent = `${labelFor(item)}: ${money(item.amount)}`; return row; }));
    const days = document.createElement('p'); days.textContent = `${estimate.days} ${strings.rentalDays}`;
    const total = document.createElement('p'); const totalStrong = document.createElement('strong'); totalStrong.textContent = `${strings.indicativeTotal}: ${money(estimate.estimate_total)}`; total.append(totalStrong);
    const mileage = document.createElement('p'); mileage.textContent = `${strings.includedKm}: ${estimate.included_km || 0} km · ${strings.excessKm}: ${money(estimate.excess_km_rate || 0)}/km`;
    content.append(days, total, mileage); output.hidden = false;
  } catch (error) {
    if ((error as DOMException).name === 'AbortError' || requestId !== estimateSequence) return;
    content.replaceChildren();
    const message = document.createElement('p'); message.textContent = strings.estimateError;
    const retry = document.createElement('button'); retry.type = 'button'; retry.className = 'button button--secondary'; retry.textContent = strings.retryEstimate; retry.addEventListener('click', () => { void requestEstimate(output, content, vehicleId, values); });
    content.append(message, retry);
  }
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
