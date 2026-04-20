export function getErrorMessage(error, fallback = 'Something went wrong.') {
  if (!error) {
    return fallback;
  }

  if (typeof error === 'string') {
    return error;
  }

  if (error.message) {
    return error.message;
  }

  if (error.errors && typeof error.errors === 'object') {
    const firstFieldErrors = Object.values(error.errors).find((messages) => Array.isArray(messages) && messages.length > 0);

    if (firstFieldErrors) {
      return String(firstFieldErrors[0]);
    }
  }

  return fallback;
}
