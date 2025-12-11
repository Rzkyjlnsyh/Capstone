<template>
  <div>
    <div class="mb-6">
      <router-link to="/products" class="text-blue-600 hover:text-blue-900 mb-4 inline-block">
        ← Kembali ke Daftar Produk
      </router-link>
      <h1 class="text-2xl font-bold text-gray-900">Edit Produk</h1>
    </div>

    <div v-if="loading" class="text-center py-12">
      <span class="animate-spin">⏳</span>
      <p class="mt-2 text-gray-500">Memuat data...</p>
    </div>

    <div v-else class="bg-white rounded-lg shadow p-6">
      <form @submit.prevent="handleSubmit">
        <Input
          v-model="form.code"
          label="Kode Produk"
          placeholder="PRD-001"
          required
          :error="errors.code"
        />
        <Input
          v-model="form.name"
          label="Nama Produk"
          placeholder="Nama produk"
          required
          :error="errors.name"
        />
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
          <textarea
            v-model="form.description"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Deskripsi produk"
          ></textarea>
        </div>
        <Input
          v-model="form.category"
          label="Kategori"
          placeholder="Kategori produk"
          :error="errors.category"
        />
        <Select
          v-model="form.status"
          label="Status"
          :options="statusOptions"
          :error="errors.status"
        />

        <div class="flex justify-end space-x-3 mt-6">
          <Button variant="outline" @click="$router.push('/products')">Batal</Button>
          <Button type="submit" :loading="saving">Simpan</Button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import Input from '@/components/UI/Input.vue';
import Select from '@/components/UI/Select.vue';
import Button from '@/components/UI/Button.vue';

const route = useRoute();
const router = useRouter();
const api = useApi();
const toast = useToast();

const form = ref({
  code: '',
  name: '',
  description: '',
  category: '',
  status: 'active',
});

const errors = ref({});
const loading = ref(true);
const saving = ref(false);

const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'draft', label: 'Draft' },
];

onMounted(async () => {
  await loadProduct();
});

const loadProduct = async () => {
  loading.value = true;
  const { data } = await api.get(`/products/${route.params.id}`);
  
  if (data) {
    form.value = {
      code: data.code || '',
      name: data.name || '',
      description: data.description || '',
      category: data.category || '',
      status: data.status || 'active',
    };
  }
  
  loading.value = false;
};

const handleSubmit = async () => {
  errors.value = {};
  saving.value = true;

  const { data, error } = await api.put(`/products/${route.params.id}`, form.value);

  if (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
    saving.value = false;
    return;
  }

  if (data) {
    toast.success('Produk berhasil diperbarui');
    router.push(`/products/${route.params.id}`);
  }

  saving.value = false;
};
</script>

