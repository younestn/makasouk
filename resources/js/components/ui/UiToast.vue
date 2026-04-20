<template>
  <div class="toast-stack" aria-live="polite" aria-atomic="false">
    <transition-group name="toast-fade" tag="div" class="stack">
      <article
        v-for="item in toastStore.items"
        :key="item.id"
        class="toast-item"
        :class="`toast-item--${item.variant}`"
      >
        <div class="toast-content">
          <strong v-if="item.title" class="toast-title">{{ item.title }}</strong>
          <p class="toast-message">{{ item.message }}</p>
        </div>

        <button class="toast-close" type="button" @click="toastStore.remove(item.id)">
          ×
        </button>
      </article>
    </transition-group>
  </div>
</template>

<script setup>
import { useToastStore } from '@/stores/toast';

const toastStore = useToastStore();
</script>
