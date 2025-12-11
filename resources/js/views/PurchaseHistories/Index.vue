<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Riwayat Pengadaan</h1>
      <Button
        v-if="canCreate"
        @click="$router.push('/purchase-histories/create')"
      >
        + Tambah Riwayat Pengadaan
      </Button>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b border-gray-200 flex items-center space-x-4">
        <Input
          v-model="search"
          placeholder="Cari..."
          class="w-64"
          @input="loadHistories"
        />
        <Select
          v-model="productFilter"
          :options="productOptions"
          placeholder="Filter Produk"
          @update:modelValue="loadHistories"
        />
      </div>

      <Table
        :columns="columns"
        :data="histories"
        :loading="loading"
        :pagination="pagination"
        has-actions
        @page-change="handlePageChange"
      >
        <template #cell-purchase_date="{ value }">
          {{ new Date(value).toLocaleDateString('id-ID') }}
        </template>
        <template #cell-unit_price_idr="{ value }">
          {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value) }}
        </template>
        <template #actions="{ row }">
          <button
            v-if="canEdit"
            @click="$router.push(`/purchase-histories/${row.id}/edit`)"
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
import Select from '@/components/UI/Select.vue';
import Table from '@/components/UI/Table.vue';

const authStore = useAuthStore();
const api = useApi();
const toast = useToast();

const histories = ref([]);
const products = ref([]);
const loading = ref(false);
const search = ref('');
const productFilter = ref('');
const pagination = ref(null);

const canCreate = computed(() => ['admin', 'finance'].includes(authStore.user?.role));
const canEdit = computed(() => ['admin', 'finance'].includes(authStore.user?.role));
const canDelete = computed(() => ['admin', 'finance'].includes(authStore.user?.role));

const columns = [
  { key: 'purchase_date', label: 'Tanggal', format: 'date' },
  { key: 'product.name', label: 'Produk' },
  { key: 'component.name', label: 'Komponen' },
  { key: 'quantity', label: 'Qty' },
  { key: 'unit_price_idr', label: 'Harga Satuan (IDR)' },
  { key: 'vendor_name', label: 'Vendor' },
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
  await loadHistories();
});

const loadProducts = async () => {
  const { data } = await api.get('/products?per_page=1000');
  if (data) {
    products.value = data.data || [];
  }
};

const loadHistories = async (page = 1) => {
  loading.value = true;
  const params = new URLSearchParams({
    page: page.toString(),
    per_page: '15',
  });
  
  if (search.value) params.append('search', search.value);
  if (productFilter.value) params.append('product_id', productFilter.value);
  
  const { data } = await api.get(`/purchase-histories?${params}`);
  
  if (data) {
    histories.value = data.data || [];
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
  loadHistories(page);
};

const handleDelete = async (history) => {
  if (!confirm('Apakah Anda yakin ingin menghapus riwayat pengadaan ini?')) {
    return;
  }
  
  const { error } = await api.delete(`/purchase-histories/${history.id}`);
  
  if (!error) {
    toast.success('Riwayat pengadaan berhasil dihapus');
    loadHistories();
  }
};
</script>

