<template>
  <div>
    <div class="mb-6">
      <router-link to="/hpe/results" class="text-blue-600 hover:text-blue-900 mb-4 inline-block">
        ← Kembali ke Hasil HPE
      </router-link>
      <h1 class="text-2xl font-bold text-gray-900">Hitung HPE</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <form @submit.prevent="handleCalculate">
        <Select
          v-model="form.product_id"
          label="Produk"
          :options="productOptions"
          required
          :error="errors.product_id"
        />
        <Input
          v-model.number="form.margin_percent"
          label="Margin (%)"
          type="number"
          step="0.01"
          min="0"
          max="100"
          placeholder="10"
          :error="errors.margin_percent"
        />

        <div class="flex justify-end space-x-3 mt-6">
          <Button variant="outline" @click="$router.push('/hpe/results')">Batal</Button>
          <Button type="submit" :loading="loading">Hitung HPE</Button>
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
const form = ref({
  product_id: '',
  margin_percent: 10,
});

const errors = ref({});
const loading = ref(false);

const productOptions = computed(() => {
  return products.value.map(p => ({
    value: p.id,
    label: `${p.code} - ${p.name}`,
  }));
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

const handleCalculate = async () => {
  errors.value = {};
  loading.value = true;

  const { data, error } = await api.post('/hpe/calculate', form.value);

  if (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      toast.error(error.response?.data?.message || 'Gagal menghitung HPE');
    }
    loading.value = false;
    return;
  }

  if (data && data.data) {
    toast.success('HPE berhasil dihitung');
    router.push(`/hpe/results/${data.data.id}`);
  }

  loading.value = false;
};
</script>

