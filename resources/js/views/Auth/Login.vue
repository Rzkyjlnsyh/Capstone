<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          HPE System
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Harga Perkiraan Estimasi
        </p>
      </div>
      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="rounded-md shadow-sm -space-y-px">
          <Input
            v-model="form.email"
            label="Email"
            type="email"
            placeholder="admin@hpe.local"
            required
            :error="errors.email"
          />
          <Input
            v-model="form.password"
            label="Password"
            type="password"
            placeholder="Password"
            required
            :error="errors.password"
          />
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
          {{ error }}
        </div>

        <div>
          <Button
            type="submit"
            :loading="loading"
            class="w-full"
            size="lg"
          >
            Masuk
          </Button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import Input from '@/components/UI/Input.vue';
import Button from '@/components/UI/Button.vue';

const router = useRouter();
const { login } = useAuth();

const form = ref({
  email: '',
  password: '',
});

const errors = ref({});
const error = ref('');
const loading = ref(false);

const handleLogin = async () => {
  errors.value = {};
  error.value = '';
  loading.value = true;

  try {
    const success = await login(form.value.email, form.value.password);
    if (!success) {
      error.value = 'Email atau password salah';
    }
  } catch (err) {
    error.value = 'Terjadi kesalahan saat login';
  } finally {
    loading.value = false;
  }
};
</script>

