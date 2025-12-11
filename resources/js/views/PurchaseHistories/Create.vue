<template>
  <div>
    <div class="mb-6">
      <router-link to="/purchase-histories" class="text-blue-600 hover:text-blue-900 mb-4 inline-block">
        ← Kembali ke Daftar Riwayat Pengadaan
      </router-link>
      <h1 class="text-2xl font-bold text-gray-900">Tambah Riwayat Pengadaan</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <form @submit.prevent="handleSubmit">
        <Select
          v-model="form.product_id"
          label="Produk"
          :options="productOptions"
          required
          :error="errors.product_id"
          @update:modelValue="loadProductComponents"
        />
        <Select
          v-model="form.component_id"
          label="Komponen"
          :options="componentOptions"
          required
          :error="errors.component_id"
        />
        <Input
          v-model="form.purchase_date"
          label="Tanggal Pengadaan"
          type="date"
          required
          :error="errors.purchase_date"
        />
        <Input
          v-model="form.vendor_name"
          label="Nama Vendor"
          placeholder="Nama vendor"
          :error="errors.vendor_name"
        />
        <Select
          v-model="form.currency"
          label="Mata Uang"
          :options="currencyOptions"
          required
          :error="errors.currency"
        />
        <Input
          v-model.number="form.quantity"
          label="Kuantitas"
          type="number"
          step="0.01"
          required
          :error="errors.quantity"
        />
        <Input
          v-model.number="form.unit_price_original"
          label="Harga Satuan (Original)"
          type="number"
          step="0.01"
          required
          :error="errors.unit_price_original"
        />
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Catatan tambahan"
          ></textarea>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
          <Button variant="outline" @click="$router.push('/purchase-histories')">Batal</Button>
          <Button type="submit" :loading="loading">Simpan</Button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import Input from '@/components/UI/Input.vue';
import Select from '@/components/UI/Select.vue';
import Button from '@/components/UI/Button.vue';

const router = useRouter();
const api = useApi();
const toast = useToast();

const products = ref([]);
const productComponents = ref([]);
const form = ref({
  product_id: '',
  component_id: '',
  purchase_date: new Date().toISOString().split('T')[0],
  vendor_name: '',
  currency: 'USD',
  quantity: 1,
  unit_price_original: 0,
  notes: '',
});

const errors = ref({});
const loading = ref(false);

const productOptions = computed(() => {
  return products.value.map(p => ({
    value: p.id,
    label: `${p.code} - ${p.name}`,
  }));
});

const componentOptions = computed(() => {
  return productComponents.value.map(c => ({
    value: c.id,
    label: `${c.code} - ${c.name}`,
  }));
});

const currencyOptions = [
  { value: 'USD', label: 'USD' },
  { value: 'IDR', label: 'IDR' },
];

onMounted(async () => {
  await loadProducts();
});

const loadProducts = async () => {
  const { data } = await api.get('/products?per_page=1000');
  if (data) {
    products.value = data.data || [];
  }
};

const loadProductComponents = async () => {
  if (!form.value.product_id) {
    productComponents.value = [];
    return;
  }
  
  const { data } = await api.get(`/products/${form.value.product_id}?with_components=1`);
  if (data && data.components) {
    productComponents.value = data.components.map(pc => pc.component).filter(Boolean);
  }
};

const handleSubmit = async () => {
  errors.value = {};
  loading.value = true;

  const { data, error } = await api.post('/purchase-histories', form.value);

  if (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
    loading.value = false;
    return;
  }

  if (data) {
    toast.success('Riwayat pengadaan berhasil ditambahkan');
    router.push('/purchase-histories');
  }

  loading.value = false;
};
</script>

