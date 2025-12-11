import axios from 'axios';
import { useToast } from './useToast';

export function useApi() {
  const toast = useToast();

  const get = async (url, config = {}) => {
    try {
      const response = await axios.get(url, config);
      return { data: response.data, error: null };
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Terjadi kesalahan';
      toast.error(message);
      return { data: null, error };
    }
  };

  const post = async (url, data = {}, config = {}) => {
    try {
      const response = await axios.post(url, data, config);
      return { data: response.data, error: null };
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Terjadi kesalahan';
      toast.error(message);
      return { data: null, error };
    }
  };

  const put = async (url, data = {}, config = {}) => {
    try {
      const response = await axios.put(url, data, config);
      return { data: response.data, error: null };
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Terjadi kesalahan';
      toast.error(message);
      return { data: null, error };
    }
  };

  const patch = async (url, data = {}, config = {}) => {
    try {
      const response = await axios.patch(url, data, config);
      return { data: response.data, error: null };
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Terjadi kesalahan';
      toast.error(message);
      return { data: null, error };
    }
  };

  const del = async (url, config = {}) => {
    try {
      const response = await axios.delete(url, config);
      return { data: response.data, error: null };
    } catch (error) {
      const message = error.response?.data?.message || error.message || 'Terjadi kesalahan';
      toast.error(message);
      return { data: null, error };
    }
  };

  return {
    get,
    post,
    put,
    patch,
    delete: del,
  };
}

