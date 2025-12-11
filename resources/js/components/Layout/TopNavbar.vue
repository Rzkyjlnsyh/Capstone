<template>
  <nav class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
    <div class="flex items-center">
      <button
        @click="$emit('toggle-sidebar')"
        class="p-2 hover:bg-gray-100 rounded-lg transition-colors md:hidden"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
      <h2 class="ml-4 text-lg font-semibold text-gray-800">{{ title }}</h2>
    </div>
    
    <div class="flex items-center space-x-4">
      <div class="text-sm text-gray-600">
        <span class="font-medium">{{ user?.name }}</span>
        <span class="mx-2">•</span>
        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">{{ user?.role }}</span>
      </div>
      <Button variant="outline" size="sm" @click="handleLogout">Logout</Button>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useAuth } from '@/composables/useAuth';
import Button from '@/components/UI/Button.vue';

const props = defineProps({
  title: {
    type: String,
    default: 'Dashboard',
  },
});

defineEmits(['toggle-sidebar']);

const router = useRouter();
const authStore = useAuthStore();
const { logout } = useAuth();

const user = computed(() => authStore.user);

const handleLogout = async () => {
  if (confirm('Apakah Anda yakin ingin logout?')) {
    await logout();
  }
};
</script>

