<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Hasil HPE</h1>
      <Button
        v-if="canCalculate"
        @click="$router.push('/hpe/calculate')"
      >
        + Hitung HPE
      </Button>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b border-gray-200 flex items-center space-x-4">
        <Select
          v-model="productFilter"
          :options="productOptions"
          placeholder="Filter Produk"
          @update:modelValue="loadResults"
        />
        <Select
          v-model="statusFilter"
          :options="statusOptions"
          placeholder="Filter Status"
          @update:modelValue="loadResults"
        />
      </div>

      <Table
        :columns="columns"
        :data="results"
        :loading="loading"
        :pagination="pagination"
        has-actions
        @page-change="handlePageChange"
      >
        <template #cell-calculated_at="{ value }">
          {{ new Date(value).toLocaleString('id-ID') }}
        </template>
        <template #cell-total_with_margin="{ value }">
          {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value) }}
        </template>
        <template #actions="{ row }">
          <router-link
            :to="`/hpe/results/${row.id}`"
            class="text-blue-600 hover:text-blue-900"
          >
            Lihat Detail
          </router-link>
        </template>
      </Table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useApi } from '@/composables/useApi';
import Button from '@/components/UI/Button.vue';
import Select from '@/components/UI/Select.vue';
import Table from '@/components/UI/Table.vue';

const authStore = useAuthStore();
const api = useApi();

const results = ref([]);
const products = ref([]);
const loading = ref(false);
const productFilter = ref('');
const statusFilter = ref('');
const pagination = ref(null);

const canCalculate = computed(() => ['admin', 'finance'].includes(authStore.user?.role));

const columns = [
  { key: 'product.name', label: 'Produk' },
  { key: 'calculated_at', label: 'Tanggal' },
  { key: 'total_with_margin', label: 'Total dengan Margin' },
  { key: 'margin_percent', label: 'Margin (%)' },
];

const productOptions = computed(() => {
  const options = [{ value: '', label: 'Semua Produk' }];
  products.value.forEach(p => {
    options.push({ value: p.id, label: `${p.code} - ${p.name}` });
  });
  return options;
});

const statusOptions = [
  { value: '', label: 'Semua Status' },
  { value: 'draft', label: 'Draft' },
  { value: 'approved', label: 'Approved' },
];

onMounted(async () => {
  await loadProducts();
  await loadResults();
});

const loadProducts = async () => {
  const { data } = await api.get('/products?per_page=1000');
  if (data) {
    products.value = data.data || [];
  }
};

const loadResults = async (page = 1) => {
  loading.value = true;
  const params = new URLSearchParams({
    page: page.toString(),
    per_page: '15',
  });
  
  if (productFilter.value) params.append('product_id', productFilter.value);
  if (statusFilter.value) params.append('status', statusFilter.value);
  
  const { data } = await api.get(`/hpe/results?${params}`);
  
  if (data) {
    results.value = data.data || [];
    pagination.value = {
      current_page: data.current_page,
      from: data.from,
      to: data.to,
      total: data.total,
      last_page: data.last_page,
      prev_page_url: data.prev_page_url,
      next_page_url: data.next_page_url,
    };
  }
  
  loading.value = false;
};

const handlePageChange = (page) => {
  loadResults(page);
};
</script>

