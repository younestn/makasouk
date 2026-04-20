const STATUS_META = {
  searching_for_tailor: { label: 'Searching', className: 'badge-info' },
  no_tailors_available: { label: 'No Tailors', className: 'badge-warning' },
  accepted: { label: 'Accepted', className: 'badge-info' },
  processing: { label: 'Processing', className: 'badge-warning' },
  ready_for_delivery: { label: 'Ready for Delivery', className: 'badge-warning' },
  completed: { label: 'Completed', className: 'badge-success' },
  cancelled_by_customer: { label: 'Cancelled by Customer', className: 'badge-danger' },
  cancelled_by_tailor: { label: 'Cancelled by Tailor', className: 'badge-danger' },
  cancelled: { label: 'Cancelled', className: 'badge-danger' },
};

export function statusMeta(status) {
  return STATUS_META[status] || { label: status || 'Unknown', className: 'badge-neutral' };
}
