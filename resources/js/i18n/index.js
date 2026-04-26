import en from '@/locales/en.json';
import ar from '@/locales/ar.json';

export const LOCALE_STORAGE_KEY = 'makasouk_locale';
export const LOCALE_COOKIE_KEY = 'makasouk_locale';
export const SUPPORTED_LOCALES = ['ar', 'en'];

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
    return 'ar';
  }

  const normalized = input.toLowerCase();

  if (normalized.startsWith('ar')) {
    return 'ar';
  }

  if (normalized.startsWith('en')) {
    return 'en';
  }

  return 'ar';
}

export function getDirection(locale) {
  return normalizeLocale(locale) === 'ar' ? 'rtl' : 'ltr';
}

export function readStoredLocale() {
  if (typeof window === 'undefined' && typeof document === 'undefined') {
    return 'ar';
  }

  const stored = typeof window !== 'undefined' ? window.localStorage.getItem(LOCALE_STORAGE_KEY) : null;

  if (stored) {
    return normalizeLocale(stored);
  }

  const cookieLocale = readLocaleFromCookie();
  if (cookieLocale) {
    return cookieLocale;
  }

  if (typeof document !== 'undefined') {
    const rawDocumentLocale = document.documentElement?.lang;
    if (rawDocumentLocale) {
      return normalizeLocale(rawDocumentLocale);
    }
  }

  const browserLocale = typeof window !== 'undefined' ? window.navigator?.language || 'ar' : 'ar';
  return normalizeLocale(browserLocale);
}

export function writeStoredLocale(locale) {
  if (typeof window === 'undefined' && typeof document === 'undefined') {
    return;
  }

  const normalized = normalizeLocale(locale);

  if (typeof window !== 'undefined') {
    window.localStorage.setItem(LOCALE_STORAGE_KEY, normalized);
  }

  writeLocaleCookie(normalized);
}

export function readLocaleFromCookie() {
  if (typeof document === 'undefined') {
    return null;
  }

  const cookie = document.cookie
    .split(';')
    .map((item) => item.trim())
    .find((item) => item.startsWith(`${LOCALE_COOKIE_KEY}=`));

  if (!cookie) {
    return null;
  }

  const value = decodeURIComponent(cookie.split('=')[1] || '');
  return normalizeLocale(value);
}

export function writeLocaleCookie(locale) {
  if (typeof document === 'undefined') {
    return;
  }

  const normalized = normalizeLocale(locale);
  const maxAge = 60 * 60 * 24 * 365;
  document.cookie = `${LOCALE_COOKIE_KEY}=${encodeURIComponent(normalized)}; path=/; max-age=${maxAge}; samesite=lax`;
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
