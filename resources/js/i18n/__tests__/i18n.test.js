import { describe, expect, it } from 'vitest';
import { getDirection, normalizeLocale, translate } from '@/i18n';

describe('i18n helpers', () => {
  it('normalizes locale values', () => {
    expect(normalizeLocale('ar-MA')).toBe('ar');
    expect(normalizeLocale('en-US')).toBe('en');
    expect(normalizeLocale('')).toBe('ar');
  });

  it('returns direction by locale', () => {
    expect(getDirection('ar')).toBe('rtl');
    expect(getDirection('en')).toBe('ltr');
  });

  it('translates known keys with fallback', () => {
    expect(translate('en', 'common.catalog')).toBe('Catalog');
    expect(translate('ar', 'common.catalog')).toBe('الكتالوج');
    expect(translate('en', 'missing.key')).toBe('missing.key');
  });
});
