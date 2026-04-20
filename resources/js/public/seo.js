import { translate } from '@/i18n';

function upsertMeta(attribute, key, content) {
  if (!content) {
    return;
  }

  let node = document.head.querySelector(`meta[${attribute}="${key}"]`);

  if (!node) {
    node = document.createElement('meta');
    node.setAttribute(attribute, key);
    document.head.appendChild(node);
  }

  node.setAttribute('content', content);
}

function upsertLink(rel, href) {
  let node = document.head.querySelector(`link[rel="${rel}"]`);

  if (!node) {
    node = document.createElement('link');
    node.setAttribute('rel', rel);
    document.head.appendChild(node);
  }

  node.setAttribute('href', href);
}

function resolveLocale(route, locale) {
  if (locale) {
    return locale;
  }

  if (typeof document !== 'undefined') {
    return document.documentElement.lang || 'en';
  }

  return 'en';
}

export function applyPublicMeta(route, locale) {
  if (typeof document === 'undefined') {
    return;
  }

  const resolvedLocale = resolveLocale(route, locale);
  const titleKey = route.meta?.titleKey || 'seo.default_title';
  const descriptionKey = route.meta?.descriptionKey || 'seo.default_description';

  const title = translate(resolvedLocale, titleKey);
  const description = translate(resolvedLocale, descriptionKey);
  const siteName = translate(resolvedLocale, 'seo.site_name');

  const currentUrl = new URL(route.fullPath || window.location.pathname, window.location.origin).toString();

  document.title = title;

  upsertMeta('name', 'description', description);
  upsertMeta('name', 'robots', 'index,follow');
  upsertMeta('property', 'og:type', 'website');
  upsertMeta('property', 'og:title', title);
  upsertMeta('property', 'og:description', description);
  upsertMeta('property', 'og:url', currentUrl);
  upsertMeta('property', 'og:site_name', siteName);
  upsertMeta('name', 'twitter:card', 'summary_large_image');
  upsertMeta('name', 'twitter:title', title);
  upsertMeta('name', 'twitter:description', description);

  upsertLink('canonical', currentUrl);
}
