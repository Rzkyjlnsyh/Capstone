<template>
  <aside
    :class="[
      'fixed left-0 top-0 h-full bg-gray-900 text-white transition-all duration-300 z-40',
      isOpen ? 'w-64' : 'w-0 md:w-20'
    ]"
  >
    <div class="flex flex-col h-full">
      <div class="p-4 flex items-center justify-between border-b border-gray-800">
        <h1 v-if="isOpen" class="text-xl font-bold">HPE System</h1>
        <button
          @click="$emit('toggle')"
          class="p-2 hover:bg-gray-800 rounded-lg transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>
      
      <nav class="flex-1 overflow-y-auto p-4">
        <router-link
          v-for="item in menuItems"
          :key="item.name"
          :to="item.to"
          :class="[
            'flex items-center px-4 py-3 mb-2 rounded-lg transition-colors',
            isActive(item.to) ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800'
          ]"
        >
          <span class="text-xl mr-3">{{ item.icon }}</span>
          <span v-if="isOpen" class="text-sm font-medium">{{ item.label }}</span>
        </router-link>
      </nav>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const props = defineProps({
  isOpen: Boolean,
});

defineEmits(['toggle']);

const route = useRoute();
const authStore = useAuthStore();

const isActive = (path) => {
  if (path === '/dashboard') {
    return route.path === '/' || route.path === '/dashboard';
  }
  return route.path.startsWith(path);
};

const menuItems = computed(() => {
  const items = [
    { name: 'dashboard', to: '/dashboard', label: 'Dashboard', icon: '📊' },
    { name: 'products', to: '/products', label: 'Produk', icon: '📦' },
    { name: 'components', to: '/components', label: 'Komponen', icon: '🔧' },
    { name: 'purchase-histories', to: '/purchase-histories', label: 'Riwayat Pengadaan', icon: '📋' },
    { name: 'hpe-results', to: '/hpe/results', label: 'Hasil HPE', icon: '📈' },
    { name: 'hpe-calculate', to: '/hpe/calculate', label: 'Hitung HPE', icon: '🧮' },
    { name: 'exchange-rates', to: '/exchange-rates', label: 'Kurs', icon: '💱' },
    { name: 'reporting', to: '/reporting', label: 'Laporan', icon: '📄' },
  ];

  // Only show audit logs for admin
  if (authStore.user?.role === 'admin') {
    items.push({ name: 'audit-logs', to: '/audit-logs', label: 'Audit Log', icon: '🔍' });
  }

  return items;
});
</script>

