<template>
  <div class="mb-4">
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <select
      :id="id"
      :value="modelValue"
      :required="required"
      :disabled="disabled"
      :class="selectClasses"
      @change="$emit('update:modelValue', $event.target.value)"
    >
      <option v-if="placeholder" value="">{{ placeholder }}</option>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: [String, Number],
  label: String,
  options: {
    type: Array,
    required: true,
  },
  placeholder: String,
  required: Boolean,
  disabled: Boolean,
  error: String,
  id: String,
});

defineEmits(['update:modelValue']);

const selectClasses = computed(() => {
  const base = 'w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent';
  const errorClass = props.error ? 'border-red-500' : 'border-gray-300';
  const disabledClass = props.disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white';
  return `${base} ${errorClass} ${disabledClass}`;
});
</script>

