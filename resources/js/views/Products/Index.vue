<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Produk</h1>
      <Button
        v-if="canCreate"
        @click="$router.push('/products/create')"
      >
        + Tambah Produk
      </Button>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b border-gray-200 flex items-center justify-between">
        <Input
          v-model="search"
          placeholder="Cari produk..."
          class="w-64"
          @input="loadProducts"
        />
        <Select
          v-model="statusFilter"
          :options="statusOptions"
          placeholder="Filter Status"
          @update:modelValue="loadProducts"
        />
      </div>

      <Table
        :columns="columns"
        :data="products"
        :loading="loading"
        :pagination="pagination"
        has-actions
        @page-change="handlePageChange"
      >
        <template #cell-code="{ value }">
          <span class="font-mono text-sm">{{ value }}</span>
        </template>
        <template #cell-status="{ value }">
          <span
            :class="[
              'px-2 py-1 text-xs rounded-full',
              value === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
            ]"
          >
            {{ value }}
          </span>
        </template>
        <template #actions="{ row }">
          <router-link
            :to="`/products/${row.id}`"
            class="text-blue-600 hover:text-blue-900 mr-3"
          >
            Lihat
          </router-link>
          <button
            v-if="canEdit"
            @click="$router.push(`/products/${row.id}/edit`)"
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
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import Button from '@/components/UI/Button.vue';
import Input from '@/components/UI/Input.vue';
import Select from '@/components/UI/Select.vue';
import Table from '@/components/UI/Table.vue';

const router = useRouter();
const authStore = useAuthStore();
const api = useApi();
const toast = useToast();

const products = ref([]);
const loading = ref(false);
const search = ref('');
const statusFilter = ref('');
const pagination = ref(null);

const canCreate = computed(() => authStore.user?.role === 'admin');
const canEdit = computed(() => authStore.user?.role === 'admin');
const canDelete = computed(() => authStore.user?.role === 'admin');

const columns = [
  { key: 'code', label: 'Kode' },
  { key: 'name', label: 'Nama' },
  { key: 'category', label: 'Kategori' },
  { key: 'status', label: 'Status' },
];

const statusOptions = [
  { value: '', label: 'Semua Status' },
  { value: 'active', label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
  { value: 'draft', label: 'Draft' },
];

onMounted(() => {
  loadProducts();
});

const loadProducts = async (page = 1) => {
  loading.value = true;
  const params = new URLSearchParams({
    page: page.toString(),
    per_page: '15',
  });
  
  if (search.value) params.append('search', search.value);
  if (statusFilter.value) params.append('status', statusFilter.value);
  
  const { data } = await api.get(`/products?${params}`);
  
  if (data) {
    products.value = data.data || [];
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
  loadProducts(page);
};

const handleDelete = async (product) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus produk "${product.name}"?`)) {
    return;
  }
  
  const { error } = await api.delete(`/products/${product.id}`);
  
  if (!error) {
    toast.success('Produk berhasil dihapus');
    loadProducts();
  }
};
</script>

