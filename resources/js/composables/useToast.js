import { useToastStore } from '@/stores/toast';

export function useToast() {
  const toastStore = useToastStore();

  return {
    pushToast: toastStore.push,
    successToast: toastStore.success,
    errorToast: toastStore.error,
    warningToast: toastStore.warning,
    infoToast: toastStore.info,
    removeToast: toastStore.remove,
    clearToasts: toastStore.clear,
  };
}
