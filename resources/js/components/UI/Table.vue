<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th
            v-for="column in columns"
            :key="column.key"
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
          >
            {{ column.label }}
          </th>
          <th v-if="hasActions" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
            Aksi
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-if="loading">
          <td :colspan="columns.length + (hasActions ? 1 : 0)" class="px-6 py-4 text-center">
            <div class="flex justify-center items-center">
              <span class="animate-spin">⏳</span>
              <span class="ml-2">Memuat data...</span>
            </div>
          </td>
        </tr>
        <tr v-else-if="data.length === 0">
          <td :colspan="columns.length + (hasActions ? 1 : 0)" class="px-6 py-4 text-center text-gray-500">
            Tidak ada data
          </td>
        </tr>
        <tr v-else v-for="(row, index) in data" :key="index" class="hover:bg-gray-50">
          <td
            v-for="column in columns"
            :key="column.key"
            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
          >
            <slot :name="`cell-${column.key}`" :row="row" :value="getValue(row, column.key)">
              {{ formatValue(getValue(row, column.key), column.format) }}
            </slot>
          </td>
          <td v-if="hasActions" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <slot name="actions" :row="row" :index="index" />
          </td>
        </tr>
      </tbody>
    </table>
    <div v-if="pagination && !loading" class="mt-4 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        Menampilkan {{ pagination.from }} sampai {{ pagination.to }} dari {{ pagination.total }} data
      </div>
      <div class="flex gap-2">
        <Button
          :disabled="!pagination.prev_page_url"
          @click="$emit('page-change', pagination.current_page - 1)"
        >
          Sebelumnya
        </Button>
        <Button
          :disabled="!pagination.next_page_url"
          @click="$emit('page-change', pagination.current_page + 1)"
        >
          Selanjutnya
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import Button from './Button.vue';

const props = defineProps({
  columns: {
    type: Array,
    required: true,
  },
  data: {
    type: Array,
    default: () => [],
  },
  loading: Boolean,
  pagination: Object,
  hasActions: Boolean,
});

defineEmits(['page-change']);

function getValue(row, key) {
  return key.split('.').reduce((obj, k) => obj?.[k], row);
}

function formatValue(value, format) {
  if (value === null || value === undefined) return '-';
  
  if (format === 'currency') {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
  }
  if (format === 'number') {
    return new Intl.NumberFormat('id-ID').format(value);
  }
  if (format === 'date') {
    return new Date(value).toLocaleDateString('id-ID');
  }
  if (format === 'datetime') {
    return new Date(value).toLocaleString('id-ID');
  }
  
  return value;
}
</script>

