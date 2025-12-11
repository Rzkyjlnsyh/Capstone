<template>
  <div>
    <div class="mb-6">
      <router-link to="/hpe/results" class="text-blue-600 hover:text-blue-900 mb-4 inline-block">
        ← Kembali ke Hasil HPE
      </router-link>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900">Detail Hasil HPE</h1>
        <Button
          v-if="canExport"
          @click="handleExport"
        >
          Export PDF
        </Button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-12">
      <span class="animate-spin">⏳</span>
      <p class="mt-2 text-gray-500">Memuat data...</p>
    </div>

    <div v-else class="space-y-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi HPE</h2>
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-sm font-medium text-gray-500">Produk</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ result.product?.name }}</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Margin</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ result.margin_percent }}%</dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Total Biaya (IDR)</dt>
            <dd class="mt-1 text-sm text-gray-900 font-semibold">
              {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(result.total_cost_idr) }}
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Total dengan Margin (IDR)</dt>
            <dd class="mt-1 text-sm text-gray-900 font-semibold text-green-600">
              {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(result.total_with_margin) }}
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Kurs USD/IDR</dt>
            <dd class="mt-1 text-sm text-gray-900">
              {{ result.exchange_rate ? new Intl.NumberFormat('id-ID').format(result.exchange_rate.rate_value) : '-' }}
            </dd>
          </div>
          <div>
            <dt class="text-sm font-medium text-gray-500">Dihitung Pada</dt>
            <dd class="mt-1 text-sm text-gray-900">
              {{ new Date(result.calculated_at).toLocaleString('id-ID') }}
            </dd>
          </div>
        </dl>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Component Breakdown</h2>
        <div v-if="result.component_breakdown && result.component_breakdown.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Komponen</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Satuan (IDR)</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal (IDR)</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">History</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in result.component_breakdown" :key="index">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ item.component_name }}</div>
                  <div class="text-sm text-gray-500">{{ item.component_code }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ item.bom_quantity }} {{ item.unit }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(item.average_unit_price_idr) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                  {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(item.subtotal_idr) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ item.history_count }} transaksi
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          Tidak ada data breakdown
        </div>
      </div>

      <div v-if="result.warnings && result.warnings.length > 0" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-yellow-800 mb-2">Peringatan:</h3>
        <ul class="list-disc list-inside text-sm text-yellow-700">
          <li v-for="(warning, index) in result.warnings" :key="index">{{ warning }}</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useApi } from '@/composables/useApi';
import { useToast } from '@/composables/useToast';
import Button from '@/components/UI/Button.vue';

const route = useRoute();
const authStore = useAuthStore();
const api = useApi();
const toast = useToast();

const result = ref({});
const loading = ref(true);

const canExport = computed(() => ['admin', 'finance'].includes(authStore.user?.role));

onMounted(async () => {
  await loadResult();
});

const loadResult = async () => {
  loading.value = true;
  const { data } = await api.get(`/hpe/results/${route.params.id}`);
  
  if (data) {
    result.value = data;
  }
  
  loading.value = false;
};

const handleExport = async () => {
  window.open(`/api/reporting/export-hpe?type=pdf&hpe_result_id=${route.params.id}`, '_blank');
};
</script>

