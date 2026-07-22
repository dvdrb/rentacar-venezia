document.documentElement.classList.add('js');

const toggle = document.querySelector<HTMLButtonElement>('[data-menu-toggle]');
const navigation = document.querySelector<HTMLElement>('[data-primary-navigation]');

if (toggle && navigation) {
  const closeMenu = (): void => {
    toggle.setAttribute('aria-expanded', 'false');
    navigation.classList.remove('is-open');
  };

  toggle.addEventListener('click', () => {
    const isOpen = toggle.getAttribute('aria-expanded') === 'true';
    toggle.setAttribute('aria-expanded', String(!isOpen));
    navigation.classList.toggle('is-open', !isOpen);
  });

  document.addEventListener('keydown', (event: KeyboardEvent) => {
    if (event.key === 'Escape' && navigation.classList.contains('is-open')) {
      closeMenu();
      toggle.focus();
    }
  });
}

type Estimate = {
  available: boolean;
  days: number;
  currency?: string;
  daily_price?: number;
  base_total?: number;
  disclaimer: string;
};

type RentacarWindow = Window & typeof globalThis & {
  rentacarVenezia?: {
    estimateUrl: string;
    whatsappDestination: string;
    estimateUnavailable: string;
  };
};

const requestCard = document.querySelector<HTMLElement>('[data-request-card]');
const requestForm = document.querySelector<HTMLFormElement>('[data-request-form]');
const requestReview = document.querySelector<HTMLElement>('[data-request-review]');
const estimateResult = document.querySelector<HTMLElement>('[data-estimate-result]');
const whatsappLink = document.querySelector<HTMLAnchorElement>('[data-whatsapp-link]');
const whatsappUnconfigured = document.querySelector<HTMLElement>('[data-whatsapp-unconfigured]');
const config = (window as RentacarWindow).rentacarVenezia;

const value = (form: HTMLFormElement, name: string): string => {
  const element = form.elements.namedItem(name) as HTMLInputElement | HTMLTextAreaElement | null;
  return element ? element.value.trim() : '';
};

const checked = (form: HTMLFormElement, name: string): boolean => {
  const element = form.elements.namedItem(name) as HTMLInputElement | null;
  return Boolean(element?.checked);
};

const euro = (amount?: number): string => new Intl.NumberFormat(document.documentElement.lang || 'en', {
  style: 'currency',
  currency: 'EUR',
  maximumFractionDigits: 2,
}).format(amount || 0);

const renderEstimate = (estimate: Estimate | null): void => {
  if (!estimateResult) return;

  estimateResult.replaceChildren();
  const title = document.createElement('p');
  title.className = 'estimate-result__label';
  title.textContent = estimate?.available ? `Indicative estimate · ${estimate.days} day${estimate.days === 1 ? '' : 's'}` : 'Estimate to be confirmed';
  estimateResult.append(title);

  if (estimate?.available) {
    const total = document.createElement('strong');
    total.className = 'estimate-result__total';
    total.textContent = euro(estimate.base_total);
    estimateResult.append(total);

    const detail = document.createElement('span');
    detail.textContent = `${euro(estimate.daily_price)} per day · base vehicle rate only`;
    estimateResult.append(detail);
  }

  const disclaimer = document.createElement('p');
  disclaimer.className = 'estimate-result__disclaimer';
  disclaimer.textContent = estimate?.disclaimer || config?.estimateUnavailable || 'Our team will confirm the final price.';
  estimateResult.append(disclaimer);
};

const whatsappMessage = (form: HTMLFormElement, estimate: Estimate | null): string => [
  'Hello, I would like to request a rental car.',
  '',
  `Preferred vehicle: ${requestCard?.dataset.vehicleTitle || document.title}`,
  `Similar vehicle accepted: ${checked(form, 'similar_vehicle') ? 'Yes' : 'No'}`,
  '',
  'Pickup:',
  `${value(form, 'pickup_location')} — ${value(form, 'pickup_date')} ${value(form, 'pickup_time')}`,
  'Return:',
  `${value(form, 'dropoff_location')} — ${value(form, 'return_date')} ${value(form, 'return_time')}`,
  '',
  `Indicative website estimate: ${estimate?.available ? euro(estimate.base_total) : 'To be confirmed'}`,
  '',
  `Name: ${value(form, 'full_name')}`,
  `Phone / WhatsApp: ${value(form, 'phone')}`,
  `Email: ${value(form, 'email')}`,
  value(form, 'message') ? `Message: ${value(form, 'message')}` : '',
].filter(Boolean).join('\n');

if (requestCard && requestForm && requestReview && config) {
  requestCard.dataset.vehicleTitle = document.querySelector('h1')?.textContent?.trim() || '';

  requestForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!requestForm.reportValidity()) return;

    const response = await fetch(config.estimateUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        vehicle_id: Number(requestCard.dataset.vehicleId),
        pickup_date: value(requestForm, 'pickup_date'),
        pickup_time: value(requestForm, 'pickup_time'),
        return_date: value(requestForm, 'return_date'),
        return_time: value(requestForm, 'return_time'),
      }),
    });

    let estimate: Estimate | null = null;
    if (response.ok) estimate = await response.json() as Estimate;
    renderEstimate(estimate);
    requestReview.hidden = false;
    requestReview.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    const destination = config.whatsappDestination.replace(/\D/g, '');
    if (destination && whatsappLink) {
      whatsappLink.href = `https://wa.me/${destination}?text=${encodeURIComponent(whatsappMessage(requestForm, estimate))}`;
      whatsappLink.hidden = false;
      if (whatsappUnconfigured) whatsappUnconfigured.hidden = true;
    }
  });
}
