import { describe, expect, it } from 'vitest';
import { applyPublicMeta } from '@/public/seo';

describe('public seo metadata', () => {
  it('sets document metadata for a public route', () => {
    applyPublicMeta(
      {
        fullPath: '/faq',
        meta: {
          titleKey: 'seo.faq_title',
          descriptionKey: 'seo.faq_description',
        },
      },
      'en',
    );

    expect(document.title).toContain('FAQ');

    const description = document.head.querySelector('meta[name="description"]');
    const canonical = document.head.querySelector('link[rel="canonical"]');

    expect(description?.getAttribute('content')).toContain('questions');
    expect(canonical?.getAttribute('href')).toContain('/faq');
  });

  it('supports arabic localized metadata', () => {
    applyPublicMeta(
      {
        fullPath: '/contact',
        meta: {
          titleKey: 'seo.contact_title',
          descriptionKey: 'seo.contact_description',
        },
      },
      'ar',
    );

    expect(document.title).toContain('مقصوك');

    const ogTitle = document.head.querySelector('meta[property="og:title"]');
    expect(ogTitle?.getAttribute('content')).toContain('مقصوك');
  });
});
