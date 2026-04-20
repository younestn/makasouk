export function homeRouteForRole(role) {
  if (role === 'customer') {
    return { name: 'customerDashboard' };
  }

  if (role === 'tailor') {
    return { name: 'tailorDashboard' };
  }

  return { name: 'login' };
}
