<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-1">Total Produk</div>
        <div class="text-3xl font-bold text-gray-900">{{ summary.total_products || 0 }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-1">Total Komponen</div>
        <div class="text-3xl font-bold text-gray-900">{{ summary.total_components || 0 }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-1">Kurs USD/IDR</div>
        <div class="text-3xl font-bold text-gray-900">
          {{ exchangeRate ? new Intl.NumberFormat('id-ID').format(exchangeRate.rate_value) : '-' }}
        </div>
        <div v-if="exchangeRate" class="text-xs text-gray-500 mt-1">
          {{ exchangeRate.rate_date }}
        </div>
      </div>
      <div class="bg-white rounded-lg shadow p-6">
        <div class="text-sm font-medium text-gray-500 mb-1">Total HPE</div>
        <div class="text-3xl font-bold text-gray-900">{{ summary.total_hpe_results || 0 }}</div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Hasil HPE Terbaru</h2>
      </div>
      <div class="p-6">
        <div v-if="loading" class="text-center py-8">
          <span class="animate-spin">⏳</span>
          <p class="mt-2 text-gray-500">Memuat data...</p>
        </div>
        <div v-else-if="recentHpe.length === 0" class="text-center py-8 text-gray-500">
          Belum ada hasil HPE
        </div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total dengan Margin</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="result in recentHpe" :key="result.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ result.product_name }}</div>
                  <div class="text-sm text-gray-500">{{ result.product_code }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(result.total_with_margin) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ result.calculated_at }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <router-link
                    :to="`/hpe/results/${result.id}`"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Lihat
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useApi } from '@/composables/useApi';

const api = useApi();

const summary = ref({});
const exchangeRate = ref(null);
const recentHpe = ref([]);
const loading = ref(true);

onMounted(async () => {
  await loadDashboard();
});

const loadDashboard = async () => {
  loading.value = true;
  const { data } = await api.get('/dashboard');
  
  if (data) {
    summary.value = data.summary || {};
    exchangeRate.value = data.exchange_rate;
    recentHpe.value = data.recent_hpe_results || [];
  }
  
  loading.value = false;
};
</script>

