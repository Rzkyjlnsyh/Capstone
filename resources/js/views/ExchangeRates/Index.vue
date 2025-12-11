<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Kurs JISDOR (USD/IDR)</h1>
      <div class="flex items-center space-x-3">
        <label class="flex items-center space-x-2 text-sm text-gray-600">
          <input
            v-model="useMock"
            type="checkbox"
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
          />
          <span>Gunakan Mock Data</span>
        </label>
        <Button
          v-if="canSync"
          @click="handleSync"
          :loading="syncing"
        >
          {{ syncing ? 'Menyinkronkan...' : 'Sinkronisasi Kurs' }}
        </Button>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b border-gray-200">
        <div v-if="latestRate" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-sm font-medium text-blue-800">Kurs Terbaru</div>
              <div class="text-2xl font-bold text-blue-900 mt-1">
                {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(latestRate.rate_value) }}
              </div>
              <div class="text-xs text-blue-600 mt-1">
                {{ latestRate.rate_date }}
                <span 
                  :class="[
                    'ml-2 px-2 py-0.5 rounded text-xs font-medium',
                    latestRate.source === 'JISDOR' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                  ]"
                >
                  {{ latestRate.source === 'JISDOR' ? '✅ Data Real' : '⚠️ Mock Data' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <Table
        :columns="columns"
        :data="rates"
        :loading="loading"
        :pagination="pagination"
        @page-change="handlePageChange"
      >
        <template #cell-rate_date="{ value }">
          {{ new Date(value).toLocaleDateString('id-ID') }}
        </template>
        <template #cell-rate_value="{ value }">
          <span class="font-semibold">{{ new Intl.NumberFormat('id-ID').format(value) }}</span>
        </template>
        <template #cell-source="{ value }">
          <span
            :class="[
              'px-2 py-1 text-xs rounded font-medium',
              value === 'JISDOR' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
            ]"
          >
            {{ value === 'JISDOR' ? '✅ Real' : '⚠️ Mock' }}
          </span>
        </template>
        <template #cell-fetched_at="{ value }">
          {{ value ? new Date(value).toLocaleString('id-ID') : '-' }}
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
import Table from '@/components/UI/Table.vue';

const authStore = useAuthStore();
const api = useApi();
const toast = useToast();

const rates = ref([]);
const latestRate = ref(null);
const loading = ref(false);
const syncing = ref(false);
const useMock = ref(false); // Default false = coba real API dulu
const pagination = ref(null);

const canSync = computed(() => authStore.user?.role === 'admin');

const columns = [
  { key: 'rate_date', label: 'Tanggal', format: 'date' },
  { key: 'rate_value', label: 'Kurs USD/IDR' },
  { key: 'source', label: 'Sumber' },
  { key: 'fetched_at', label: 'Diperbarui' },
];

onMounted(async () => {
  await Promise.all([loadLatestRate(), loadRates()]);
});

const loadLatestRate = async () => {
  const { data } = await api.get('/exchange-rates/latest');
  if (data) {
    latestRate.value = data;
  }
};

const loadRates = async (page = 1) => {
  loading.value = true;
  const params = new URLSearchParams({
    page: page.toString(),
    per_page: '30',
  });
  
  const { data } = await api.get(`/exchange-rates?${params}`);
  
  if (data) {
    rates.value = data.data || [];
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
  loadRates(page);
};

// FIX BUG: Sync langsung tanpa queue untuk development
const handleSync = async (event) => {
  // Prevent double execution
  if (syncing.value) {
    return;
  }

  // Prevent event bubbling
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }

  // Confirm dialog
  if (!confirm('Sinkronisasi kurs untuk hari ini?')) {
    return;
  }

  // Set syncing immediately to prevent double clicks
  syncing.value = true;
  
  try {
    // Simpan jumlah data sebelum sync untuk cek apakah bertambah
    const countBefore = rates.value.length;
    
    const { data, error } = await api.post('/exchange-rates/sync', {
      date: new Date().toISOString().split('T')[0],
      sync_now: true, // Sync langsung tanpa queue
      use_mock: useMock.value, // Kirim pilihan mock/real dari frontend
    });

    if (error) {
      toast.error('Gagal sinkronisasi kurs');
      return;
    }

    if (data) {
      // Refresh data setelah sync
      await Promise.all([loadLatestRate(), loadRates()]);
      
      // Cek apakah data bertambah
      const countAfter = rates.value.length;
      const isNewData = countAfter > countBefore || (data.rate && data.rate.id);
      
      if (isNewData) {
        toast.success('Kurs berhasil disinkronkan dan data diperbarui');
      } else {
        // Mungkin data untuk tanggal yang sama sudah ada (update bukan insert)
        toast.success('Kurs berhasil disinkronkan');
      }
    }
  } catch (err) {
    console.error('Sync error:', err);
    toast.error('Gagal sinkronisasi kurs');
  } finally {
    // Always reset syncing state
    syncing.value = false;
  }
};
</script>

