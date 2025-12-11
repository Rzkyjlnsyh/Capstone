<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Audit Log</h1>

    <div class="bg-white rounded-lg shadow">
      <div class="p-4 border-b border-gray-200 flex items-center space-x-4">
        <Select
          v-model="entityFilter"
          :options="entityOptions"
          placeholder="Filter Entity"
          @update:modelValue="loadLogs"
        />
        <Select
          v-model="actionFilter"
          :options="actionOptions"
          placeholder="Filter Action"
          @update:modelValue="loadLogs"
        />
      </div>

      <Table
        :columns="columns"
        :data="logs"
        :loading="loading"
        :pagination="pagination"
        has-actions
        @page-change="handlePageChange"
      >
        <template #cell-created_at="{ value }">
          {{ new Date(value).toLocaleString('id-ID') }}
        </template>
        <template #actions="{ row }">
          <button
            @click="showDetail(row)"
            class="text-blue-600 hover:text-blue-900"
          >
            Detail
          </button>
        </template>
      </Table>
    </div>

    <Modal
      :show="showModal"
      title="Detail Audit Log"
      @close="showModal = false"
    >
      <div v-if="selectedLog" class="space-y-4">
        <div>
          <dt class="text-sm font-medium text-gray-500">User</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ selectedLog.user?.name || '-' }}</dd>
        </div>
        <div>
          <dt class="text-sm font-medium text-gray-500">Entity</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ selectedLog.entity_type }}</dd>
        </div>
        <div>
          <dt class="text-sm font-medium text-gray-500">Action</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ selectedLog.action }}</dd>
        </div>
        <div>
          <dt class="text-sm font-medium text-gray-500">IP Address</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ selectedLog.ip_address }}</dd>
        </div>
        <div>
          <dt class="text-sm font-medium text-gray-500">Timestamp</dt>
          <dd class="mt-1 text-sm text-gray-900">{{ new Date(selectedLog.created_at).toLocaleString('id-ID') }}</dd>
        </div>
        <div v-if="selectedLog.changes">
          <dt class="text-sm font-medium text-gray-500">Changes</dt>
          <dd class="mt-1 text-sm text-gray-900">
            <pre class="bg-gray-50 p-3 rounded text-xs overflow-auto">{{ JSON.stringify(selectedLog.changes, null, 2) }}</pre>
          </dd>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useApi } from '@/composables/useApi';
import Select from '@/components/UI/Select.vue';
import Table from '@/components/UI/Table.vue';
import Modal from '@/components/UI/Modal.vue';

const api = useApi();

const logs = ref([]);
const loading = ref(false);
const entityFilter = ref('');
const actionFilter = ref('');
const pagination = ref(null);
const showModal = ref(false);
const selectedLog = ref(null);

const columns = [
  { key: 'created_at', label: 'Tanggal' },
  { key: 'user.name', label: 'User' },
  { key: 'entity_type', label: 'Entity' },
  { key: 'action', label: 'Action' },
  { key: 'ip_address', label: 'IP Address' },
];

const entityOptions = [
  { value: '', label: 'Semua Entity' },
  { value: 'products', label: 'Products' },
  { value: 'components', label: 'Components' },
  { value: 'purchase-histories', label: 'Purchase Histories' },
];

const actionOptions = [
  { value: '', label: 'Semua Action' },
  { value: 'POST', label: 'Create' },
  { value: 'PUT', label: 'Update' },
  { value: 'PATCH', label: 'Update' },
  { value: 'DELETE', label: 'Delete' },
];

onMounted(() => {
  loadLogs();
});

const loadLogs = async (page = 1) => {
  loading.value = true;
  const params = new URLSearchParams({
    page: page.toString(),
    per_page: '15',
  });
  
  if (entityFilter.value) params.append('entity_type', entityFilter.value);
  if (actionFilter.value) params.append('action', actionFilter.value);
  
  const { data } = await api.get(`/audit-logs?${params}`);
  
  if (data) {
    logs.value = data.data || [];
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
  loadLogs(page);
};

const showDetail = async (log) => {
  const { data } = await api.get(`/audit-logs/${log.id}`);
  if (data) {
    selectedLog.value = data;
    showModal.value = true;
  }
};
</script>

