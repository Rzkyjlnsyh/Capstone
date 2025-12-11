<template>
  <div class="mb-4">
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :class="inputClasses"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur', $event)"
    />
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-if="hint && !error" class="mt-1 text-sm text-gray-500">{{ hint }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: [String, Number],
  label: String,
  type: {
    type: String,
    default: 'text',
  },
  placeholder: String,
  required: Boolean,
  disabled: Boolean,
  error: String,
  hint: String,
  id: String,
});

defineEmits(['update:modelValue', 'blur']);

const inputClasses = computed(() => {
  const base = 'w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent';
  const errorClass = props.error ? 'border-red-500' : 'border-gray-300';
  const disabledClass = props.disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white';
  return `${base} ${errorClass} ${disabledClass}`;
});
</script>

