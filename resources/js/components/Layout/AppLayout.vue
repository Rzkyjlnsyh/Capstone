<template>
  <div class="flex h-screen bg-gray-50">
    <Sidebar :is-open="sidebarOpen" @toggle="sidebarOpen = !sidebarOpen" />
    
    <div
      :class="[
        'flex-1 flex flex-col transition-all duration-300',
        sidebarOpen ? 'md:ml-64' : 'md:ml-20'
      ]"
    >
      <TopNavbar :title="pageTitle" @toggle-sidebar="sidebarOpen = !sidebarOpen" />
      
      <main class="flex-1 overflow-y-auto p-6">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
    
    <Toast />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import Sidebar from './Sidebar.vue';
import TopNavbar from './TopNavbar.vue';
import Toast from '@/components/UI/Toast.vue';

const route = useRoute();
const sidebarOpen = ref(window.innerWidth >= 768);

const pageTitle = computed(() => {
  const titles = {
    dashboard: 'Dashboard',
    'products.index': 'Produk',
    'products.create': 'Tambah Produk',
    'products.edit': 'Edit Produk',
    'products.show': 'Detail Produk',
    'components.index': 'Komponen',
    'components.create': 'Tambah Komponen',
    'components.edit': 'Edit Komponen',
    'purchase-histories.index': 'Riwayat Pengadaan',
    'purchase-histories.create': 'Tambah Riwayat Pengadaan',
    'purchase-histories.edit': 'Edit Riwayat Pengadaan',
    'hpe.calculate': 'Hitung HPE',
    'hpe.results.index': 'Hasil HPE',
    'hpe.results.show': 'Detail Hasil HPE',
    'exchange-rates.index': 'Kurs JISDOR',
    'audit-logs.index': 'Audit Log',
    'reporting.index': 'Laporan',
  };
  
  return titles[route.name] || 'HPE System';
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

