import { readStoredLocale, translate } from '@/i18n';

const STATUS_META = {
  placed: { key: 'custom_orders.status.placed', className: 'badge-neutral' },
  admin_review: { key: 'custom_orders.status.admin_review', className: 'badge-info' },
  quoted: { key: 'custom_orders.status.quoted', className: 'badge-warning' },
  quote_accepted: { key: 'custom_orders.status.quote_accepted', className: 'badge-success' },
  quote_rejected: { key: 'custom_orders.status.quote_rejected', className: 'badge-danger' },
  tailor_assignment_pending: { key: 'custom_orders.status.tailor_assignment_pending', className: 'badge-warning' },
  assigned_to_tailor: { key: 'custom_orders.status.assigned_to_tailor', className: 'badge-info' },
  work_started: { key: 'custom_orders.status.work_started', className: 'badge-info' },
  cutting_started: { key: 'custom_orders.status.cutting_started', className: 'badge-info' },
  sewing_started: { key: 'custom_orders.status.sewing_started', className: 'badge-info' },
  completed: { key: 'custom_orders.status.completed', className: 'badge-success' },
  preparing: { key: 'custom_orders.status.preparing', className: 'badge-warning' },
  sent_to_shipping_center: { key: 'custom_orders.status.sent_to_shipping_center', className: 'badge-warning' },
  arrived: { key: 'custom_orders.status.arrived', className: 'badge-info' },
  received: { key: 'custom_orders.status.received', className: 'badge-success' },
  delivered: { key: 'custom_orders.status.delivered', className: 'badge-success' },
  cancelled: { key: 'custom_orders.status.cancelled', className: 'badge-danger' },
};

export function customOrderStatusMeta(status) {
  const locale = readStoredLocale();
  const fallbackLabel = status || translate(locale, 'custom_orders.status.unknown');
  const meta = STATUS_META[status];

  if (!meta) {
    return { label: fallbackLabel, className: 'badge-neutral' };
  }

  return {
    label: translate(locale, meta.key),
    className: meta.className,
  };
}
