document.documentElement.classList.add('js');

const toggle = document.querySelector<HTMLButtonElement>('[data-menu-toggle]');
const navigation = document.querySelector<HTMLElement>('[data-primary-navigation]');
if (toggle && navigation) {
  toggle.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', String(open));
    navigation.classList.toggle('is-open', open);
  });
}

type ReservationConfig = { reservationUrl: string };
type RentacarWindow = Window & typeof globalThis & { rentacarVenezia?: ReservationConfig };
const modal = document.querySelector<HTMLElement>('[data-reservation-modal]');
const form = document.querySelector<HTMLFormElement>('[data-reservation-form]');
const formWrap = document.querySelector<HTMLElement>('[data-reservation-form-wrap]');
const success = document.querySelector<HTMLElement>('[data-reservation-success]');
const errorBox = document.querySelector<HTMLElement>('[data-reservation-errors]');
let trigger: HTMLElement | null = null;

const focusable = (): HTMLElement[] => modal ? Array.from(modal.querySelectorAll<HTMLElement>('a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])')).filter((item) => !item.closest('[hidden]')) : [];
const close = (): void => { if (!modal) return; modal.hidden = true; document.body.classList.remove('reservation-open'); trigger?.focus(); };
const open = (button: HTMLElement): void => {
  if (!modal || !form) return;
  trigger = button; modal.hidden = false; document.body.classList.add('reservation-open');
  const data = button.dataset;
  const vehicleId = form.querySelector<HTMLInputElement>('[data-reservation-vehicle-id]');
  if (vehicleId) vehicleId.value = data.vehicleId || '';
  const title = modal.querySelector<HTMLElement>('[data-reservation-title]'); if (title) title.textContent = data.vehicleTitle || '';
  const specifications = modal.querySelector<HTMLElement>('[data-reservation-specifications]'); if (specifications) specifications.textContent = data.vehicleSpecifications || '';
  const prices = modal.querySelector<HTMLElement>('[data-reservation-prices]'); if (prices) prices.textContent = data.vehiclePriceBands || '';
  const image = modal.querySelector<HTMLElement>('[data-reservation-image]'); if (image) { image.replaceChildren(); if (data.vehicleImage) { const vehicleImage = document.createElement('img'); vehicleImage.src = data.vehicleImage; vehicleImage.alt = ''; image.append(vehicleImage); } }
  requestAnimationFrame(() => (modal.querySelector<HTMLElement>('[data-reservation-close]') || form).focus());
};

document.querySelectorAll<HTMLElement>('[data-reservation-trigger]').forEach((button) => button.addEventListener('click', () => open(button)));
modal?.querySelectorAll<HTMLElement>('[data-reservation-close]').forEach((button) => button.addEventListener('click', close));
document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && modal && !modal.hidden) close();
  if (event.key === 'Tab' && modal && !modal.hidden) { const items = focusable(); if (!items.length) return; const first = items[0], last = items[items.length - 1]; if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); } else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); } }
});

const displayErrors = (errors: Record<string, string[]>): void => {
  if (!form || !errorBox) return; const messages = Object.values(errors).flat();
  form.querySelectorAll('[aria-invalid="true"]').forEach((field) => { field.removeAttribute('aria-invalid'); field.removeAttribute('aria-describedby'); });
  Object.keys(errors).forEach((name) => { const field = form.elements.namedItem(name) as HTMLElement | null; field?.setAttribute('aria-invalid', 'true'); field?.setAttribute('aria-describedby', 'reservation-errors'); });
  errorBox.textContent = messages.join(' '); errorBox.focus();
};
if (form) form.addEventListener('submit', async (event) => {
  if (!window.fetch) return;
  event.preventDefault();
  if (!form.reportValidity()) return;
  const submit = form.querySelector<HTMLButtonElement>('[type="submit"]'); if (submit) { submit.disabled = true; submit.textContent = 'Sending…'; }
  const data = new FormData(form); data.set('rentacar_ajax', '1');
  try {
    const response = await fetch((window as RentacarWindow).rentacarVenezia?.reservationUrl || form.action, { method: 'POST', body: data, credentials: 'same-origin' });
    const body = await response.json() as { success: boolean; data: { reference?: string; errors?: Record<string, string[]> } };
    if (!body.success) { displayErrors(body.data.errors || { request: ['Please review the form and try again.'] }); return; }
    formWrap?.setAttribute('hidden', ''); success?.removeAttribute('hidden');
    const reference = modal?.querySelector<HTMLElement>('[data-reservation-reference]'); if (reference && body.data.reference) reference.textContent = `Reference: ${body.data.reference}`;
    modal?.querySelector<HTMLElement>('[data-reservation-success] h2')?.focus();
  } catch { displayErrors({ request: ['We could not send the request. Please try again.'] }); } finally { if (submit) { submit.disabled = false; submit.textContent = 'Send reservation request'; } }
});
