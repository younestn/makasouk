import en from '@/locales/en.json';
import ar from '@/locales/ar.json';

export const LOCALE_STORAGE_KEY = 'makasouk_locale';
export const SUPPORTED_LOCALES = ['en', 'ar'];

const dictionaries = {
  en,
  ar,
};

function getFromPath(source, path) {
  return String(path)
    .split('.')
    .reduce((acc, segment) => (acc && typeof acc === 'object' ? acc[segment] : undefined), source);
}

export function normalizeLocale(input) {
  if (!input || typeof input !== 'string') {
    return 'en';
  }

  return input.toLowerCase().startsWith('ar') ? 'ar' : 'en';
}

export function getDirection(locale) {
  return normalizeLocale(locale) === 'ar' ? 'rtl' : 'ltr';
}

export function readStoredLocale() {
  if (typeof window === 'undefined') {
    return 'en';
  }

  const stored = window.localStorage.getItem(LOCALE_STORAGE_KEY);

  if (stored) {
    return normalizeLocale(stored);
  }

  const browserLocale = window.navigator?.language || 'en';
  return normalizeLocale(browserLocale);
}

export function writeStoredLocale(locale) {
  if (typeof window === 'undefined') {
    return;
  }

  window.localStorage.setItem(LOCALE_STORAGE_KEY, normalizeLocale(locale));
}

export function translate(locale, key, params = {}) {
  const normalizedLocale = normalizeLocale(locale);
  const dictionary = dictionaries[normalizedLocale] || dictionaries.en;
  const fallbackDictionary = dictionaries.en;

  const template = getFromPath(dictionary, key) ?? getFromPath(fallbackDictionary, key) ?? key;

  return String(template).replace(/\{(\w+)\}/g, (_, token) => {
    if (params[token] === undefined || params[token] === null) {
      return `{${token}}`;
    }

    return String(params[token]);
  });
}
