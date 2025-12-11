import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useApi } from './useApi';
import { useToast } from './useToast';

export function useAuth() {
  const router = useRouter();
  const authStore = useAuthStore();
  const api = useApi();
  const toast = useToast();

  const login = async (email, password) => {
    const { data, error } = await api.post('/auth/login', { email, password });
    
    if (data && data.token) {
      authStore.setAuth(data.user, data.token);
      toast.success('Login berhasil');
      router.push('/dashboard');
      return true;
    }
    
    return false;
  };

  const logout = async () => {
    try {
      await api.post('/auth/logout');
    } catch (error) {
      // Ignore error on logout
    } finally {
      authStore.clearAuth();
      router.push('/login');
    }
  };

  return {
    login,
    logout,
    user: authStore.user,
    isAuthenticated: authStore.isAuthenticated,
  };
}

