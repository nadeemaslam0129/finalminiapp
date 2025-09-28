<template>
  <div class="card p-4 rounded shadow-sm">
    <h2 class="text-lg font-medium">Balance</h2>
    <div class="mt-2 text-3xl font-semibold">
      {{ formattedBalance }}
    </div>
    <div class="text-sm text-gray-500 mt-1">Updated in real-time</div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { store } from '../store';

const formattedBalance = computed(() => {
  return Number(store.user.balance || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
});
</script>
