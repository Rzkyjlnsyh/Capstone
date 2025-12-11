<template>
  <div>
    <div class="mb-6">
      <router-link to="/components" class="text-blue-600 hover:text-blue-900 mb-4 inline-block">
        ← Kembali ke Daftar Komponen
      </router-link>
      <h1 class="text-2xl font-bold text-gray-900">Tambah Komponen</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <form @submit.prevent="handleSubmit">
        <Input
          v-model="form.code"
          label="Kode Komponen"
          placeholder="CMP-001"
          required
          :error="errors.code"
        />
        <Input
          v-model="form.name"
          label="Nama Komponen"
          placeholder="Nama komponen"
          required
          :error="errors.name"
        />
        <Input
          v-model="form.unit"
          label="Satuan"
          placeholder="pcs, kg, m, dll"
          required
          :error="errors.unit"
        />

        <div class="flex justify-end space-x-3 mt-6">
          <Button variant="outline" @click="$router.push('/components')">Batal</Button>
          <Button type="submit" :loading="loading">Simpan</Button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import Input from '@/components/UI/Input.vue';
import Button from '@/components/UI/Button.vue';

const router = useRouter();
const api = useApi();
const toast = useToast();

const form = ref({
  code: '',
  name: '',
  unit: '',
});

const errors = ref({});
const loading = ref(false);

const handleSubmit = async () => {
  errors.value = {};
  loading.value = true;

  const { data, error } = await api.post('/components', form.value);

  if (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
    loading.value = false;
    return;
  }

  if (data) {
    toast.success('Komponen berhasil ditambahkan');
    router.push('/components');
  }

  loading.value = false;
};
</script>

