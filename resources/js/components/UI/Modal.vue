<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="$emit('close')"></div>
          
          <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
          
          <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
            :class="sizeClass"
          >
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">{{ title }}</h3>
                <button
                  @click="$emit('close')"
                  class="text-gray-400 hover:text-gray-500 focus:outline-none"
                >
                  <span class="sr-only">Tutup</span>
                  ✕
                </button>
              </div>
              <div>
                <slot />
              </div>
            </div>
            <div v-if="showFooter" class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <slot name="footer">
                <Button @click="$emit('close')">Tutup</Button>
              </slot>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';
import Button from './Button.vue';

const props = defineProps({
  show: Boolean,
  title: String,
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value),
  },
  showFooter: {
    type: Boolean,
    default: true,
  },
});

defineEmits(['close']);

const sizeClass = computed(() => {
  const sizes = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-lg',
    lg: 'sm:max-w-2xl',
    xl: 'sm:max-w-4xl',
  };
  return sizes[props.size];
});
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>

