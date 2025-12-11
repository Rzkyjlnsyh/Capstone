import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('api_token') || null);

  const isAuthenticated = computed(() => !!token.value);

  function setAuth(userData, authToken) {
    user.value = userData;
    token.value = authToken;
    localStorage.setItem('api_token', authToken);
    localStorage.setItem('user', JSON.stringify(userData));
    axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;
  }

  function clearAuth() {
    user.value = null;
    token.value = null;
    localStorage.removeItem('api_token');
    localStorage.removeItem('user');
    delete axios.defaults.headers.common['Authorization'];
  }

  function loadUserFromStorage() {
    const storedUser = localStorage.getItem('user');
    const storedToken = localStorage.getItem('api_token');
    if (storedUser && storedToken) {
      user.value = JSON.parse(storedUser);
      token.value = storedToken;
      axios.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`;
    }
  }

  async function checkAuth() {
    try {
      const response = await axios.get('/auth/me');
      user.value = response.data;
      return true;
    } catch (error) {
      clearAuth();
      return false;
    }
  }

  // Load user from storage on init
  loadUserFromStorage();

  return {
    user,
    token,
    isAuthenticated,
    setAuth,
    clearAuth,
    checkAuth,
  };
});

