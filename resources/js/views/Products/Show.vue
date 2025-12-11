<template>
  <div>
    <div class="mb-6">
      <router-link to="/products" class="text-blue-600 hover:text-blue-900 mb-4 inline-block">
        ← Kembali ke Daftar Produk
      </router-link>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">{{ product.name || 'Detail Produk' }}</h1>
        <div class="space-x-2">
          <Button
            v-if="canEdit"
            variant="outline"
            @click="$router.push(`/products/${product.id}/edit`)"
          >
            Edit
          </Button>
          <Button
            v-if="canDelete"
            variant="danger"
            @click="handleDelete"
          >
            Hapus
          </Button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-12">
      <span class="animate-spin">⏳</span>
      <p class="mt-2 text-gray-500">Memuat data...</p>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Produk</h2>
          <dl class="space-y-4">
            <div>
              <dt class="text-sm font-medium text-gray-500">Kode Produk</dt>
              <dd class="mt-1 text-sm text-gray-900 font-mono">{{ product.code }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Nama Produk</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ product.name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Kategori</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ product.category || '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Status</dt>
              <dd class="mt-1">
                <span
                  :class="[
                    'px-2 py-1 text-xs rounded-full',
                    product.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ product.status }}
                </span>
              </dd>
            </div>
            <div v-if="product.description">
              <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ product.description }}</dd>
            </div>
          </dl>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Komponen Penyusun (BoM)</h2>
            <Button
              v-if="canEdit"
              size="sm"
              @click="showAddComponentModal = true"
            >
              + Tambah Komponen
            </Button>
          </div>
          <div v-if="components.length === 0" class="text-center py-8 text-gray-500">
            Belum ada komponen
          </div>
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Komponen</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kuantitas</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                  <th v-if="canEdit" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="pc in components" :key="pc.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ pc.component?.name }}</div>
                    <div class="text-sm text-gray-500">{{ pc.component?.code }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ pc.quantity }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ pc.unit_override || pc.component?.unit }}</td>
                  <td v-if="canEdit" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      @click="handleRemoveComponent(pc.id)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Hapus
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h2>
          <dl class="space-y-4">
            <div>
              <dt class="text-sm font-medium text-gray-500">Total Komponen</dt>
              <dd class="mt-1 text-2xl font-bold text-gray-900">{{ components.length }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>

    <!-- Add Component Modal -->
    <Modal
      :show="showAddComponentModal"
      title="Tambah Komponen"
      @close="showAddComponentModal = false"
    >
      <form @submit.prevent="handleAddComponent">
        <Select
          v-model="newComponent.component_id"
          label="Komponen"
          :options="availableComponents"
          required
        />
        <Input
          v-model.number="newComponent.quantity"
          label="Kuantitas"
          type="number"
          step="0.01"
          required
        />
        <Input
          v-model="newComponent.unit_override"
          label="Satuan (opsional)"
          placeholder="Kosongkan untuk menggunakan satuan default"
        />
        <div class="flex justify-end space-x-3 mt-6">
          <Button variant="outline" @click="showAddComponentModal = false">Batal</Button>
          <Button type="submit" :loading="addingComponent">Tambah</Button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import Button from '@/components/UI/Button.vue';
import Input from '@/components/UI/Input.vue';
import Select from '@/components/UI/Select.vue';
import Modal from '@/components/UI/Modal.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const api = useApi();
const toast = useToast();

const product = ref({});
const components = ref([]);
const allComponents = ref([]);
const loading = ref(true);
const showAddComponentModal = ref(false);
const addingComponent = ref(false);

const newComponent = ref({
  component_id: '',
  quantity: 1,
  unit_override: '',
});

const canEdit = computed(() => authStore.user?.role === 'admin');
const canDelete = computed(() => authStore.user?.role === 'admin');

const availableComponents = computed(() => {
  return allComponents.value.map(c => ({
    value: c.id,
    label: `${c.code} - ${c.name}`,
  }));
});

onMounted(async () => {
  await Promise.all([loadProduct(), loadAllComponents()]);
});

const loadProduct = async () => {
  loading.value = true;
  const { data } = await api.get(`/products/${route.params.id}?with_components=1`);
  
  if (data) {
    product.value = data;
    components.value = data.components || [];
  }
  
  loading.value = false;
};

const loadAllComponents = async () => {
  const { data } = await api.get('/components?per_page=1000');
  if (data) {
    allComponents.value = data.data || [];
  }
};

const handleAddComponent = async () => {
  addingComponent.value = true;
  const { error } = await api.post(`/products/${route.params.id}/components`, newComponent.value);
  
  if (!error) {
    toast.success('Komponen berhasil ditambahkan');
    showAddComponentModal.value = false;
    newComponent.value = { component_id: '', quantity: 1, unit_override: '' };
    await loadProduct();
  }
  
  addingComponent.value = false;
};

const handleRemoveComponent = async (productComponentId) => {
  if (!confirm('Apakah Anda yakin ingin menghapus komponen ini?')) {
    return;
  }
  
  const { error } = await api.delete(`/products/${route.params.id}/components/${productComponentId}`);
  
  if (!error) {
    toast.success('Komponen berhasil dihapus');
    await loadProduct();
  }
};

const handleDelete = async () => {
  if (!confirm(`Apakah Anda yakin ingin menghapus produk "${product.value.name}"?`)) {
    return;
  }
  
  const { error } = await api.delete(`/products/${route.params.id}`);
  
  if (!error) {
    toast.success('Produk berhasil dihapus');
    router.push('/products');
  }
};
</script>

