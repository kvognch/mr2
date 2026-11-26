const formSelector = 'form[data-form-notices]';
const notices = new WeakMap();
const validatingForms = new WeakSet();
let noticeId = 0;

function findField(form, name) {
  const key = ['captcha', 'g-recaptcha-response'].includes(name) ? 'captcha' : name;
  const group = form.querySelector(`[data-form-field="${CSS.escape(key)}"]`);
  const control = form.elements.namedItem(name);

  return group && {
    group,
    control: control instanceof HTMLElement && control.type !== 'hidden' && key !== 'captcha'
      ? control
      : group.querySelector('button, iframe'),
  };
}

function clearNotice(group) {
  const notice = notices.get(group);
  if (!notice) return;

  const { element, control, previousInvalid } = notice;
  if (control) {
    const descriptions = (control.getAttribute('aria-describedby') || '').split(/\s+/).filter(id => id && id !== element.id);
    if (descriptions.length) control.setAttribute('aria-describedby', descriptions.join(' '));
    else control.removeAttribute('aria-describedby');
    if (previousInvalid === null) control.removeAttribute('aria-invalid');
    else control.setAttribute('aria-invalid', previousInvalid);
  }

  element.remove();
  notices.delete(group);
}

function showNotice({ group, control }, message) {
  clearNotice(group);
  const element = document.createElement('div');
  element.id = `form-notice-${++noticeId}`;
  element.className = 'form-notice form-notice--error form-notice--field';
  element.setAttribute('role', 'alert');
  group.append(element);

  const previousInvalid = control?.getAttribute('aria-invalid') ?? null;
  if (control) {
    const description = control.getAttribute('aria-describedby');
    control.setAttribute('aria-describedby', [description, element.id].filter(Boolean).join(' '));
    control.setAttribute('aria-invalid', 'true');
  }

  notices.set(group, { element, control, previousInvalid });
  element.textContent = message;
}

function focusNotice({ group, control }) {
  control?.focus({ preventScroll: true });
  notices.get(group)?.element.scrollIntoView({ block: 'nearest', inline: 'nearest' });
}

export function showFormErrors(form, errors, focus = true) {
  let fallback = '';
  const pending = new Map();

  for (const [name, messages] of Object.entries(errors || {})) {
    const message = [messages].flat().find(Boolean);
    if (!message) continue;
    const field = findField(form, name);
    if (!field) {
      fallback ||= message;
      continue;
    }

    if (!pending.has(field.group)) pending.set(field.group, { field, message });
  }

  let first;
  for (const group of form.querySelectorAll('[data-form-field]')) {
    const notice = pending.get(group);
    if (!notice) continue;
    showNotice(notice.field, notice.message);
    first ??= notice.field;
  }
  if (focus && first) focusNotice(first);

  return fallback;
}

function clearFormNotices(form) {
  form.querySelectorAll('[data-form-field]').forEach(clearNotice);
}

function missingGroupMessage(form, group) {
  const message = group.dataset.formRequired;
  const name = group.dataset.formValue || group.dataset.formField;
  return message && !form.elements.namedItem(name)?.value ? message : '';
}

function showValidationNotices(form) {
  clearFormNotices(form);

  const errors = {};
  for (const group of form.querySelectorAll('[data-form-field]')) {
    const control = group.querySelector('input:invalid, textarea:invalid, select:invalid');
    const message = control
      ? control.validity.valueMissing ? control.dataset.requiredMessage || 'Заполните это поле.' : control.validationMessage
      : missingGroupMessage(form, group);
    if (message) errors[group.dataset.formField] = message;
  }
  showFormErrors(form, errors);
}

document.addEventListener('invalid', (event) => {
  const control = event.target;
  const form = control.form;
  const group = control.closest('[data-form-field]');
  if (!form?.matches(formSelector) || !group) return;

  event.preventDefault();
  if (validatingForms.has(form)) return;
  validatingForms.add(form);
  // All invalid events from this submission must finish before choosing a focus target.
  setTimeout(() => {
    validatingForms.delete(form);
    showValidationNotices(form);
  }, 0);
}, true);

for (const eventName of ['input', 'change', 'click']) {
  document.addEventListener(eventName, (event) => {
    const group = event.target.closest('[data-form-field]');
    if (!group?.closest(formSelector)) return;
    if (eventName !== 'click' || (group.dataset.formField === 'rating' && event.target.closest('button'))) {
      clearNotice(group);
    }
  });
}

document.addEventListener('submit', (event) => {
  const form = event.target;
  if (!form.matches(formSelector)) return;
  if ([...form.querySelectorAll('[data-form-required]')].some(group => missingGroupMessage(form, group))) {
    event.preventDefault();
    event.stopImmediatePropagation();
    showValidationNotices(form);
    return;
  }
  clearFormNotices(form);
}, true);

document.addEventListener('reset', (event) => {
  if (event.target.matches(formSelector)) clearFormNotices(event.target);
}, true);

export function clearCompletedCaptchaNotices() {
  document.querySelectorAll(`${formSelector} [data-form-field="captcha"]`).forEach(group => {
    const form = group.closest(formSelector);
    if (!missingGroupMessage(form, group)) clearNotice(group);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll(`${formSelector}[data-form-errors]`).forEach(form => {
    const summary = form.querySelector('[data-form-error-summary]');
    if (!summary) return;
    const fallback = showFormErrors(form, JSON.parse(form.dataset.formErrors), false);
    summary.textContent = fallback;
    summary.hidden = !fallback;
  });
});
