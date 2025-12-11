<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
    <div class="bg-white rounded-lg p-6 flex flex-col items-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
      <p class="text-gray-700">{{ message }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  message: {
    type: String,
    default: 'Memuat...',
  },
});
</script>

