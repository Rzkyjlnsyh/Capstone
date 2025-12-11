<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Laporan & Ekspor Data</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Export HPE Results</h2>
        <form @submit.prevent="handleExportHpe">
          <Select
            v-model="hpeForm.type"
            label="Format"
            :options="exportTypeOptions"
            required
          />
          <Select
            v-model="hpeForm.product_id"
            label="Produk (opsional)"
            :options="productOptions"
          />
          <Input
            v-model="hpeForm.date_from"
            label="Dari Tanggal (opsional)"
            type="date"
          />
          <Input
            v-model="hpeForm.date_to"
            label="Sampai Tanggal (opsional)"
            type="date"
          />
          <div class="mt-6">
            <Button type="submit" :loading="exportingHpe" class="w-full">
              Export HPE
            </Button>
          </div>
        </form>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Export Products</h2>
        <form @submit.prevent="handleExportProducts">
          <Select
            v-model="productsForm.type"
            label="Format"
            :options="exportTypeOptions"
            required
          />
          <div class="mt-6">
            <Button type="submit" :loading="exportingProducts" class="w-full">
              Export Products
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import Input from '@/components/UI/Input.vue';
import Select from '@/components/UI/Select.vue';
import Button from '@/components/UI/Button.vue';

const api = useApi();
const toast = useToast();

const products = ref([]);
const hpeForm = ref({
  type: 'pdf',
  product_id: '',
  date_from: '',
  date_to: '',
});
const productsForm = ref({
  type: 'pdf',
});
const exportingHpe = ref(false);
const exportingProducts = ref(false);

const exportTypeOptions = [
  { value: 'pdf', label: 'PDF' },
  { value: 'excel', label: 'Excel' },
];

const productOptions = computed(() => {
  const options = [{ value: '', label: 'Semua Produk' }];
  products.value.forEach(p => {
    options.push({ value: p.id, label: `${p.code} - ${p.name}` });
  });
  return options;
});

onMounted(async () => {
  await loadProducts();
});

const loadProducts = async () => {
  const { data } = await api.get('/products?per_page=1000');
  if (data) {
    products.value = data.data || [];
  }
};

const handleExportHpe = async () => {
  exportingHpe.value = true;
  
  const params = new URLSearchParams({
    type: hpeForm.value.type,
  });
  
  if (hpeForm.value.product_id) params.append('product_id', hpeForm.value.product_id);
  if (hpeForm.value.date_from) params.append('date_from', hpeForm.value.date_from);
  if (hpeForm.value.date_to) params.append('date_to', hpeForm.value.date_to);
  
  try {
    const url = `/api/reporting/export-hpe?${params}`;
    window.open(url, '_blank');
    toast.success('Export dimulai');
  } catch (error) {
    toast.error('Gagal export');
  } finally {
    exportingHpe.value = false;
  }
};

const handleExportProducts = async () => {
  exportingProducts.value = true;
  
  const params = new URLSearchParams({
    type: productsForm.value.type,
  });
  
  try {
    const url = `/api/reporting/export-products?${params}`;
    window.open(url, '_blank');
    toast.success('Export dimulai');
  } catch (error) {
    toast.error('Gagal export');
  } finally {
    exportingProducts.value = false;
  }
};
</script>

