document.documentElement.classList.add('js');

type ReservationStrings = {
  menuOpen: string;
  menuClose: string;
  sending: string;
  sendRequest: string;
  reviewForm: string;
  deliveryFailed: string;
  reference: string;
};

type ReservationConfig = {
  reservationUrl: string;
  strings: ReservationStrings;
};

type RentacarWindow = Window & typeof globalThis & { rentacarVenezia?: ReservationConfig };

const configuration = (window as RentacarWindow).rentacarVenezia;
const strings: ReservationStrings = configuration?.strings || {
  menuOpen: 'Open navigation',
  menuClose: 'Close navigation',
  sending: 'Sending…',
  sendRequest: 'Send reservation request',
  reviewForm: 'Please review the form and try again.',
  deliveryFailed: 'We could not send the request. Please try again.',
  reference: 'Reference: %s',
};

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
  const dropoff = tripForm.elements.namedItem('dropoff_location') as HTMLSelectElement | null;
  const returnDifferent = tripForm.querySelector<HTMLInputElement>('[data-return-different]');
  const returnLocation = tripForm.querySelector<HTMLElement>('[data-return-location]');
  const quickLocations = Array.from(tripForm.querySelectorAll<HTMLButtonElement>('[data-trip-location]'));

  const syncTripLocations = (): void => {
    const different = Boolean(returnDifferent?.checked);
    if (returnLocation) returnLocation.hidden = !different;
    if (!different && pickup && dropoff) dropoff.value = pickup.value;

    quickLocations.forEach((button) => {
      button.setAttribute('aria-pressed', String(button.dataset.tripLocation === pickup?.value));
    });
  };

  pickup?.addEventListener('change', syncTripLocations);
  returnDifferent?.addEventListener('change', syncTripLocations);
  quickLocations.forEach((button) => {
    button.addEventListener('click', () => {
      if (pickup) pickup.value = button.dataset.tripLocation || pickup.value;
      syncTripLocations();
    });
  });
  syncTripLocations();
}

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
const inertTargets = Array.from(document.querySelectorAll<HTMLElement>('[data-site-header], #main-content, .site-footer'));
let trigger: HTMLElement | null = null;

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
  ? Array.from(modal.querySelectorAll<HTMLElement>('a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])')).filter((item) => !item.closest('[hidden]'))
  : [];

const closeModal = (): void => {
  if (!modal) return;
  modal.hidden = true;
  document.body.classList.remove('reservation-open');
  setBackgroundInert(false);
  trigger?.focus();
};

const resetVisibleState = (): void => {
  formWrap?.removeAttribute('hidden');
  success?.setAttribute('hidden', '');
  if (errorBox) errorBox.textContent = '';
};

if (modal) {
  const initiallyOpen = modal.dataset.reservationInitialOpen === '1';
  modal.setAttribute('role', 'dialog');
  modal.setAttribute('aria-modal', 'true');
  modal.hidden = !initiallyOpen;
  modal.classList.remove('reservation-modal--inline');
  if (initiallyOpen) {
    document.body.classList.add('reservation-open');
    setBackgroundInert(true);
    requestAnimationFrame(() => modal.querySelector<HTMLElement>('[data-reservation-close]')?.focus());
  }
}

const openModal = (button: HTMLElement): void => {
  if (!modal || !form) return;
  setNavigation(false);
  trigger = button;
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
};

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

const nativeErrors = (): Record<string, string[]> => {
  const errors: Record<string, string[]> = {};
  if (!form) return errors;
  Array.from(form.elements).forEach((element) => {
    if (!(element instanceof HTMLInputElement || element instanceof HTMLTextAreaElement || element instanceof HTMLSelectElement)) return;
    if (!element.name || element.type === 'hidden' || element.validity.valid) return;
    errors[element.name] = [element.validationMessage || strings.reviewForm];
  });
  return errors;
};

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

    const clientErrors = nativeErrors();
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
      const reference = modal?.querySelector<HTMLElement>('[data-reservation-reference]');
      if (reference && body.data.reference) {
        reference.textContent = strings.reference.replace('%s', body.data.reference);
      }
      modal?.querySelector<HTMLElement>('[data-reservation-success] h2')?.focus();
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
