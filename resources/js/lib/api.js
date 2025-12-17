// resources/js/lib/api.js
import axios from 'axios';

export const api = () => {
  const instance = axios.create({ baseURL: '/api' });
  instance.interceptors.request.use((config) => {
    config.headers['Accept'] = 'application/json';
    const token = sessionStorage.getItem('access_token');
    if (token) config.headers['Authorization'] = `Bearer ${token}`;
    else delete config.headers['Authorization'];
    return config;
  });
  return instance;
};