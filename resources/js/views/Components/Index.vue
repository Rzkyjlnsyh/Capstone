<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Komponen</h1>
      <Button
        v-if="canCreate"
        @click="$router.push('/components/create')"
      >
        + Tambah Komponen
      </Button>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b border-gray-200">
        <Input
          v-model="search"
          placeholder="Cari komponen..."
          class="w-64"
          @input="loadComponents"
        />
      </div>

      <Table
        :columns="columns"
        :data="components"
        :loading="loading"
        :pagination="pagination"
        has-actions
        @page-change="handlePageChange"
      >
        <template #cell-code="{ value }">
          <span class="font-mono text-sm">{{ value }}</span>
        </template>
        <template #actions="{ row }">
          <button
            v-if="canEdit"
            @click="$router.push(`/components/${row.id}/edit`)"
            class="text-yellow-600 hover:text-yellow-900 mr-3"
          >
            Edit
          </button>
          <button
            v-if="canDelete"
            @click="handleDelete(row)"
            class="text-red-600 hover:text-red-900"
          >
            Hapus
          </button>
        </template>
      </Table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import Button from '@/components/UI/Button.vue';
import Input from '@/components/UI/Input.vue';
import Table from '@/components/UI/Table.vue';

const authStore = useAuthStore();
const api = useApi();
const toast = useToast();

const components = ref([]);
const loading = ref(false);
const search = ref('');
const pagination = ref(null);

const canCreate = computed(() => authStore.user?.role === 'admin');
const canEdit = computed(() => authStore.user?.role === 'admin');
const canDelete = computed(() => authStore.user?.role === 'admin');

const columns = [
  { key: 'code', label: 'Kode' },
  { key: 'name', label: 'Nama' },
  { key: 'unit', label: 'Satuan' },
];

onMounted(() => {
  loadComponents();
});

const loadComponents = async (page = 1) => {
  loading.value = true;
  const params = new URLSearchParams({
    page: page.toString(),
    per_page: '15',
  });
  
  if (search.value) params.append('search', search.value);
  
  const { data } = await api.get(`/components?${params}`);
  
  if (data) {
    components.value = data.data || [];
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
  loadComponents(page);
};

const handleDelete = async (component) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus komponen "${component.name}"?`)) {
    return;
  }
  
  const { error } = await api.delete(`/components/${component.id}`);
  
  if (!error) {
    toast.success('Komponen berhasil dihapus');
    loadComponents();
  }
};
</script>

