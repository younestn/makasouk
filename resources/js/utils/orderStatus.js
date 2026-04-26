import { readStoredLocale, translate } from '@/i18n';

const STATUS_META = {
  searching_for_tailor: { key: 'orders.status.searching_for_tailor', className: 'badge-info' },
  no_tailors_available: { key: 'orders.status.no_tailors_available', className: 'badge-warning' },
  accepted: { key: 'orders.status.accepted', className: 'badge-info' },
  processing: { key: 'orders.status.processing', className: 'badge-warning' },
  ready_for_delivery: { key: 'orders.status.ready_for_delivery', className: 'badge-warning' },
  completed: { key: 'orders.status.completed', className: 'badge-success' },
  cancelled_by_customer: { key: 'orders.status.cancelled_by_customer', className: 'badge-danger' },
  cancelled_by_tailor: { key: 'orders.status.cancelled_by_tailor', className: 'badge-danger' },
  cancelled: { key: 'orders.status.cancelled', className: 'badge-danger' },
};

export function statusMeta(status) {
  const locale = readStoredLocale();
  const fallbackLabel = status || translate(locale, 'orders.status.unknown');
  const meta = STATUS_META[status];

  if (!meta) {
    return { label: fallbackLabel, className: 'badge-neutral' };
  }

  return {
    label: translate(locale, meta.key),
    className: meta.className,
  };
}
