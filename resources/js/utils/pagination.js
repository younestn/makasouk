export function normalizePagination(payload, defaults = {}) {
  const meta = payload?.meta || {};

  return {
    currentPage: Number(meta.current_page ?? defaults.currentPage ?? 1),
    lastPage: Number(meta.last_page ?? defaults.lastPage ?? 1),
    perPage: Number(meta.per_page ?? defaults.perPage ?? 20),
    total: Number(meta.total ?? defaults.total ?? 0),
    from: meta.from ?? defaults.from ?? null,
    to: meta.to ?? defaults.to ?? null,
    scope: meta.scope ?? defaults.scope ?? '',
  };
}

export function defaultPagination(overrides = {}) {
  return {
    currentPage: 1,
    lastPage: 1,
    perPage: 20,
    total: 0,
    from: 0,
    to: 0,
    scope: '',
    ...overrides,
  };
}
