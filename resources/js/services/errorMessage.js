import { readStoredLocale, translate } from '@/i18n';

export function getErrorMessage(error, fallback = '') {
  const locale = readStoredLocale();
  const resolvedFallback = fallback || translate(locale, 'messages.unexpected_client_error');

  if (!error) {
    return resolvedFallback;
  }

  if (typeof error === 'string') {
    return error;
  }

  if (error.message) {
    return error.message;
  }

  if (error.errors && typeof error.errors === 'object') {
    const firstFieldErrors = Object.values(error.errors).find((messages) => Array.isArray(messages) && messages.length > 0);

    if (firstFieldErrors) {
      return String(firstFieldErrors[0]);
    }
  }

  return resolvedFallback;
}
